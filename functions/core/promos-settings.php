<?php

add_action('admin_menu', function () {
    add_menu_page(
        'Акции',
        'Акции',
        'manage_options',
        'promos-settings',
        'deline_render_promos_page',
        'dashicons-megaphone',
        7
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_promos-settings') return;
    wp_enqueue_media();
    wp_enqueue_editor();
});

function deline_get_promos_options() {
    return get_option('deline_promos_options', ['interval' => 6]);
}

function deline_get_promos() {
    return get_option('deline_promos', []);
}

add_action('admin_init', function () {
    if (
        !isset($_POST['deline_promos_nonce']) ||
        !wp_verify_nonce($_POST['deline_promos_nonce'], 'deline_save_promos')
    ) return;

    if (!current_user_can('manage_options')) return;

    update_option('deline_promos_options', [
        'interval' => max(1, min(30, absint($_POST['promos_interval'] ?? 6))),
    ]);

    $promos = [];

    if (!empty($_POST['promos']) && is_array($_POST['promos'])) {
        foreach ($_POST['promos'] as $item) {
            $title = sanitize_text_field($item['title'] ?? '');
            $image_id = absint($item['image_id'] ?? 0);

            if (!$title && !$image_id) continue;

            $promos[] = [
                'title'      => $title,
                'content'    => wp_kses_post($item['content'] ?? ''),
                'image_id'   => $image_id,
                'avif_id'    => absint($item['avif_id'] ?? 0),
                'btn1_label' => sanitize_text_field($item['btn1_label'] ?? ''),
                'btn1_url'   => esc_url_raw($item['btn1_url'] ?? ''),
                'btn2_label' => sanitize_text_field($item['btn2_label'] ?? ''),
                'btn2_url'   => esc_url_raw($item['btn2_url'] ?? ''),
            ];
        }
    }

    update_option('deline_promos', $promos);

    add_settings_error('deline_promos', 'success', 'Акции сохранены.', 'updated');
    set_transient('deline_promos_errors', get_settings_errors('deline_promos'), 30);
});

function deline_render_promos_page() {
    $promos = deline_get_promos();
    $opts   = deline_get_promos_options();

    if ($errors = get_transient('deline_promos_errors')) {
        delete_transient('deline_promos_errors');
        foreach ($errors as $error) {
            add_settings_error($error['setting'], $error['code'], $error['message'], $error['type']);
        }
    }

    $img_fields = [
        ['key' => 'image_id', 'label' => 'Изображение (png/jpg)'],
        ['key' => 'avif_id',  'label' => 'Изображение (avif)'],
    ];

    $editor_settings = [
        'media_buttons' => false,
        'textarea_rows' => 6,
        'teeny'         => true,
        'quicktags'     => true,
    ];
    ?>
    <div class="wrap">
        <h1>Акции</h1>

        <?php settings_errors('deline_promos'); ?>

        <form method="post" id="promos-form">
            <?php wp_nonce_field('deline_save_promos', 'deline_promos_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="promos_interval">Вставлять каждые N товаров</label></th>
                    <td>
                        <input type="number" id="promos_interval" name="promos_interval"
                               value="<?php echo esc_attr($opts['interval']); ?>" min="1" max="30" style="width: 70px;">
                        <p class="description">
                            Акции вставляются в сетку каталога через указанное число товаров.
                            Если акций несколько — чередуются по кругу. После последнего товара акция не выводится.
                        </p>
                    </td>
                </tr>
            </table>

            <div id="promos-repeater" style="margin-top: 16px;">
                <?php foreach ($promos as $i => $item): ?>
                <div class="promo-row" style="padding: 16px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <strong>Акция <?php echo $i + 1; ?></strong>
                        <button type="button" class="button remove-promo" style="color: #b32d2e;">&times; Удалить</button>
                    </div>

                    <p>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Заголовок</label>
                        <input type="text" name="promos[<?php echo $i; ?>][title]" value="<?php echo esc_attr($item['title']); ?>" class="regular-text" style="width: 100%;" placeholder="Верхние шкафы в подарок!">
                    </p>

                    <p style="margin-bottom: 4px; font-weight: 600;">Текст</p>
                    <?php
                    wp_editor(
                        $item['content'] ?? '',
                        'promo_content_' . $i,
                        array_merge($editor_settings, [
                            'textarea_name' => 'promos[' . $i . '][content]',
                        ])
                    );
                    ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">Кнопка 1 — подпись</label>
                            <input type="text" name="promos[<?php echo $i; ?>][btn1_label]" value="<?php echo esc_attr($item['btn1_label'] ?? ''); ?>" style="width: 100%;" placeholder="Добавить акцию">
                            <label style="display: block; margin: 8px 0 4px; font-weight: 600;">Кнопка 1 — ссылка</label>
                            <input type="url" name="promos[<?php echo $i; ?>][btn1_url]" value="<?php echo esc_attr($item['btn1_url'] ?? ''); ?>" style="width: 100%;" placeholder="https://">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">Кнопка 2 — подпись</label>
                            <input type="text" name="promos[<?php echo $i; ?>][btn2_label]" value="<?php echo esc_attr($item['btn2_label'] ?? ''); ?>" style="width: 100%;" placeholder="Заказать расчёт">
                            <label style="display: block; margin: 8px 0 4px; font-weight: 600;">Кнопка 2 — ссылка</label>
                            <input type="url" name="promos[<?php echo $i; ?>][btn2_url]" value="<?php echo esc_attr($item['btn2_url'] ?? ''); ?>" style="width: 100%;" placeholder="https://">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 200px); gap: 12px; margin-top: 12px;">
                        <?php foreach ($img_fields as $field):
                            $val = $item[$field['key']] ?? 0;
                            $url = $val ? wp_get_attachment_image_url($val, 'thumbnail') : '';
                        ?>
                        <div style="text-align: center;">
                            <div class="promo-img-preview" style="width: 100%; height: 90px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 6px; background: #f9f9f9;">
                                <?php if ($url): ?>
                                    <img src="<?php echo esc_url($url); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="promos[<?php echo $i; ?>][<?php echo $field['key']; ?>]" class="promo-img-id" value="<?php echo esc_attr($val); ?>">
                            <button type="button" class="button button-small upload-promo-img"><?php echo $field['label']; ?></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button" id="add-promo" style="margin-top: 4px;">+ Добавить акцию</button>

            <?php submit_button('Сохранить'); ?>
        </form>
    </div>

    <script>
    jQuery(function($) {
        var promoIndex = <?php echo count($promos); ?>;
        var imgFields = <?php echo wp_json_encode($img_fields); ?>;

        function imgFieldHtml(idx, key, label) {
            return '<div style="text-align:center;">'
                + '<div class="promo-img-preview" style="width:100%;height:90px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:6px;background:#f9f9f9;"></div>'
                + '<input type="hidden" name="promos[' + idx + '][' + key + ']" class="promo-img-id" value="0">'
                + '<button type="button" class="button button-small upload-promo-img">' + label + '</button>'
                + '</div>';
        }

        $('#add-promo').on('click', function() {
            var idx = promoIndex;
            var editorId = 'promo_content_' + idx;

            var imgs = '';
            imgFields.forEach(function(f) { imgs += imgFieldHtml(idx, f.key, f.label); });

            var html = '<div class="promo-row" style="padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:12px;">'
                + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">'
                + '<strong>Акция ' + (idx + 1) + '</strong>'
                + '<button type="button" class="button remove-promo" style="color:#b32d2e;">&times; Удалить</button>'
                + '</div>'
                + '<p><label style="display:block;margin-bottom:4px;font-weight:600;">Заголовок</label>'
                + '<input type="text" name="promos[' + idx + '][title]" class="regular-text" style="width:100%;" placeholder="Верхние шкафы в подарок!"></p>'
                + '<p style="margin-bottom:4px;font-weight:600;">Текст</p>'
                + '<textarea id="' + editorId + '" name="promos[' + idx + '][content]" rows="6" style="width:100%;"></textarea>'
                + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">'
                + '<div><label style="display:block;margin-bottom:4px;font-weight:600;">Кнопка 1 — подпись</label>'
                + '<input type="text" name="promos[' + idx + '][btn1_label]" style="width:100%;" placeholder="Добавить акцию">'
                + '<label style="display:block;margin:8px 0 4px;font-weight:600;">Кнопка 1 — ссылка</label>'
                + '<input type="url" name="promos[' + idx + '][btn1_url]" style="width:100%;" placeholder="https://"></div>'
                + '<div><label style="display:block;margin-bottom:4px;font-weight:600;">Кнопка 2 — подпись</label>'
                + '<input type="text" name="promos[' + idx + '][btn2_label]" style="width:100%;" placeholder="Заказать расчёт">'
                + '<label style="display:block;margin:8px 0 4px;font-weight:600;">Кнопка 2 — ссылка</label>'
                + '<input type="url" name="promos[' + idx + '][btn2_url]" style="width:100%;" placeholder="https://"></div>'
                + '</div>'
                + '<div style="display:grid;grid-template-columns:repeat(2,200px);gap:12px;margin-top:12px;">' + imgs + '</div>'
                + '</div>';

            $('#promos-repeater').append(html);

            wp.editor.initialize(editorId, {
                tinymce: { wpautop: true, toolbar1: 'bold,italic,bullist,numlist,link,unlink,undo,redo' },
                quicktags: true
            });

            promoIndex++;
        });

        $('#promos-repeater').on('click', '.remove-promo', function() {
            var $row = $(this).closest('.promo-row');
            var $textarea = $row.find('textarea[id^="promo_content_"]');
            if ($textarea.length) {
                wp.editor.remove($textarea.attr('id'));
            }
            $row.remove();
        });

        $('#promos-repeater').on('click', '.upload-promo-img', function() {
            var $wrap = $(this).parent();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                $wrap.find('.promo-img-id').val(att.id);
                $wrap.find('.promo-img-preview').html('<img src="' + thumb + '" style="max-width:100%;max-height:100%;object-fit:contain;">');
            });
            frame.open();
        });

        $('#promos-form').on('submit', function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });
    });
    </script>
    <?php
}
