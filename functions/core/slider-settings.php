<?php

add_filter('upload_mimes', function ($mimes) {
    $mimes['avif'] = 'image/avif';
    return $mimes;
});

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    if (str_ends_with($filename, '.avif')) {
        $data['ext'] = 'avif';
        $data['type'] = 'image/avif';
    }
    return $data;
}, 10, 4);

add_action('admin_menu', function () {
    add_menu_page(
        'Настройка слайдера',
        'Настройка слайдера',
        'manage_options',
        'slider-settings',
        'deline_render_slider_page',
        'dashicons-images-alt2',
        3
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_slider-settings') return;
    wp_enqueue_media();
});

function deline_get_slider() {
    return get_option('deline_slider', [
        'show_bullets' => true,
        'show_arrows'  => true,
        'slides'       => [],
    ]);
}

add_action('admin_init', function () {
    if (
        !isset($_POST['deline_slider_nonce']) ||
        !wp_verify_nonce($_POST['deline_slider_nonce'], 'deline_save_slider')
    ) return;

    if (!current_user_can('manage_options')) return;

    $settings = [
        'show_bullets' => !empty($_POST['show_bullets']),
        'show_arrows'  => !empty($_POST['show_arrows']),
        'slides'       => [],
    ];

    if (!empty($_POST['slides']) && is_array($_POST['slides'])) {
        foreach ($_POST['slides'] as $slide) {
            $desktop_id      = absint($slide['desktop_id'] ?? 0);
            $desktop_avif_id = absint($slide['desktop_avif_id'] ?? 0);
            $mobile_id       = absint($slide['mobile_id'] ?? 0);
            $mobile_avif_id  = absint($slide['mobile_avif_id'] ?? 0);

            if (!$desktop_id && !$mobile_id) continue;

            $settings['slides'][] = [
                'desktop_id'      => $desktop_id,
                'desktop_avif_id' => $desktop_avif_id,
                'mobile_id'       => $mobile_id,
                'mobile_avif_id'  => $mobile_avif_id,
            ];
        }
    }

    update_option('deline_slider', $settings);

    add_settings_error('deline_slider', 'success', 'Настройки слайдера сохранены.', 'updated');
    set_transient('deline_slider_errors', get_settings_errors('deline_slider'), 30);
});

function deline_render_slider_page() {
    $settings = deline_get_slider();

    if ($errors = get_transient('deline_slider_errors')) {
        delete_transient('deline_slider_errors');
        foreach ($errors as $error) {
            add_settings_error($error['setting'], $error['code'], $error['message'], $error['type']);
        }
    }
    ?>
    <div class="wrap">
        <h1>Настройка слайдера</h1>

        <?php settings_errors('deline_slider'); ?>

        <form method="post">
            <?php wp_nonce_field('deline_save_slider', 'deline_slider_nonce'); ?>

            <h2>Параметры</h2>
            <table class="form-table">
                <tr>
                    <th>Буллиты (точки)</th>
                    <td>
                        <label>
                            <input type="checkbox" name="show_bullets" value="1" <?php checked($settings['show_bullets']); ?>>
                            Показывать буллиты навигации
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>Стрелки</th>
                    <td>
                        <label>
                            <input type="checkbox" name="show_arrows" value="1" <?php checked($settings['show_arrows']); ?>>
                            Показывать стрелки навигации
                        </label>
                    </td>
                </tr>
            </table>

            <h2>Слайды</h2>
            <p class="description">Для каждого слайда укажите изображение для десктопа и мобилки. AVIF-версии опциональны — браузер подставит их автоматически, если поддерживает формат.</p>

            <div id="slides-repeater" style="margin-top: 12px;">
                <?php foreach ($settings['slides'] as $i => $slide):
                    $desktop_url      = $slide['desktop_id'] ? wp_get_attachment_url($slide['desktop_id']) : '';
                    $desktop_avif_url = $slide['desktop_avif_id'] ? wp_get_attachment_url($slide['desktop_avif_id']) : '';
                    $mobile_url       = $slide['mobile_id'] ? wp_get_attachment_url($slide['mobile_id']) : '';
                    $mobile_avif_url  = $slide['mobile_avif_id'] ? wp_get_attachment_url($slide['mobile_avif_id']) : '';
                ?>
                <div class="slide-row" style="padding: 16px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <strong>Слайд <?php echo $i + 1; ?></strong>
                        <button type="button" class="button remove-slide" style="color: #b32d2e;">&times; Удалить</button>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <?php
                        $fields = [
                            ['key' => 'desktop_id',      'label' => 'Десктоп (jpg/png)',  'url' => $desktop_url],
                            ['key' => 'desktop_avif_id', 'label' => 'Десктоп (avif)',     'url' => $desktop_avif_url],
                            ['key' => 'mobile_id',       'label' => 'Мобилка (jpg/png)',  'url' => $mobile_url],
                            ['key' => 'mobile_avif_id',  'label' => 'Мобилка (avif)',     'url' => $mobile_avif_url],
                        ];
                        foreach ($fields as $field): ?>
                        <div style="text-align: center;">
                            <div class="slide-img-preview" style="width: 100%; height: 80px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 6px; background: #f9f9f9;">
                                <?php if ($field['url']): ?>
                                    <img src="<?php echo esc_url($field['url']); ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="slides[<?php echo $i; ?>][<?php echo $field['key']; ?>]" class="slide-img-id" value="<?php echo esc_attr($slide[$field['key']]); ?>">
                            <button type="button" class="button button-small upload-slide-img"><?php echo $field['label']; ?></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button" id="add-slide" style="margin-top: 4px;">+ Добавить слайд</button>

            <?php submit_button('Сохранить настройки'); ?>
        </form>
    </div>

    <script>
    jQuery(function($) {
        var slideIndex = <?php echo count($settings['slides']); ?>;

        function slideFieldHtml(idx, key, label) {
            return '<div style="text-align:center;">'
                + '<div class="slide-img-preview" style="width:100%;height:80px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:6px;background:#f9f9f9;"></div>'
                + '<input type="hidden" name="slides[' + idx + '][' + key + ']" class="slide-img-id" value="0">'
                + '<button type="button" class="button button-small upload-slide-img">' + label + '</button>'
                + '</div>';
        }

        $('#add-slide').on('click', function() {
            var html = '<div class="slide-row" style="padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:12px;">'
                + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">'
                + '<strong>Слайд ' + (slideIndex + 1) + '</strong>'
                + '<button type="button" class="button remove-slide" style="color:#b32d2e;">&times; Удалить</button>'
                + '</div>'
                + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">'
                + slideFieldHtml(slideIndex, 'desktop_id', 'Десктоп (jpg/png)')
                + slideFieldHtml(slideIndex, 'desktop_avif_id', 'Десктоп (avif)')
                + slideFieldHtml(slideIndex, 'mobile_id', 'Мобилка (jpg/png)')
                + slideFieldHtml(slideIndex, 'mobile_avif_id', 'Мобилка (avif)')
                + '</div></div>';
            $('#slides-repeater').append(html);
            slideIndex++;
        });

        $('#slides-repeater').on('click', '.remove-slide', function() {
            $(this).closest('.slide-row').remove();
        });

        $('#slides-repeater').on('click', '.upload-slide-img', function() {
            var $btn = $(this);
            var $wrap = $btn.parent();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                $wrap.find('.slide-img-id').val(att.id);
                $wrap.find('.slide-img-preview').html('<img src="' + att.url + '" style="max-width:100%;max-height:100%;object-fit:cover;">');
            });
            frame.open();
        });
    });
    </script>
    <?php
}
