<?php

add_action('admin_menu', function () {
    add_menu_page(
        'Наши работы',
        'Наши работы',
        'manage_options',
        'works-settings',
        'deline_render_works_page',
        'dashicons-format-gallery',
        4
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_works-settings') return;
    wp_enqueue_media();
});

function deline_get_works() {
    return get_option('deline_works', []);
}

add_action('admin_init', function () {
    if (
        !isset($_POST['deline_works_nonce']) ||
        !wp_verify_nonce($_POST['deline_works_nonce'], 'deline_save_works')
    ) return;

    if (!current_user_can('manage_options')) return;

    $works = [];

    if (!empty($_POST['works']) && is_array($_POST['works'])) {
        foreach ($_POST['works'] as $work) {
            $preview_id = absint($work['preview_id'] ?? 0);
            if (!$preview_id) continue;

            $works[] = [
                'title'           => sanitize_text_field($work['title'] ?? ''),
                'city'            => sanitize_text_field($work['city'] ?? ''),
                'preview_id'      => $preview_id,
                'preview_avif_id' => absint($work['preview_avif_id'] ?? 0),
                'main_id'         => absint($work['main_id'] ?? 0),
                'main_avif_id'    => absint($work['main_avif_id'] ?? 0),
            ];
        }
    }

    update_option('deline_works', $works);

    add_settings_error('deline_works', 'success', 'Работы сохранены.', 'updated');
    set_transient('deline_works_errors', get_settings_errors('deline_works'), 30);
});

function deline_render_works_page() {
    $works = deline_get_works();

    if ($errors = get_transient('deline_works_errors')) {
        delete_transient('deline_works_errors');
        foreach ($errors as $error) {
            add_settings_error($error['setting'], $error['code'], $error['message'], $error['type']);
        }
    }

    $fields_meta = [
        ['key' => 'preview_id',      'label' => 'Превью (jpg/png)'],
        ['key' => 'preview_avif_id', 'label' => 'Превью (avif)'],
        ['key' => 'main_id',         'label' => 'Основное (jpg/png)'],
        ['key' => 'main_avif_id',    'label' => 'Основное (avif)'],
    ];
    ?>
    <div class="wrap">
        <h1>Наши работы</h1>

        <?php settings_errors('deline_works'); ?>

        <p class="description">Превью — для слайдера и сетки. Основное — открывается в галерее при клике. AVIF-версии опциональны.</p>

        <form method="post">
            <?php wp_nonce_field('deline_save_works', 'deline_works_nonce'); ?>

            <div id="works-repeater" style="margin-top: 12px;">
                <?php foreach ($works as $i => $work): ?>
                <div class="work-row" style="padding: 16px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <strong>Работа <?php echo $i + 1; ?></strong>
                        <button type="button" class="button remove-work" style="color: #b32d2e;">&times; Удалить</button>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">Название</label>
                            <input type="text" name="works[<?php echo $i; ?>][title]" value="<?php echo esc_attr($work['title']); ?>" class="regular-text" style="width: 100%;" placeholder="Кухня в современном стиле">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">Город</label>
                            <input type="text" name="works[<?php echo $i; ?>][city]" value="<?php echo esc_attr($work['city']); ?>" class="regular-text" style="width: 100%;" placeholder="Киров">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                        <?php foreach ($fields_meta as $field):
                            $val = $work[$field['key']] ?? 0;
                            $url = $val ? wp_get_attachment_image_url($val, 'thumbnail') : '';
                        ?>
                        <div style="text-align: center;">
                            <div class="work-img-preview" style="width: 100%; height: 80px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 6px; background: #f9f9f9;">
                                <?php if ($url): ?>
                                    <img src="<?php echo esc_url($url); ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="works[<?php echo $i; ?>][<?php echo $field['key']; ?>]" class="work-img-id" value="<?php echo esc_attr($val); ?>">
                            <button type="button" class="button button-small upload-work-img"><?php echo $field['label']; ?></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button" id="add-work" style="margin-top: 4px;">+ Добавить работу</button>

            <?php submit_button('Сохранить'); ?>
        </form>
    </div>

    <script>
    jQuery(function($) {
        var workIndex = <?php echo count($works); ?>;
        var fieldsMeta = <?php echo json_encode($fields_meta); ?>;

        function imgFieldHtml(idx, key, label) {
            return '<div style="text-align:center;">'
                + '<div class="work-img-preview" style="width:100%;height:80px;border:1px dashed #c3c4c7;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:6px;background:#f9f9f9;"></div>'
                + '<input type="hidden" name="works[' + idx + '][' + key + ']" class="work-img-id" value="0">'
                + '<button type="button" class="button button-small upload-work-img">' + label + '</button>'
                + '</div>';
        }

        $('#add-work').on('click', function() {
            var imgs = '';
            fieldsMeta.forEach(function(f) { imgs += imgFieldHtml(workIndex, f.key, f.label); });

            var html = '<div class="work-row" style="padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:12px;">'
                + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">'
                + '<strong>Работа ' + (workIndex + 1) + '</strong>'
                + '<button type="button" class="button remove-work" style="color:#b32d2e;">&times; Удалить</button>'
                + '</div>'
                + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">'
                + '<div><label style="display:block;margin-bottom:4px;font-weight:600;">Название</label>'
                + '<input type="text" name="works[' + workIndex + '][title]" class="regular-text" style="width:100%;" placeholder="Кухня в современном стиле"></div>'
                + '<div><label style="display:block;margin-bottom:4px;font-weight:600;">Город</label>'
                + '<input type="text" name="works[' + workIndex + '][city]" class="regular-text" style="width:100%;" placeholder="Киров"></div>'
                + '</div>'
                + '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">' + imgs + '</div>'
                + '</div>';
            $('#works-repeater').append(html);
            workIndex++;
        });

        $('#works-repeater').on('click', '.remove-work', function() {
            $(this).closest('.work-row').remove();
        });

        $('#works-repeater').on('click', '.upload-work-img', function() {
            var $wrap = $(this).parent();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                $wrap.find('.work-img-id').val(att.id);
                $wrap.find('.work-img-preview').html('<img src="' + thumb + '" style="max-width:100%;max-height:100%;object-fit:cover;">');
            });
            frame.open();
        });
    });
    </script>
    <?php
}
