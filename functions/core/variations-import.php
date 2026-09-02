<?php
/**
 * Разовый инструмент: создание вариаций товаров из спецификации.
 *
 * CSV-импортёр WooCommerce молча отбрасывает строки вариаций, если что-то не
 * сходится в сопоставлении колонок. Здесь вариации создаются напрямую через
 * API, поэтому результат предсказуем и виден в отчёте.
 *
 * Инструмент идемпотентен: повторный запуск не плодит дубли, а обновляет цены.
 */

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=product',
        'Импорт вариаций',
        'Импорт вариаций',
        'manage_woocommerce',
        'variations-import',
        'deline_render_variations_import'
    );
});

/**
 * Загружает картинку в медиатеку и возвращает её ID.
 * Повторные вызовы с тем же адресом переиспользуют уже загруженный файл,
 * иначе при каждом запуске медиатека пухла бы копиями.
 */
function deline_sideload_once($url, $parent_id = 0) {
    $existing = get_posts([
        'post_type'      => 'attachment',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_deline_source_url',
        'meta_value'     => $url,
    ]);
    if ($existing) {
        return $existing[0];
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $id = media_sideload_image($url, $parent_id, null, 'id');
    if (is_wp_error($id)) {
        return 0;
    }

    update_post_meta($id, '_deline_source_url', $url);
    return $id;
}

/**
 * Находит товар по артикулу и создаёт у него вариации.
 *
 * @param array $spec [['sku' => …, 'attribute' => 'Размер', 'variations' => [['value' => …, 'price' => …]]]]
 * @return array Отчёт по каждому товару
 */
function deline_import_variations(array $spec) {
    $report = [];

    foreach ($spec as $entry) {
        $sku = sanitize_text_field($entry['sku'] ?? '');
        $attribute_label = sanitize_text_field($entry['attribute'] ?? '');
        $rows = $entry['variations'] ?? [];

        if (!$sku || !$attribute_label || !$rows) {
            $report[] = ['sku' => $sku, 'status' => 'error', 'message' => 'Неполные данные'];
            continue;
        }

        $product_id = $sku ? wc_get_product_id_by_sku($sku) : 0;

        // Запасной путь: если артикул не импортировался, ищем по точному названию
        $name = sanitize_text_field($entry['name'] ?? '');
        if (!$product_id && $name) {
            $found = get_posts([
                'post_type'      => 'product',
                'title'          => $name,
                'post_status'    => ['publish', 'draft', 'private'],
                'posts_per_page' => 2,
                'fields'         => 'ids',
            ]);
            if (count($found) === 1) {
                $product_id = $found[0];
                // Заодно проставим артикул, чтобы дальше искать по нему
                if ($sku && !wc_get_product_id_by_sku($sku)) {
                    $p = wc_get_product($product_id);
                    if ($p) { $p->set_sku($sku); $p->save(); }
                }
            } elseif (count($found) > 1) {
                $report[] = ['sku' => $sku, 'status' => 'error',
                             'message' => 'Несколько товаров с таким названием — уточните артикул'];
                continue;
            }
        }

        if (!$product_id) {
            $report[] = ['sku' => $sku, 'status' => 'error',
                         'message' => 'Товар не найден ни по артикулу, ни по названию'];
            continue;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            $report[] = ['sku' => $sku, 'status' => 'error', 'message' => 'Не удалось загрузить товар'];
            continue;
        }

        // Тип мог остаться простым, если импорт не довёл товар до вариативного
        if (!$product->is_type('variable')) {
            wp_set_object_terms($product_id, 'variable', 'product_type');
            $product = wc_get_product($product_id);
        }

        // Ищем нужный атрибут среди атрибутов товара
        $attributes = $product->get_attributes();
        $taxonomy = null;
        foreach ($attributes as $key => $attribute) {
            $label = $attribute->is_taxonomy()
                ? wc_attribute_label($attribute->get_name())
                : $attribute->get_name();

            if (mb_strtolower($label) === mb_strtolower($attribute_label)) {
                $taxonomy = $key;
                break;
            }
        }

        if (!$taxonomy) {
            $report[] = ['sku' => $sku, 'status' => 'error',
                         'message' => sprintf('У товара нет атрибута «%s»', $attribute_label)];
            continue;
        }

        // Без этого флага вариации не появятся в выпадающих списках
        if (!$attributes[$taxonomy]->get_variation()) {
            $attributes[$taxonomy]->set_variation(true);
            $product->set_attributes($attributes);
            $product->save();
        }

        $is_taxonomy = $attributes[$taxonomy]->is_taxonomy();

        // Уже существующие вариации — по значению атрибута
        $existing = [];
        foreach ($product->get_children() as $child_id) {
            $child = wc_get_product($child_id);
            if (!$child) continue;
            $value = $child->get_attribute($taxonomy);
            if ($value !== '') {
                $existing[$value] = $child_id;
            }
        }

        $created = $updated = $skipped = $images = 0;
        $no_url = $img_failed = $img_exists = 0;

        foreach ($rows as $row) {
            $value = sanitize_text_field($row['value'] ?? '');
            $price = isset($row['price']) ? (float) $row['price'] : 0;
            if ($value === '') { $skipped++; continue; }

            // В мете вариации хранится слаг термина, а не его название
            $slug = $value;
            if ($is_taxonomy) {
                $term = get_term_by('name', $value, $taxonomy);
                if (!$term) {
                    $skipped++;
                    continue;
                }
                $slug = $term->slug;
            }

            $image_url = esc_url_raw($row['image'] ?? '');

            if (isset($existing[$value])) {
                $variation = wc_get_product($existing[$value]);
                $variation->set_regular_price($price);

                if (!$image_url) {
                    $no_url++;
                } else {
                    // get_image_id() у вариации подставляет картинку родителя,
                    // поэтому «своё ли это фото» проверяем через мету напрямую
                    $own = (int) get_post_meta($variation->get_id(), '_thumbnail_id', true);
                    if ($own) {
                        $img_exists++;
                    } elseif ($att = deline_sideload_once($image_url, $product_id)) {
                        $variation->set_image_id($att);
                        $images++;
                    } else {
                        $img_failed++;
                    }
                }

                $variation->save();
                $updated++;
                continue;
            }

            $variation = new WC_Product_Variation();
            $variation->set_parent_id($product_id);
            $variation->set_attributes([$taxonomy => $slug]);
            $variation->set_regular_price($price);
            $variation->set_stock_status('instock');
            if (!$image_url) {
                $no_url++;
            } elseif ($att = deline_sideload_once($image_url, $product_id)) {
                $variation->set_image_id($att);
                $images++;
            } else {
                $img_failed++;
            }
            $variation->save();
            $created++;
        }

        WC_Product_Variable::sync($product_id);

        $report[] = [
            'sku'     => $sku,
            'status'  => 'ok',
            'message' => sprintf('создано %d, обновлено %d%s%s%s%s%s',
                $created, $updated,
                $images      ? sprintf(', фото загружено %d', $images) : '',
                $img_exists  ? sprintf(', фото уже было у %d', $img_exists) : '',
                $no_url      ? sprintf(', адрес фото не указан у %d', $no_url) : '',
                $img_failed  ? sprintf(', НЕ УДАЛОСЬ скачать %d', $img_failed) : '',
                $skipped     ? sprintf(', пропущено %d', $skipped) : ''),
        ];
    }

    return $report;
}

/**
 * Включает «использовать для вариаций» у атрибутов, которые уже задействованы
 * в существующих вариациях. Ничего не ищет по артикулу и не требует JSON:
 * данные берутся из самих вариаций, поэтому промахнуться не может.
 */
function deline_fix_variation_flags() {
    $ids = wc_get_products([
        'type'   => 'variable',
        'limit'  => -1,
        'status' => ['publish', 'draft', 'private'],
        'return' => 'ids',
    ]);

    $report = [];

    foreach ($ids as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product) continue;

        // Какие атрибуты реально используются дочерними вариациями
        $used = [];
        foreach ($product->get_children() as $child_id) {
            $child = wc_get_product($child_id);
            if (!$child) continue;
            foreach ($child->get_attributes() as $key => $value) {
                if ($value !== '') {
                    $used[$key] = true;
                }
            }
        }

        if (!$used) {
            $report[] = ['name' => $product->get_name(), 'status' => 'error',
                         'message' => 'нет вариаций с заданными атрибутами'];
            continue;
        }

        $attributes = $product->get_attributes();
        $changed = [];

        foreach ($attributes as $key => $attribute) {
            if (isset($used[$key]) && !$attribute->get_variation()) {
                $attribute->set_variation(true);
                $changed[] = $attribute->is_taxonomy()
                    ? wc_attribute_label($attribute->get_name())
                    : $attribute->get_name();
            }
        }

        if ($changed) {
            $product->set_attributes($attributes);
            $product->save();
            WC_Product_Variable::sync($product_id);
        }

        $report[] = [
            'name'    => $product->get_name(),
            'status'  => 'ok',
            'message' => $changed
                ? 'включено: ' . implode(', ', $changed)
                : 'уже было включено',
        ];
    }

    return $report;
}

/**
 * Удаляет дублирующиеся вариации: те, у которых полностью совпадает набор
 * значений атрибутов. Из каждой группы остаётся одна — предпочтительно
 * та, у которой есть собственное фото.
 */
function deline_dedupe_variations() {
    $ids = wc_get_products([
        'type'   => 'variable',
        'limit'  => -1,
        'status' => ['publish', 'draft', 'private'],
        'return' => 'ids',
    ]);

    $report = [];

    foreach ($ids as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product) continue;

        // Группируем по «подписи» — набору значений атрибутов
        $groups = [];
        foreach ($product->get_children() as $child_id) {
            $child = wc_get_product($child_id);
            if (!$child) continue;

            $attrs = $child->get_attributes();
            ksort($attrs);
            $signature = wp_json_encode($attrs);

            $groups[$signature][] = [
                'id'       => $child_id,
                'own_image' => (int) get_post_meta($child_id, '_thumbnail_id', true),
            ];
        }

        $removed = 0;
        foreach ($groups as $items) {
            if (count($items) < 2) continue;

            // Оставляем ту, у которой есть своё фото
            usort($items, fn($a, $b) => ($b['own_image'] <=> $a['own_image']));
            array_shift($items);

            foreach ($items as $item) {
                wp_delete_post($item['id'], true);
                $removed++;
            }
        }

        if ($removed) {
            WC_Product_Variable::sync($product_id);
        }

        $report[] = [
            'name'    => $product->get_name(),
            'status'  => 'ok',
            'message' => $removed ? sprintf('удалено дублей: %d', $removed) : 'дублей нет',
        ];
    }

    return $report;
}

function deline_render_variations_import() {
    $report = [];
    $report_title = 'Отчёт';

    $valid = isset($_POST['deline_variations_nonce'])
        && wp_verify_nonce($_POST['deline_variations_nonce'], 'deline_import_variations')
        && current_user_can('manage_woocommerce');

    if ($valid && isset($_POST['fix_flags'])) {
        $report = deline_fix_variation_flags();
        $report_title = 'Отчёт: признак «использовать для вариаций»';
        $ok = count(array_filter($report, fn($r) => $r['status'] === 'ok'));
        add_settings_error('deline_variations', 'flags',
            sprintf('Проверено вариативных товаров: %d, обработано: %d.', count($report), $ok), 'updated');

    } elseif ($valid && isset($_POST['dedupe'])) {
        $report = deline_dedupe_variations();
        $report_title = 'Отчёт: удаление дублей';
        $total = array_sum(array_map(
            fn($r) => (int) preg_replace('/\D/', '', $r['message']), $report));
        add_settings_error('deline_variations', 'dedupe',
            sprintf('Проверено товаров: %d, удалено дублей: %d.', count($report), $total), 'updated');

    } elseif ($valid && isset($_POST['import_spec'])) {
        $raw = wp_unslash($_POST['spec'] ?? '');
        $spec = json_decode($raw, true);

        if (!is_array($spec)) {
            add_settings_error('deline_variations', 'json', 'Не удалось разобрать JSON.', 'error');
        } else {
            $report = deline_import_variations($spec);
            $report_title = 'Отчёт: создание вариаций';
            $ok = count(array_filter($report, fn($r) => $r['status'] === 'ok'));
            add_settings_error('deline_variations', 'done',
                sprintf('Обработано товаров: %d, успешно: %d.', count($report), $ok), 'updated');
        }
    }
    ?>
    <div class="wrap">
        <h1>Импорт вариаций</h1>
        <p class="description">
            Два независимых действия. Оба безопасно запускать повторно.
        </p>

        <?php settings_errors('deline_variations'); ?>

        <div style="padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;max-width:900px;">
            <h2 style="margin-top:0;">Починить признак «Используется для вариаций»</h2>
            <p class="description">
                Проходит по всем вариативным товарам, смотрит, какие атрибуты задействованы
                в уже созданных вариациях, и включает у них этот признак. Артикулы и JSON не нужны —
                данные берутся из самих вариаций. Именно из-за выключенного признака не появляются
                выпадающие списки, а цена показывается диапазоном.
            </p>
            <form method="post">
                <?php wp_nonce_field('deline_import_variations', 'deline_variations_nonce'); ?>
                <?php submit_button('Включить у всех товаров', 'primary', 'fix_flags', false); ?>
            </form>
        </div>

        <div style="padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;max-width:900px;margin-top:20px;">
            <h2 style="margin-top:0;">Удалить дублирующиеся вариации</h2>
            <p class="description">
                Повторный импорт CSV мог создать по две вариации на один и тот же размер.
                Лишние мешают: на странице товара срабатывает первая совпавшая, и если это дубль
                без своего фото, картинка при выборе размера не меняется.
                Из каждой пары остаётся вариация с фото. <strong>Удаление необратимо.</strong>
            </p>
            <form method="post">
                <?php wp_nonce_field('deline_import_variations', 'deline_variations_nonce'); ?>
                <?php submit_button('Удалить дубли', 'delete', 'dedupe', false); ?>
            </form>
        </div>

        <div style="padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;max-width:900px;margin-top:20px;">
            <h2 style="margin-top:0;">Создать вариации из спецификации</h2>
            <p class="description">
                Нужно, только если вариаций ещё нет. Товары ищутся по артикулу, а если его нет — по названию.
            </p>
            <form method="post">
                <?php wp_nonce_field('deline_import_variations', 'deline_variations_nonce'); ?>
                <textarea name="spec" rows="8" style="width:100%;font-family:monospace;" placeholder='[{"sku":"…","name":"…","attribute":"Размер","variations":[{"value":"…","price":0}]}]'></textarea>
                <?php submit_button('Создать вариации', 'secondary', 'import_spec'); ?>
            </form>
        </div>

        <?php if ($report): ?>
        <h2><?php echo esc_html($report_title); ?></h2>
        <table class="widefat striped" style="max-width:900px;">
            <thead><tr><th>Товар</th><th>Результат</th></tr></thead>
            <tbody>
            <?php foreach ($report as $row): ?>
                <tr>
                    <td><?php echo esc_html($row['name'] ?? $row['sku'] ?? ''); ?></td>
                    <td<?php echo $row['status'] === 'error' ? ' style="color:#b32d2e;"' : ''; ?>>
                        <?php echo esc_html($row['message']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php
}
