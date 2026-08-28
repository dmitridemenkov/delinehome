<?php
/**
 * Фотографии для страницы «Наше производство».
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
});

function deline_get_production() {
    return get_option('deline_production', []);
}

/**
 * Как разместить кадр: во всю ширину или в половину.
 * Размеры берём из метаданных вложения — WordPress пишет их при загрузке.
 */
function deline_production_layout($item) {
    $mode = $item['layout'] ?? 'auto';
    if ($mode === 'full' || $mode === 'half') {
        return $mode;
    }

    $meta = wp_get_attachment_metadata($item['image_id'] ?? 0);
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

    $items = [];
    if (!empty($_POST['production']) && is_array($_POST['production'])) {
        foreach ($_POST['production'] as $item) {
            $image_id = absint($item['image_id'] ?? 0);
            if (!$image_id) continue;

            $layout = $item['layout'] ?? 'auto';
            if (!in_array($layout, ['auto', 'half', 'full'], true)) {
                $layout = 'auto';
            }

            $items[] = [
                'image_id' => $image_id,
                'avif_id'  => absint($item['avif_id'] ?? 0),
                'caption'  => sanitize_text_field($item['caption'] ?? ''),
                'layout'   => $layout,
            ];
        }
    }

    update_option('deline_production', $items);

    add_settings_error('deline_production', 'success', 'Фотографии сохранены.', 'updated');
    set_transient('deline_production_errors', get_settings_errors('deline_production'), 30);
});

function deline_render_production_page() {
    $items = deline_get_production();

    if ($errors = get_transient('deline_production_errors')) {
        delete_transient('deline_production_errors');
        foreach ($errors as $error) {
            add_settings_error($error['setting'], $error['code'], $error['message'], $error['type']);
        }
    }

    $img_fields = [
        ['key' => 'image_id', 'label' => 'Фото (png/jpg)'],
        ['key' => 'avif_id',  'label' => 'Фото (avif)'],
    ];
    ?>
    <div class="wrap">
        <h1>Производство</h1>
        <p class="description">
            Фотографии для страницы «Наше производство». Раскладка по умолчанию определяется
            по пропорциям кадра: шире, чем 2:1 — во всю ширину, остальные — в половину.
            Кадр обрезается под плитку, поэтому важное держите ближе к центру.
        </p>

        <?php settings_errors('deline_production'); ?>

        <form method="post">
            <?php wp_nonce_field('deline_save_production', 'deline_production_nonce'); ?>

            <div id="prod-repeater" data-next="<?php echo count($items); ?>" style="margin-top: 16px;">
                <?php foreach ($items as $i => $item):
                    $meta  = wp_get_attachment_metadata($item['image_id']);
                    $ratio = (!empty($meta['width']) && !empty($meta['height']))
                        ? $meta['width'] / $meta['height'] : 0;
                    $auto  = $ratio >= DELINE_WIDE_RATIO ? 'во всю ширину' : 'в половину';
                ?>
                <div class="prod-row" style="display: flex; align-items: flex-end; gap: 12px; padding: 12px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 8px;">
                    <span style="min-width: 22px; color: #999; font-weight: 600;"><?php echo $i + 1; ?></span>

                    <?php foreach ($img_fields as $field):
                        $val = $item[$field['key']] ?? 0;
                        $url = $val ? wp_get_attachment_image_url($val, 'thumbnail') : '';
                    ?>
                    <div style="text-align: center;">
                        <div class="prod-img-preview" style="width: 90px; height: 68px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f9f9f9;">
                            <?php if ($url): ?><img src="<?php echo esc_url($url); ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;"><?php endif; ?>
                        </div>
                        <input type="hidden" name="production[<?php echo $i; ?>][<?php echo $field['key']; ?>]" class="prod-img-id" value="<?php echo esc_attr($val); ?>">
                        <button type="button" class="button button-small prod-upload" style="margin-top: 4px;"><?php echo $field['label']; ?></button>
                    </div>
                    <?php endforeach; ?>

                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Подпись</label>
                        <input type="text" name="production[<?php echo $i; ?>][caption]" value="<?php echo esc_attr($item['caption']); ?>" style="width: 100%;" placeholder="Участок кромления">
                    </div>

                    <div style="width: 190px;">
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Раскладка</label>
                        <select name="production[<?php echo $i; ?>][layout]" style="width: 100%;">
                            <option value="auto" <?php selected($item['layout'] ?? 'auto', 'auto'); ?>>Авто — <?php echo $auto; ?></option>
                            <option value="half" <?php selected($item['layout'] ?? 'auto', 'half'); ?>>В половину</option>
                            <option value="full" <?php selected($item['layout'] ?? 'auto', 'full'); ?>>Во всю ширину</option>
                        </select>
                        <?php if ($ratio): ?>
                        <p class="description" style="margin: 4px 0 0;">
                            <?php echo (int) $meta['width']; ?>×<?php echo (int) $meta['height']; ?>,
                            <?php echo number_format($ratio, 2); ?>:1
                        </p>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="button prod-remove" style="color: #b32d2e;">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button" id="prod-add">+ Добавить фото</button>

            <?php submit_button('Сохранить'); ?>
        </form>
    </div>

    <script>
    jQuery(function($) {
        var imgFields = <?php echo wp_json_encode($img_fields); ?>;

        $('#prod-add').on('click', function() {
            var $wrap = $('#prod-repeater');
            var i = parseInt($wrap.attr('data-next'), 10) || 0;
            $wrap.attr('data-next', i + 1);

            var imgs = '';
            imgFields.forEach(function(f) {
                imgs += '<div style="text-align:center;">'
                    + '<div class="prod-img-preview" style="width:90px;height:68px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f9f9f9;"></div>'
                    + '<input type="hidden" name="production[' + i + '][' + f.key + ']" class="prod-img-id" value="0">'
                    + '<button type="button" class="button button-small prod-upload" style="margin-top:4px;">' + f.label + '</button>'
                    + '</div>';
            });

            $wrap.append(
                '<div class="prod-row" style="display:flex;align-items:flex-end;gap:12px;padding:12px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:8px;">'
                + '<span style="min-width:22px;color:#999;font-weight:600;">' + (i + 1) + '</span>'
                + imgs
                + '<div style="flex:1;"><label style="display:block;margin-bottom:4px;font-weight:600;">Подпись</label>'
                + '<input type="text" name="production[' + i + '][caption]" style="width:100%;" placeholder="Участок кромления"></div>'
                + '<div style="width:190px;"><label style="display:block;margin-bottom:4px;font-weight:600;">Раскладка</label>'
                + '<select name="production[' + i + '][layout]" style="width:100%;">'
                + '<option value="auto">Авто — по пропорциям</option>'
                + '<option value="half">В половину</option>'
                + '<option value="full">Во всю ширину</option>'
                + '</select>'
                + '<p class="description" style="margin:4px 0 0;">Размер покажется после сохранения</p></div>'
                + '<button type="button" class="button prod-remove" style="color:#b32d2e;">&times;</button>'
                + '</div>'
            );
        });

        $('#prod-repeater').on('click', '.prod-remove', function() {
            $(this).closest('.prod-row').remove();
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
    });
    </script>
    <?php
}
