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
    $promos = get_option('deline_promos', []);

    // Совместимость: раньше кнопки хранились как две фиксированные пары btn1_/btn2_.
    // Разворачиваем их в массив, чтобы уже сохранённые акции не потерялись.
    foreach ($promos as &$promo) {
        if (isset($promo['buttons']) && is_array($promo['buttons'])) continue;

        $promo['buttons'] = [];
        foreach ([1, 2] as $n) {
            $label = $promo["btn{$n}_label"] ?? '';
            if (!$label) continue;
            $promo['buttons'][] = [
                'label'   => $label,
                'url'     => $promo["btn{$n}_url"] ?? '',
                'icon_id' => 0,
            ];
        }
    }
    unset($promo);

    return $promos;
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

            $buttons = [];
            if (!empty($item['buttons']) && is_array($item['buttons'])) {
                foreach ($item['buttons'] as $btn) {
                    $label = sanitize_text_field($btn['label'] ?? '');
                    if (!$label) continue;
                    $buttons[] = [
                        'label'   => $label,
                        'url'     => esc_url_raw($btn['url'] ?? ''),
                        'icon_id' => absint($btn['icon_id'] ?? 0),
                    ];
                }
            }

            $promos[] = [
                'title'    => $title,
                'content'  => wp_kses_post($item['content'] ?? ''),
                'image_id' => $image_id,
                'avif_id'  => absint($item['avif_id'] ?? 0),
                'buttons'  => $buttons,
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

                    <p style="margin: 16px 0 4px; font-weight: 600;">Кнопки</p>
                    <p class="description" style="margin-bottom: 8px;">
                        Можно не добавлять ни одной или добавить сколько нужно. Иконка необязательна, поддерживается SVG.
                    </p>

                    <?php $buttons = $item['buttons'] ?? []; ?>
                    <div class="promo-buttons" data-promo="<?php echo $i; ?>" data-next="<?php echo count($buttons); ?>">
                        <?php foreach ($buttons as $j => $btn):
                            $icon_id  = $btn['icon_id'] ?? 0;
                            $icon_url = $icon_id ? wp_get_attachment_url($icon_id) : '';
                        ?>
                        <div class="promo-btn-row" style="display: flex; align-items: flex-end; gap: 12px; padding: 12px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px; margin-bottom: 8px;">
                            <div style="text-align: center;">
                                <div class="promo-btn-icon-preview" style="width: 56px; height: 56px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #fff;">
                                    <?php if ($icon_url): ?>
                                        <img src="<?php echo esc_url($icon_url); ?>" style="max-width: 80%; max-height: 80%; object-fit: contain;">
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="promos[<?php echo $i; ?>][buttons][<?php echo $j; ?>][icon_id]" class="promo-btn-icon-id" value="<?php echo esc_attr($icon_id); ?>">
                                <button type="button" class="button button-small upload-btn-icon" style="margin-top: 4px;">Иконка</button>
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 4px; font-weight: 600;">Подпись</label>
                                <input type="text" name="promos[<?php echo $i; ?>][buttons][<?php echo $j; ?>][label]" value="<?php echo esc_attr($btn['label'] ?? ''); ?>" style="width: 100%;" placeholder="Добавить акцию">
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 4px; font-weight: 600;">Ссылка</label>
                                <input type="url" name="promos[<?php echo $i; ?>][buttons][<?php echo $j; ?>][url]" value="<?php echo esc_attr($btn['url'] ?? ''); ?>" style="width: 100%;" placeholder="https://">
                            </div>
                            <button type="button" class="button remove-promo-btn" style="color: #b32d2e;">&times;</button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="button button-small add-promo-btn">+ Добавить кнопку</button>

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

        // Строка кнопки во вложенном репитере
        function btnRowHtml(promoIdx, btnIdx) {
            var n = 'promos[' + promoIdx + '][buttons][' + btnIdx + ']';
            return '<div class="promo-btn-row" style="display:flex;align-items:flex-end;gap:12px;padding:12px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;margin-bottom:8px;">'
                + '<div style="text-align:center;">'
                + '<div class="promo-btn-icon-preview" style="width:56px;height:56px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fff;"></div>'
                + '<input type="hidden" name="' + n + '[icon_id]" class="promo-btn-icon-id" value="0">'
                + '<button type="button" class="button button-small upload-btn-icon" style="margin-top:4px;">Иконка</button>'
                + '</div>'
                + '<div style="flex:1;"><label style="display:block;margin-bottom:4px;font-weight:600;">Подпись</label>'
                + '<input type="text" name="' + n + '[label]" style="width:100%;" placeholder="Добавить акцию"></div>'
                + '<div style="flex:1;"><label style="display:block;margin-bottom:4px;font-weight:600;">Ссылка</label>'
                + '<input type="url" name="' + n + '[url]" style="width:100%;" placeholder="https://"></div>'
                + '<button type="button" class="button remove-promo-btn" style="color:#b32d2e;">&times;</button>'
                + '</div>';
        }

        $('#promos-repeater').on('click', '.add-promo-btn', function() {
            var $wrap = $(this).closest('.promo-row').find('.promo-buttons');
            var promoIdx = $wrap.attr('data-promo');
            // Счётчик монотонный: после удаления строк индексы не переиспользуются
            var btnIdx = parseInt($wrap.attr('data-next'), 10) || 0;
            $wrap.append(btnRowHtml(promoIdx, btnIdx));
            $wrap.attr('data-next', btnIdx + 1);
        });

        $('#promos-repeater').on('click', '.remove-promo-btn', function() {
            $(this).closest('.promo-btn-row').remove();
        });

        $('#promos-repeater').on('click', '.upload-btn-icon', function() {
            var $wrap = $(this).parent();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                $wrap.find('.promo-btn-icon-id').val(att.id);
                $wrap.find('.promo-btn-icon-preview')
                     .html('<img src="' + att.url + '" style="max-width:80%;max-height:80%;object-fit:contain;">');
            });
            frame.open();
        });

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
                + '<p style="margin:16px 0 4px;font-weight:600;">Кнопки</p>'
                + '<p class="description" style="margin-bottom:8px;">Можно не добавлять ни одной или добавить сколько нужно. Иконка необязательна, поддерживается SVG.</p>'
                + '<div class="promo-buttons" data-promo="' + idx + '" data-next="0"></div>'
                + '<button type="button" class="button button-small add-promo-btn">+ Добавить кнопку</button>'
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
