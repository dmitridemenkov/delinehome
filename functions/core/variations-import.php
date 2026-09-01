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

        $created = $updated = $skipped = 0;

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

            if (isset($existing[$value])) {
                $variation = wc_get_product($existing[$value]);
                $variation->set_regular_price($price);
                $variation->save();
                $updated++;
                continue;
            }

            $variation = new WC_Product_Variation();
            $variation->set_parent_id($product_id);
            $variation->set_attributes([$taxonomy => $slug]);
            $variation->set_regular_price($price);
            $variation->set_stock_status('instock');
            $variation->save();
            $created++;
        }

        WC_Product_Variable::sync($product_id);

        $report[] = [
            'sku'     => $sku,
            'status'  => 'ok',
            'message' => sprintf('создано %d, обновлено %d%s', $created, $updated,
                                 $skipped ? sprintf(', пропущено %d', $skipped) : ''),
        ];
    }

    return $report;
}

function deline_render_variations_import() {
    $report = [];

    if (
        isset($_POST['deline_variations_nonce']) &&
        wp_verify_nonce($_POST['deline_variations_nonce'], 'deline_import_variations') &&
        current_user_can('manage_woocommerce')
    ) {
        $raw = wp_unslash($_POST['spec'] ?? '');
        $spec = json_decode($raw, true);

        if (!is_array($spec)) {
            add_settings_error('deline_variations', 'json', 'Не удалось разобрать JSON.', 'error');
        } else {
            $report = deline_import_variations($spec);
            $ok = count(array_filter($report, fn($r) => $r['status'] === 'ok'));
            add_settings_error('deline_variations', 'done',
                sprintf('Обработано товаров: %d, успешно: %d.', count($report), $ok), 'updated');
        }
    }
    ?>
    <div class="wrap">
        <h1>Импорт вариаций</h1>
        <p class="description">
            Вставьте содержимое файла <code>variations.json</code>. Товары ищутся по артикулу.
            Повторный запуск безопасен: существующие вариации не дублируются, у них обновляется цена.
        </p>

        <?php settings_errors('deline_variations'); ?>

        <form method="post">
            <?php wp_nonce_field('deline_import_variations', 'deline_variations_nonce'); ?>
            <textarea name="spec" rows="12" style="width:100%;font-family:monospace;" placeholder='[{"sku":"…","attribute":"Размер","variations":[{"value":"…","price":0}]}]'></textarea>
            <?php submit_button('Создать вариации'); ?>
        </form>

        <?php if ($report): ?>
        <h2>Отчёт</h2>
        <table class="widefat striped" style="max-width:900px;">
            <thead><tr><th>Артикул</th><th>Результат</th></tr></thead>
            <tbody>
            <?php foreach ($report as $row): ?>
                <tr>
                    <td><code><?php echo esc_html($row['sku']); ?></code></td>
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
