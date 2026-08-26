<?php

add_action('admin_menu', function () {
    add_menu_page(
        'Материалы',
        'Материалы',
        'manage_options',
        'materials-settings',
        'deline_render_materials_page',
        'dashicons-screenoptions',
        6
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_materials-settings') return;
    wp_enqueue_media();
    wp_enqueue_editor();
});

function deline_get_materials() {
    return get_option('deline_materials', []);
}

add_action('admin_init', function () {
    if (
        !isset($_POST['deline_materials_nonce']) ||
        !wp_verify_nonce($_POST['deline_materials_nonce'], 'deline_save_materials')
    ) return;

    if (!current_user_can('manage_options')) return;

    $materials = [];

    if (!empty($_POST['materials']) && is_array($_POST['materials'])) {
        foreach ($_POST['materials'] as $item) {
            $title = sanitize_text_field($item['title'] ?? '');
            $image_id = absint($item['image_id'] ?? 0);

            // Пропускаем полностью пустые строки
            if (!$title && !$image_id) continue;

            $materials[] = [
                'title'    => $title,
                'content'  => wp_kses_post($item['content'] ?? ''),
                'image_id' => $image_id,
                'avif_id'  => absint($item['avif_id'] ?? 0),
            ];
        }
    }

    update_option('deline_materials', $materials);

    add_settings_error('deline_materials', 'success', 'Материалы сохранены.', 'updated');
    set_transient('deline_materials_errors', get_settings_errors('deline_materials'), 30);
});

function deline_render_materials_page() {
    $materials = deline_get_materials();

    if ($errors = get_transient('deline_materials_errors')) {
        delete_transient('deline_materials_errors');
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
        'textarea_rows' => 8,
        'teeny'         => true,
        'quicktags'     => true,
    ];
    ?>
    <div class="wrap">
        <h1>Материалы</h1>

        <?php settings_errors('deline_materials'); ?>

        <form method="post" id="materials-form">
            <?php wp_nonce_field('deline_save_materials', 'deline_materials_nonce'); ?>

            <p class="description">
                Блоки выводятся в порядке добавления — на главной и на странице «Поставщики материалов».
                На мобильных изображение показывается сверху, текст под ним по центру.
            </p>

            <div id="materials-repeater" style="margin-top: 16px;">
                <?php foreach ($materials as $i => $item): ?>
                <div class="material-row" style="padding: 16px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <strong>Блок <?php echo $i + 1; ?></strong>
                        <button type="button" class="button remove-material" style="color: #b32d2e;">&times; Удалить</button>
                    </div>

                    <p>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Заголовок</label>
                        <input type="text" name="materials[<?php echo $i; ?>][title]" value="<?php echo esc_attr($item['title']); ?>" class="regular-text" style="width: 100%;" placeholder="Премиальные ящики Avantech от Hettich">
                    </p>

                    <p style="margin-bottom: 4px; font-weight: 600;">Текст</p>
                    <?php
                    wp_editor(
                        $item['content'] ?? '',
                        'material_content_' . $i,
                        array_merge($editor_settings, [
                            'textarea_name' => 'materials[' . $i . '][content]',
                        ])
                    );
                    ?>

                    <div style="display: grid; grid-template-columns: repeat(2, 200px); gap: 12px; margin-top: 12px;">
                        <?php foreach ($img_fields as $field):
                            $val = $item[$field['key']] ?? 0;
                            $url = $val ? wp_get_attachment_image_url($val, 'thumbnail') : '';
                        ?>
                        <div style="text-align: center;">
                            <div class="material-img-preview" style="width: 100%; height: 90px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 6px; background: #f9f9f9;">
                                <?php if ($url): ?>
                                    <img src="<?php echo esc_url($url); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="materials[<?php echo $i; ?>][<?php echo $field['key']; ?>]" class="material-img-id" value="<?php echo esc_attr($val); ?>">
                            <button type="button" class="button button-small upload-material-img"><?php echo $field['label']; ?></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button" id="add-material" style="margin-top: 4px;">+ Добавить блок</button>

            <?php submit_button('Сохранить'); ?>
        </form>
    </div>

    <script>
    jQuery(function($) {
        var materialIndex = <?php echo count($materials); ?>;
        var imgFields = <?php echo wp_json_encode($img_fields); ?>;

        function imgFieldHtml(idx, key, label) {
            return '<div style="text-align:center;">'
                + '<div class="material-img-preview" style="width:100%;height:90px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:6px;background:#f9f9f9;"></div>'
                + '<input type="hidden" name="materials[' + idx + '][' + key + ']" class="material-img-id" value="0">'
                + '<button type="button" class="button button-small upload-material-img">' + label + '</button>'
                + '</div>';
        }

        $('#add-material').on('click', function() {
            var idx = materialIndex;
            var editorId = 'material_content_' + idx;

            var imgs = '';
            imgFields.forEach(function(f) { imgs += imgFieldHtml(idx, f.key, f.label); });

            var html = '<div class="material-row" style="padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:12px;">'
                + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">'
                + '<strong>Блок ' + (idx + 1) + '</strong>'
                + '<button type="button" class="button remove-material" style="color:#b32d2e;">&times; Удалить</button>'
                + '</div>'
                + '<p><label style="display:block;margin-bottom:4px;font-weight:600;">Заголовок</label>'
                + '<input type="text" name="materials[' + idx + '][title]" class="regular-text" style="width:100%;" placeholder="Премиальные ящики Avantech от Hettich"></p>'
                + '<p style="margin-bottom:4px;font-weight:600;">Текст</p>'
                + '<textarea id="' + editorId + '" name="materials[' + idx + '][content]" rows="8" style="width:100%;"></textarea>'
                + '<div style="display:grid;grid-template-columns:repeat(2,200px);gap:12px;margin-top:12px;">' + imgs + '</div>'
                + '</div>';

            $('#materials-repeater').append(html);

            // Поднимаем визуальный редактор на только что добавленном поле
            wp.editor.initialize(editorId, {
                tinymce: { wpautop: true, toolbar1: 'bold,italic,bullist,numlist,link,unlink,undo,redo' },
                quicktags: true
            });

            materialIndex++;
        });

        $('#materials-repeater').on('click', '.remove-material', function() {
            var $row = $(this).closest('.material-row');
            var $textarea = $row.find('textarea[id^="material_content_"]');
            if ($textarea.length) {
                wp.editor.remove($textarea.attr('id'));
            }
            $row.remove();
        });

        $('#materials-repeater').on('click', '.upload-material-img', function() {
            var $wrap = $(this).parent();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                $wrap.find('.material-img-id').val(att.id);
                $wrap.find('.material-img-preview').html('<img src="' + thumb + '" style="max-width:100%;max-height:100%;object-fit:contain;">');
            });
            frame.open();
        });

        // Переносим содержимое TinyMCE в textarea перед отправкой формы
        $('#materials-form').on('submit', function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });
    });
    </script>
    <?php
}
