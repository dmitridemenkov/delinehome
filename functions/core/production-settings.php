<?php
/**
 * Страница «Наше производство»: разделы вида заголовок — текст — группа фото.
 */

// Панорамные кадры занимают строку целиком, остальные — половину.
// 2.0 выбран так, чтобы 16:9 (1.78) остался половиной: во всю ширину
// попадают только осознанно широкие кадры.
const DELINE_WIDE_RATIO = 2.0;

add_action('admin_menu', function () {
    add_menu_page(
        'Производство',
        'Производство',
        'manage_options',
        'production-settings',
        'deline_render_production_page',
        'dashicons-hammer',
        6
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_production-settings') return;
    wp_enqueue_media();
    wp_enqueue_editor();
    // Перетаскивание строк — jQuery UI входит в состав WordPress
    wp_enqueue_script('jquery-ui-sortable');
});

/**
 * Разделы страницы. Каждый: заголовок, текст, группа фотографий.
 */
function deline_get_production() {
    $saved = get_option('deline_production', []);
    if (empty($saved) || !is_array($saved)) return [];

    // Совместимость: раньше хранился плоский список фотографий без разделов.
    // Заворачиваем его в один безымянный раздел, чтобы ничего не потерять.
    $first = reset($saved);
    if (isset($first['image_id'])) {
        return [[
            'title'  => '',
            'text'   => '',
            'photos' => $saved,
        ]];
    }

    return $saved;
}

/**
 * Как разместить кадр: во всю ширину или в половину.
 * Размеры берём из метаданных вложения — WordPress пишет их при загрузке.
 */
function deline_production_layout($photo) {
    $mode = $photo['layout'] ?? 'auto';
    if ($mode === 'full' || $mode === 'half') {
        return $mode;
    }

    $meta = wp_get_attachment_metadata($photo['image_id'] ?? 0);
    if (empty($meta['width']) || empty($meta['height'])) {
        return 'half';
    }

    return ($meta['width'] / $meta['height']) >= DELINE_WIDE_RATIO ? 'full' : 'half';
}

add_action('admin_init', function () {
    if (
        !isset($_POST['deline_production_nonce']) ||
        !wp_verify_nonce($_POST['deline_production_nonce'], 'deline_save_production')
    ) return;

    if (!current_user_can('manage_options')) return;

    $sections = [];

    if (!empty($_POST['production']) && is_array($_POST['production'])) {
        foreach ($_POST['production'] as $section) {
            $photos = [];

            if (!empty($section['photos']) && is_array($section['photos'])) {
                foreach ($section['photos'] as $photo) {
                    $image_id = absint($photo['image_id'] ?? 0);
                    if (!$image_id) continue;

                    $layout = $photo['layout'] ?? 'auto';
                    if (!in_array($layout, ['auto', 'half', 'full'], true)) {
                        $layout = 'auto';
                    }

                    $photos[] = [
                        'image_id' => $image_id,
                        'avif_id'  => absint($photo['avif_id'] ?? 0),
                        'caption'  => sanitize_text_field($photo['caption'] ?? ''),
                        'layout'   => $layout,
                    ];
                }
            }

            $title = sanitize_text_field($section['title'] ?? '');
            $text  = wp_kses_post($section['text'] ?? '');

            // Полностью пустой раздел не сохраняем
            if (!$title && !trim(wp_strip_all_tags($text)) && !$photos) continue;

            $sections[] = [
                'title'  => $title,
                'text'   => $text,
                'photos' => $photos,
            ];
        }
    }

    update_option('deline_production', $sections);

    add_settings_error('deline_production', 'success', 'Разделы сохранены.', 'updated');
    set_transient('deline_production_errors', get_settings_errors('deline_production'), 30);
});

/**
 * Разметка одной строки фотографии.
 */
function deline_production_photo_row($section_i, $photo_i, $photo = []) {
    $img_fields = [
        ['key' => 'image_id', 'label' => 'Фото (png/jpg)'],
        ['key' => 'avif_id',  'label' => 'Фото (avif)'],
    ];

    $meta  = wp_get_attachment_metadata($photo['image_id'] ?? 0);
    $ratio = (!empty($meta['width']) && !empty($meta['height'])) ? $meta['width'] / $meta['height'] : 0;
    $auto  = $ratio >= DELINE_WIDE_RATIO ? 'во всю ширину' : 'в половину';
    $name  = "production[{$section_i}][photos][{$photo_i}]";
    ?>
    <div class="prod-row" style="display: flex; align-items: flex-end; gap: 12px; padding: 12px; background: #fff; border: 1px solid #dcdcde; border-radius: 4px; margin-bottom: 8px;">
        <span class="prod-photo-handle" title="Перетащите, чтобы изменить порядок"
              style="display: flex; align-items: center; gap: 8px; align-self: stretch; cursor: grab; color: #8c8f94; user-select: none;">
            <svg width="12" height="18" viewBox="0 0 12 18" fill="currentColor" aria-hidden="true">
                <circle cx="3" cy="3" r="1.5"/><circle cx="9" cy="3" r="1.5"/>
                <circle cx="3" cy="9" r="1.5"/><circle cx="9" cy="9" r="1.5"/>
                <circle cx="3" cy="15" r="1.5"/><circle cx="9" cy="15" r="1.5"/>
            </svg>
            <b class="prod-photo-num" style="color: #999;"><?php echo $photo_i + 1; ?></b>
        </span>

        <?php foreach ($img_fields as $field):
            $val = $photo[$field['key']] ?? 0;
            $url = $val ? wp_get_attachment_image_url($val, 'thumbnail') : '';
        ?>
        <div style="text-align: center;">
            <div class="prod-img-preview" style="width: 90px; height: 68px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f9f9f9;">
                <?php if ($url): ?><img src="<?php echo esc_url($url); ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;"><?php endif; ?>
            </div>
            <input type="hidden" name="<?php echo esc_attr($name . '[' . $field['key'] . ']'); ?>" class="prod-img-id" value="<?php echo esc_attr($val); ?>">
            <button type="button" class="button button-small prod-upload" style="margin-top: 4px;"><?php echo $field['label']; ?></button>
        </div>
        <?php endforeach; ?>

        <div style="flex: 1;">
            <label style="display: block; margin-bottom: 4px; font-weight: 600;">Подпись на фото</label>
            <input type="text" name="<?php echo esc_attr($name . '[caption]'); ?>" value="<?php echo esc_attr($photo['caption'] ?? ''); ?>" style="width: 100%;" placeholder="Участок кромления">
        </div>

        <div style="width: 190px;">
            <label style="display: block; margin-bottom: 4px; font-weight: 600;">Раскладка</label>
            <select name="<?php echo esc_attr($name . '[layout]'); ?>" style="width: 100%;">
                <option value="auto" <?php selected($photo['layout'] ?? 'auto', 'auto'); ?>>Авто<?php echo $ratio ? ' — ' . $auto : ''; ?></option>
                <option value="half" <?php selected($photo['layout'] ?? 'auto', 'half'); ?>>В половину</option>
                <option value="full" <?php selected($photo['layout'] ?? 'auto', 'full'); ?>>Во всю ширину</option>
            </select>
            <?php if ($ratio): ?>
            <p class="description" style="margin: 4px 0 0;">
                <?php echo (int) $meta['width']; ?>×<?php echo (int) $meta['height']; ?>, <?php echo number_format($ratio, 2); ?>:1
            </p>
            <?php endif; ?>
        </div>

        <button type="button" class="button prod-remove" style="color: #b32d2e;">&times;</button>
    </div>
    <?php
}

function deline_render_production_page() {
    $sections = deline_get_production();

    if ($errors = get_transient('deline_production_errors')) {
        delete_transient('deline_production_errors');
        foreach ($errors as $error) {
            add_settings_error($error['setting'], $error['code'], $error['message'], $error['type']);
        }
    }

    $editor_settings = [
        'media_buttons' => false,
        'textarea_rows' => 5,
        'teeny'         => true,
        'quicktags'     => true,
    ];
    ?>
    <div class="wrap">
        <h1>Производство</h1>
        <p class="description">
            Страница собирается из разделов: заголовок, текст, группа фотографий — и так сколько нужно.
            Раскладка кадра по умолчанию определяется его пропорциями: шире, чем 2:1 — во всю ширину,
            остальные — в половину. Кадр обрезается под плитку, поэтому важное держите ближе к центру.
        </p>

        <?php settings_errors('deline_production'); ?>

        <form method="post" id="production-form">
            <?php wp_nonce_field('deline_save_production', 'deline_production_nonce'); ?>

            <div id="prod-repeater" style="margin-top: 16px;">
                <?php foreach ($sections as $i => $section): ?>
                <div class="prod-section" style="padding: 16px; background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <span class="prod-handle" title="Перетащите, чтобы изменить порядок раздела"
                              style="display: flex; align-items: center; gap: 8px; cursor: grab; color: #8c8f94; user-select: none;">
                            <svg width="12" height="18" viewBox="0 0 12 18" fill="currentColor" aria-hidden="true">
                                <circle cx="3" cy="3" r="1.5"/><circle cx="9" cy="3" r="1.5"/>
                                <circle cx="3" cy="9" r="1.5"/><circle cx="9" cy="9" r="1.5"/>
                                <circle cx="3" cy="15" r="1.5"/><circle cx="9" cy="15" r="1.5"/>
                            </svg>
                        </span>
                        <strong>Раздел <span class="prod-num"><?php echo $i + 1; ?></span></strong>
                        <button type="button" class="button prod-section-remove" style="margin-inline-start: auto; color: #b32d2e;">&times; Удалить раздел</button>
                    </div>

                    <p>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Заголовок</label>
                        <input type="text" name="production[<?php echo $i; ?>][title]" value="<?php echo esc_attr($section['title'] ?? ''); ?>" style="width: 100%;" placeholder="Распил деталей">
                    </p>

                    <p style="margin-bottom: 4px; font-weight: 600;">Текст</p>
                    <?php
                    wp_editor(
                        $section['text'] ?? '',
                        'production_text_' . $i,
                        array_merge($editor_settings, [
                            'textarea_name' => 'production[' . $i . '][text]',
                        ])
                    );
                    ?>

                    <p style="margin: 16px 0 6px; font-weight: 600;">Фотографии</p>
                    <div class="prod-photos">
                        <?php foreach (($section['photos'] ?? []) as $j => $photo): ?>
                            <?php deline_production_photo_row($i, $j, $photo); ?>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-small prod-photo-add">+ Добавить фото</button>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button button-primary" id="prod-section-add">+ Добавить раздел</button>

            <?php submit_button('Сохранить'); ?>
        </form>
    </div>

    <style>
        .prod-placeholder,
        .prod-photo-placeholder {
            border: 2px dashed #c3c4c7;
            border-radius: 4px;
            background: #fff;
            margin-bottom: 8px;
        }
        .prod-section.ui-sortable-helper,
        .prod-row.ui-sortable-helper {
            box-shadow: 0 6px 18px rgb(0 0 0 / .15);
        }
        .prod-photos {
            border: 1px dashed transparent;
            border-radius: 4px;
            transition: background-color .15s, border-color .15s;
        }
        .prod-photos.is-empty {
            min-height: 68px;
            border-color: #c3c4c7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }
        .prod-photos.is-empty::before {
            content: 'Перетащите сюда фото из другого раздела или добавьте кнопкой ниже';
            color: #8c8f94;
            font-size: 13px;
        }
        .prod-photos.is-drop-target {
            border-color: #2271b1;
            background: #f0f6fc;
        }
        /* Подсказка не должна мешать, когда в контейнер уже что-то тащат */
        .prod-photos.is-empty.is-drop-target::before {
            content: none;
        }
    </style>

    <script>
    jQuery(function($) {
        var sectionSeq = <?php echo count($sections); ?>;

        // PHP собирает массив по индексу в name, а не по порядку в DOM.
        // Поэтому после любой перестановки индексы переписываем заново —
        // сначала внешние (разделы), потом вложенные (фото внутри раздела).
        function renumber() {
            $('#prod-repeater > .prod-section').each(function(si) {
                $(this).find('[name]').each(function() {
                    this.name = this.name.replace(/^production\[\d+\]/, 'production[' + si + ']');
                });
                $(this).find('> div .prod-num').first().text(si + 1);

                $(this).find('.prod-photos > .prod-row').each(function(pi) {
                    $(this).find('[name]').each(function() {
                        this.name = this.name.replace(/\[photos\]\[\d+\]/, '[photos][' + pi + ']');
                    });
                    $(this).find('.prod-photo-num').text(pi + 1);
                });
            });

            // У пустого раздела контейнер нулевой высоты — в него нечем попасть
            $('.prod-photos').each(function() {
                $(this).toggleClass('is-empty', $(this).children('.prod-row').length === 0);
            });
        }

        function initPhotoSortable($container) {
            $container.sortable({
                handle: '.prod-photo-handle',
                // Фото можно перетаскивать между разделами
                connectWith: '.prod-photos',
                opacity: .75,
                tolerance: 'pointer',
                forcePlaceholderSize: true,
                placeholder: 'prod-photo-placeholder',
                over: function(e, ui) {
                    $(this).addClass('is-drop-target');
                },
                out: function(e, ui) {
                    $(this).removeClass('is-drop-target');
                },
                // При переносе между списками update приходит и от источника,
                // и от приёмника — renumber идемпотентен, лишний вызов безвреден
                update: renumber,
                stop: function() {
                    $('.prod-photos').removeClass('is-drop-target');
                    renumber();
                }
            });
        }

        $('#prod-repeater').sortable({
            handle: '.prod-handle',
            axis: 'y',
            opacity: .75,
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            placeholder: 'prod-placeholder',
            // Внутри разделов живут редакторы TinyMCE: при перемещении узла
            // в DOM iframe перезагружается и теряет содержимое
            start: function(e, ui) {
                ui.item.find('textarea[id^="production_text_"]').each(function() {
                    var ed = tinymce.get(this.id);
                    if (ed) { ed.save(); wp.editor.remove(this.id); }
                });
            },
            stop: function(e, ui) {
                ui.item.find('textarea[id^="production_text_"]').each(function() {
                    wp.editor.initialize(this.id, {
                        tinymce: { wpautop: true, toolbar1: 'bold,italic,bullist,numlist,link,unlink,undo,redo' },
                        quicktags: true
                    });
                });
            },
            update: renumber
        });

        $('#prod-repeater .prod-photos').each(function() { initPhotoSortable($(this)); });

        // Разметить пустые разделы сразу, не дожидаясь первой правки
        renumber();

        $('#prod-section-add').on('click', function() {
            var i = sectionSeq++;
            var editorId = 'production_text_' + i;

            $('#prod-repeater').append(
                '<div class="prod-section" style="padding:16px;background:#f6f7f7;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:16px;">'
                + '<div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">'
                + '<span class="prod-handle" title="Перетащите, чтобы изменить порядок раздела" style="display:flex;align-items:center;gap:8px;cursor:grab;color:#8c8f94;user-select:none;">'
                + '<svg width="12" height="18" viewBox="0 0 12 18" fill="currentColor" aria-hidden="true">'
                + '<circle cx="3" cy="3" r="1.5"/><circle cx="9" cy="3" r="1.5"/>'
                + '<circle cx="3" cy="9" r="1.5"/><circle cx="9" cy="9" r="1.5"/>'
                + '<circle cx="3" cy="15" r="1.5"/><circle cx="9" cy="15" r="1.5"/></svg></span>'
                + '<strong>Раздел <span class="prod-num"></span></strong>'
                + '<button type="button" class="button prod-section-remove" style="margin-inline-start:auto;color:#b32d2e;">&times; Удалить раздел</button>'
                + '</div>'
                + '<p><label style="display:block;margin-bottom:4px;font-weight:600;">Заголовок</label>'
                + '<input type="text" name="production[' + i + '][title]" style="width:100%;" placeholder="Распил деталей"></p>'
                + '<p style="margin-bottom:4px;font-weight:600;">Текст</p>'
                + '<textarea id="' + editorId + '" name="production[' + i + '][text]" rows="5" style="width:100%;"></textarea>'
                + '<p style="margin:16px 0 6px;font-weight:600;">Фотографии</p>'
                + '<div class="prod-photos"></div>'
                + '<button type="button" class="button button-small prod-photo-add">+ Добавить фото</button>'
                + '</div>'
            );

            wp.editor.initialize(editorId, {
                tinymce: { wpautop: true, toolbar1: 'bold,italic,bullist,numlist,link,unlink,undo,redo' },
                quicktags: true
            });

            initPhotoSortable($('#prod-repeater .prod-photos').last());
            renumber();
        });

        $('#prod-repeater').on('click', '.prod-section-remove', function() {
            var $section = $(this).closest('.prod-section');
            $section.find('textarea[id^="production_text_"]').each(function() {
                wp.editor.remove(this.id);
            });
            $section.remove();
            renumber();
        });

        $('#prod-repeater').on('click', '.prod-photo-add', function() {
            var $photos = $(this).prev('.prod-photos');
            var si = $('#prod-repeater > .prod-section').index($(this).closest('.prod-section'));
            var pi = $photos.children('.prod-row').length;
            var n = 'production[' + si + '][photos][' + pi + ']';

            var imgs = '';
            [['image_id', 'Фото (png/jpg)'], ['avif_id', 'Фото (avif)']].forEach(function(f) {
                imgs += '<div style="text-align:center;">'
                    + '<div class="prod-img-preview" style="width:90px;height:68px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f9f9f9;"></div>'
                    + '<input type="hidden" name="' + n + '[' + f[0] + ']" class="prod-img-id" value="0">'
                    + '<button type="button" class="button button-small prod-upload" style="margin-top:4px;">' + f[1] + '</button>'
                    + '</div>';
            });

            $photos.append(
                '<div class="prod-row" style="display:flex;align-items:flex-end;gap:12px;padding:12px;background:#fff;border:1px solid #dcdcde;border-radius:4px;margin-bottom:8px;">'
                + '<span class="prod-photo-handle" title="Перетащите, чтобы изменить порядок" style="display:flex;align-items:center;gap:8px;align-self:stretch;cursor:grab;color:#8c8f94;user-select:none;">'
                + '<svg width="12" height="18" viewBox="0 0 12 18" fill="currentColor" aria-hidden="true">'
                + '<circle cx="3" cy="3" r="1.5"/><circle cx="9" cy="3" r="1.5"/>'
                + '<circle cx="3" cy="9" r="1.5"/><circle cx="9" cy="9" r="1.5"/>'
                + '<circle cx="3" cy="15" r="1.5"/><circle cx="9" cy="15" r="1.5"/></svg>'
                + '<b class="prod-photo-num"></b></span>'
                + imgs
                + '<div style="flex:1;"><label style="display:block;margin-bottom:4px;font-weight:600;">Подпись на фото</label>'
                + '<input type="text" name="' + n + '[caption]" style="width:100%;" placeholder="Участок кромления"></div>'
                + '<div style="width:190px;"><label style="display:block;margin-bottom:4px;font-weight:600;">Раскладка</label>'
                + '<select name="' + n + '[layout]" style="width:100%;">'
                + '<option value="auto">Авто — по пропорциям</option>'
                + '<option value="half">В половину</option>'
                + '<option value="full">Во всю ширину</option></select>'
                + '<p class="description" style="margin:4px 0 0;">Размер покажется после сохранения</p></div>'
                + '<button type="button" class="button prod-remove" style="color:#b32d2e;">&times;</button>'
                + '</div>'
            );

            renumber();
        });

        $('#prod-repeater').on('click', '.prod-remove', function() {
            $(this).closest('.prod-row').remove();
            renumber();
        });

        $('#prod-repeater').on('click', '.prod-upload', function() {
            var $wrap = $(this).parent();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                $wrap.find('.prod-img-id').val(att.id);
                $wrap.find('.prod-img-preview').html('<img src="' + thumb + '" style="max-width:100%;max-height:100%;object-fit:cover;">');
            });
            frame.open();
        });

        $('#production-form').on('submit', function() {
            if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        });
    });
    </script>
    <?php
}
