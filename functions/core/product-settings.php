<?php
/**
 * Общие блоки карточки товара: кнопки и блок «как мы работаем».
 * Одинаковы для всех товаров, поэтому живут в настройках, а не в мете.
 */

add_action('admin_menu', function () {
    add_menu_page(
        'Карточка товара',
        'Карточка товара',
        'manage_options',
        'product-settings',
        'deline_render_product_page',
        'dashicons-products',
        8
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_product-settings') return;
    wp_enqueue_media();
});

function deline_get_product_buttons() {
    // false отличает «ещё не сохраняли» от «сохранили пустым»,
    // иначе очищенный список каждый раз возвращал бы значения по умолчанию
    $saved = get_option('deline_product_buttons', false);
    if ($saved !== false) return $saved;

    return [
        ['label' => 'Заказать расчет',   'url' => '', 'icon_id' => 0, 'action' => 'form'],
        ['label' => 'Заказать проект',   'url' => '', 'icon_id' => 0, 'action' => 'form'],
        ['label' => 'Вызвать дизайнера', 'url' => '', 'icon_id' => 0, 'action' => 'form'],
    ];
}

function deline_get_product_steps() {
    $saved = get_option('deline_product_steps', false);
    if ($saved !== false) return $saved;

    return [
        ['text' => 'обсудим вместе ваше пространство'],
        ['text' => 'сделаем грамотный проект'],
        ['text' => 'сделаем варианты расчетов.'],
    ];
}

add_action('admin_init', function () {
    if (
        !isset($_POST['deline_product_nonce']) ||
        !wp_verify_nonce($_POST['deline_product_nonce'], 'deline_save_product')
    ) return;

    if (!current_user_can('manage_options')) return;

    $buttons = [];
    if (!empty($_POST['product_buttons']) && is_array($_POST['product_buttons'])) {
        foreach ($_POST['product_buttons'] as $btn) {
            $label = sanitize_text_field($btn['label'] ?? '');
            if (!$label) continue;
            $action = ($btn['action'] ?? 'link') === 'form' ? 'form' : 'link';
            $buttons[] = [
                'label'   => $label,
                'url'     => esc_url_raw($btn['url'] ?? ''),
                'icon_id' => absint($btn['icon_id'] ?? 0),
                'action'  => $action,
            ];
        }
    }
    update_option('deline_product_buttons', $buttons);

    $steps = [];
    if (!empty($_POST['product_steps']) && is_array($_POST['product_steps'])) {
        foreach ($_POST['product_steps'] as $step) {
            $text = sanitize_text_field($step['text'] ?? '');
            if (!$text) continue;
            $steps[] = ['text' => $text];
        }
    }
    update_option('deline_product_steps', $steps);

    add_settings_error('deline_product', 'success', 'Настройки карточки товара сохранены.', 'updated');
    set_transient('deline_product_errors', get_settings_errors('deline_product'), 30);
});

function deline_render_product_page() {
    $buttons = deline_get_product_buttons();
    $steps   = deline_get_product_steps();

    if ($errors = get_transient('deline_product_errors')) {
        delete_transient('deline_product_errors');
        foreach ($errors as $error) {
            add_settings_error($error['setting'], $error['code'], $error['message'], $error['type']);
        }
    }
    ?>
    <div class="wrap">
        <h1>Карточка товара</h1>
        <p class="description">Эти блоки выводятся на странице каждого товара.</p>

        <?php settings_errors('deline_product'); ?>

        <form method="post">
            <?php wp_nonce_field('deline_save_product', 'deline_product_nonce'); ?>

            <h2>Кнопки</h2>
            <p class="description">Сколько угодно кнопок или ни одной. Иконка необязательна, поддерживается SVG.</p>

            <div id="pbtn-repeater" data-next="<?php echo count($buttons); ?>" style="margin-top: 12px;">
                <?php foreach ($buttons as $i => $btn):
                    $icon_id  = $btn['icon_id'] ?? 0;
                    $icon_url = $icon_id ? wp_get_attachment_url($icon_id) : '';
                ?>
                <div class="pbtn-row" style="display: flex; align-items: flex-end; gap: 12px; padding: 12px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 8px;">
                    <div style="text-align: center;">
                        <div class="pbtn-icon-preview" style="width: 56px; height: 56px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f9f9f9;">
                            <?php if ($icon_url): ?><img src="<?php echo esc_url($icon_url); ?>" style="max-width: 80%; max-height: 80%; object-fit: contain;"><?php endif; ?>
                        </div>
                        <input type="hidden" name="product_buttons[<?php echo $i; ?>][icon_id]" class="pbtn-icon-id" value="<?php echo esc_attr($icon_id); ?>">
                        <button type="button" class="button button-small pbtn-upload" style="margin-top: 4px;">Иконка</button>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Подпись</label>
                        <input type="text" name="product_buttons[<?php echo $i; ?>][label]" value="<?php echo esc_attr($btn['label']); ?>" style="width: 100%;" placeholder="Заказать расчёт">
                    </div>
                    <div style="width: 150px;">
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Действие</label>
                        <select name="product_buttons[<?php echo $i; ?>][action]" class="pbtn-action" style="width: 100%;">
                            <option value="form" <?php selected(($btn['action'] ?? 'link'), 'form'); ?>>Открыть форму</option>
                            <option value="link" <?php selected(($btn['action'] ?? 'link'), 'link'); ?>>Перейти по ссылке</option>
                        </select>
                    </div>
                    <div style="flex: 1;" class="pbtn-url-field"<?php echo ($btn['action'] ?? 'link') === 'form' ? ' hidden' : ''; ?>>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Ссылка</label>
                        <input type="url" name="product_buttons[<?php echo $i; ?>][url]" value="<?php echo esc_attr($btn['url'] ?? ''); ?>" style="width: 100%;" placeholder="https://">
                    </div>
                    <button type="button" class="button pbtn-remove" style="color: #b32d2e;">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button" id="pbtn-add">+ Добавить кнопку</button>

            <h2 style="margin-top: 32px;">Блок «как мы работаем»</h2>
            <p class="description">Номера проставляются автоматически по порядку.</p>

            <div id="pstep-repeater" data-next="<?php echo count($steps); ?>" style="margin-top: 12px;">
                <?php foreach ($steps as $i => $step): ?>
                <div class="pstep-row" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 8px;">
                    <strong style="min-width: 24px; font-size: 20px; color: #20436c;"><?php echo $i + 1; ?></strong>
                    <input type="text" name="product_steps[<?php echo $i; ?>][text]" value="<?php echo esc_attr($step['text']); ?>" style="flex: 1;" placeholder="обсудим вместе ваше пространство">
                    <button type="button" class="button pstep-remove" style="color: #b32d2e;">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button" id="pstep-add">+ Добавить шаг</button>

            <?php submit_button('Сохранить'); ?>
        </form>
    </div>

    <script>
    jQuery(function($) {
        // Индексы монотонные: после удаления строки новая не займёт освободившийся индекс
        function nextIndex($wrap) {
            var n = parseInt($wrap.attr('data-next'), 10) || 0;
            $wrap.attr('data-next', n + 1);
            return n;
        }

        $('#pbtn-add').on('click', function() {
            var $wrap = $('#pbtn-repeater');
            var i = nextIndex($wrap);
            var n = 'product_buttons[' + i + ']';
            $wrap.append(
                '<div class="pbtn-row" style="display:flex;align-items:flex-end;gap:12px;padding:12px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:8px;">'
                + '<div style="text-align:center;">'
                + '<div class="pbtn-icon-preview" style="width:56px;height:56px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f9f9f9;"></div>'
                + '<input type="hidden" name="' + n + '[icon_id]" class="pbtn-icon-id" value="0">'
                + '<button type="button" class="button button-small pbtn-upload" style="margin-top:4px;">Иконка</button>'
                + '</div>'
                + '<div style="flex:1;"><label style="display:block;margin-bottom:4px;font-weight:600;">Подпись</label>'
                + '<input type="text" name="' + n + '[label]" style="width:100%;" placeholder="Заказать расчёт"></div>'
                + '<div style="width:150px;"><label style="display:block;margin-bottom:4px;font-weight:600;">Действие</label>'
                + '<select name="' + n + '[action]" class="pbtn-action" style="width:100%;">'
                + '<option value="form">Открыть форму</option><option value="link">Перейти по ссылке</option>'
                + '</select></div>'
                + '<div style="flex:1;" class="pbtn-url-field" hidden><label style="display:block;margin-bottom:4px;font-weight:600;">Ссылка</label>'
                + '<input type="url" name="' + n + '[url]" style="width:100%;" placeholder="https://"></div>'
                + '<button type="button" class="button pbtn-remove" style="color:#b32d2e;">&times;</button>'
                + '</div>'
            );
        });

        // Поле ссылки нужно только варианту «Перейти по ссылке»
        $('#pbtn-repeater').on('change', '.pbtn-action', function() {
            $(this).closest('.pbtn-row').find('.pbtn-url-field').prop('hidden', $(this).val() === 'form');
        });

        $('#pbtn-repeater').on('click', '.pbtn-remove', function() {
            $(this).closest('.pbtn-row').remove();
        });

        $('#pbtn-repeater').on('click', '.pbtn-upload', function() {
            var $wrap = $(this).parent();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                $wrap.find('.pbtn-icon-id').val(att.id);
                $wrap.find('.pbtn-icon-preview').html('<img src="' + att.url + '" style="max-width:80%;max-height:80%;object-fit:contain;">');
            });
            frame.open();
        });

        function renumberSteps() {
            $('#pstep-repeater .pstep-row').each(function(i) {
                $(this).find('strong').text(i + 1);
            });
        }

        $('#pstep-add').on('click', function() {
            var $wrap = $('#pstep-repeater');
            var i = nextIndex($wrap);
            $wrap.append(
                '<div class="pstep-row" style="display:flex;align-items:center;gap:12px;padding:12px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:8px;">'
                + '<strong style="min-width:24px;font-size:20px;color:#20436c;"></strong>'
                + '<input type="text" name="product_steps[' + i + '][text]" style="flex:1;" placeholder="обсудим вместе ваше пространство">'
                + '<button type="button" class="button pstep-remove" style="color:#b32d2e;">&times;</button>'
                + '</div>'
            );
            renumberSteps();
        });

        $('#pstep-repeater').on('click', '.pstep-remove', function() {
            $(this).closest('.pstep-row').remove();
            renumberSteps();
        });
    });
    </script>
    <?php
}
