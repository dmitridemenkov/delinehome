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
            $title  = sanitize_text_field($work['title'] ?? '');
            $city   = sanitize_text_field($work['city'] ?? '');
            $images = [];

            if (!empty($work['images'])) {
                $images = array_map('absint', explode(',', $work['images']));
                $images = array_filter($images);
            }

            if (empty($images)) continue;

            $works[] = [
                'title'  => $title,
                'city'   => $city,
                'images' => array_values($images),
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
    ?>
    <div class="wrap">
        <h1>Наши работы</h1>

        <?php settings_errors('deline_works'); ?>

        <form method="post">
            <?php wp_nonce_field('deline_save_works', 'deline_works_nonce'); ?>

            <div id="works-repeater" style="margin-top: 12px;">
                <?php foreach ($works as $i => $work): ?>
                <div class="work-row" style="padding: 16px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <strong>Работа <?php echo $i + 1; ?></strong>
                        <button type="button" class="button remove-work" style="color: #b32d2e;">&times; Удалить</button>
                    </div>
                    <table class="form-table" style="margin: 0;">
                        <tr>
                            <th style="width: 120px;"><label>Название</label></th>
                            <td><input type="text" name="works[<?php echo $i; ?>][title]" value="<?php echo esc_attr($work['title']); ?>" class="regular-text" placeholder="Кухня в современном стиле"></td>
                        </tr>
                        <tr>
                            <th><label>Город</label></th>
                            <td><input type="text" name="works[<?php echo $i; ?>][city]" value="<?php echo esc_attr($work['city']); ?>" class="regular-text" placeholder="Киров"></td>
                        </tr>
                        <tr>
                            <th>Фото</th>
                            <td>
                                <div class="work-gallery" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                                    <?php foreach ($work['images'] as $img_id):
                                        $thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
                                        if (!$thumb) continue;
                                    ?>
                                    <div class="work-thumb" style="width: 80px; height: 80px; border-radius: 4px; overflow: hidden; position: relative;">
                                        <img src="<?php echo esc_url($thumb); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <button type="button" class="remove-thumb" style="position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,.6); color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 14px; line-height: 18px; padding: 0;">&times;</button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="works[<?php echo $i; ?>][images]" class="work-images-ids" value="<?php echo esc_attr(implode(',', $work['images'])); ?>">
                                <button type="button" class="button upload-work-images">Добавить фото</button>
                            </td>
                        </tr>
                    </table>
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

        $('#add-work').on('click', function() {
            var html = '<div class="work-row" style="padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:12px;">'
                + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">'
                + '<strong>Работа ' + (workIndex + 1) + '</strong>'
                + '<button type="button" class="button remove-work" style="color:#b32d2e;">&times; Удалить</button>'
                + '</div>'
                + '<table class="form-table" style="margin:0;">'
                + '<tr><th style="width:120px;"><label>Название</label></th>'
                + '<td><input type="text" name="works[' + workIndex + '][title]" class="regular-text" placeholder="Кухня в современном стиле"></td></tr>'
                + '<tr><th><label>Город</label></th>'
                + '<td><input type="text" name="works[' + workIndex + '][city]" class="regular-text" placeholder="Киров"></td></tr>'
                + '<tr><th>Фото</th><td>'
                + '<div class="work-gallery" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px;"></div>'
                + '<input type="hidden" name="works[' + workIndex + '][images]" class="work-images-ids" value="">'
                + '<button type="button" class="button upload-work-images">Добавить фото</button>'
                + '</td></tr></table></div>';
            $('#works-repeater').append(html);
            workIndex++;
        });

        $('#works-repeater').on('click', '.remove-work', function() {
            $(this).closest('.work-row').remove();
        });

        $('#works-repeater').on('click', '.upload-work-images', function() {
            var $row = $(this).closest('.work-row');
            var $input = $row.find('.work-images-ids');
            var $gallery = $row.find('.work-gallery');

            var frame = wp.media({ multiple: true });
            frame.on('select', function() {
                var attachments = frame.state().get('selection').toJSON();
                var currentIds = $input.val() ? $input.val().split(',').map(Number) : [];

                attachments.forEach(function(att) {
                    if (currentIds.indexOf(att.id) === -1) {
                        currentIds.push(att.id);
                        var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                        $gallery.append(
                            '<div class="work-thumb" style="width:80px;height:80px;border-radius:4px;overflow:hidden;position:relative;" data-id="' + att.id + '">'
                            + '<img src="' + thumb + '" style="width:100%;height:100%;object-fit:cover;">'
                            + '<button type="button" class="remove-thumb" style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,.6);color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:14px;line-height:18px;padding:0;">&times;</button>'
                            + '</div>'
                        );
                    }
                });

                $input.val(currentIds.join(','));
            });
            frame.open();
        });

        $('#works-repeater').on('click', '.remove-thumb', function() {
            var $thumb = $(this).closest('.work-thumb');
            var $row = $thumb.closest('.work-row');
            var $input = $row.find('.work-images-ids');
            var removeId = $thumb.data('id');

            $thumb.remove();

            var ids = $input.val().split(',').map(Number).filter(function(id) {
                return id !== removeId && id > 0;
            });
            $input.val(ids.join(','));
        });
    });
    </script>
    <?php
}
