<?php

add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    if (str_ends_with($filename, '.svg')) {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}, 10, 4);

add_action('admin_menu', function () {
    add_menu_page(
        'Настройки сайта',
        'Настройки сайта',
        'manage_options',
        'site-settings',
        'deline_render_settings_page',
        'dashicons-admin-generic',
        2
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_site-settings') return;
    wp_enqueue_media();
});

function deline_get_settings() {
    return get_option('deline_settings', [
        'logo_id'    => 0,
        'logo_title' => '',
        'logo_alt'   => '',
        'phone'      => '',
        'address'    => '',
        'contacts'   => [],
    ]);
}

add_action('admin_init', function () {
    if (
        !isset($_POST['deline_settings_nonce']) ||
        !wp_verify_nonce($_POST['deline_settings_nonce'], 'deline_save_settings')
    ) return;

    if (!current_user_can('manage_options')) return;

    $settings = [
        'logo_id'    => absint($_POST['logo_id'] ?? 0),
        'logo_title' => sanitize_text_field($_POST['logo_title'] ?? ''),
        'logo_alt'   => sanitize_text_field($_POST['logo_alt'] ?? ''),
        'phone'      => sanitize_text_field($_POST['site_phone'] ?? ''),
        'address'    => sanitize_text_field($_POST['site_address'] ?? ''),
        'contacts'   => [],
    ];

    if (!empty($_POST['contacts']) && is_array($_POST['contacts'])) {
        foreach ($_POST['contacts'] as $contact) {
            if (empty($contact['label']) && empty($contact['url']) && empty($contact['icon_id'])) continue;
            $settings['contacts'][] = [
                'icon_id' => absint($contact['icon_id'] ?? 0),
                'label'   => sanitize_text_field($contact['label'] ?? ''),
                'url'     => esc_url_raw($contact['url'] ?? ''),
            ];
        }
    }

    update_option('deline_settings', $settings);

    add_settings_error('deline_settings', 'success', 'Настройки сохранены.', 'updated');
    set_transient('deline_settings_errors', get_settings_errors('deline_settings'), 30);
});

function deline_render_settings_page() {
    $settings = deline_get_settings();
    $logo_url = $settings['logo_id'] ? wp_get_attachment_url($settings['logo_id']) : '';

    if ($errors = get_transient('deline_settings_errors')) {
        delete_transient('deline_settings_errors');
        foreach ($errors as $error) {
            add_settings_error($error['setting'], $error['code'], $error['message'], $error['type']);
        }
    }
    ?>
    <div class="wrap">
        <h1>Настройки сайта</h1>

        <?php settings_errors('deline_settings'); ?>

        <form method="post">
            <?php wp_nonce_field('deline_save_settings', 'deline_settings_nonce'); ?>

            <h2>Логотип</h2>
            <table class="form-table">
                <tr>
                    <th>SVG логотип</th>
                    <td>
                        <div id="logo-preview" style="margin-bottom: 10px; max-width: 200px;">
                            <?php if ($logo_url): ?>
                                <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 200px; height: auto;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="logo_id" id="logo-id" value="<?php echo esc_attr($settings['logo_id']); ?>">
                        <button type="button" class="button" id="upload-logo">Выбрать логотип</button>
                        <button type="button" class="button" id="remove-logo" <?php echo !$settings['logo_id'] ? 'style="display:none"' : ''; ?>>Удалить</button>
                    </td>
                </tr>
                <tr>
                    <th><label for="logo-title">Title логотипа</label></th>
                    <td><input type="text" name="logo_title" id="logo-title" value="<?php echo esc_attr($settings['logo_title']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="logo-alt">Alt логотипа</label></th>
                    <td><input type="text" name="logo_alt" id="logo-alt" value="<?php echo esc_attr($settings['logo_alt']); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2>Телефон и адрес</h2>
            <table class="form-table">
                <tr>
                    <th><label for="site-phone">Телефон</label></th>
                    <td><input type="text" name="site_phone" id="site-phone" value="<?php echo esc_attr($settings['phone']); ?>" class="regular-text" placeholder="+7(8332) 22-42-94"></td>
                </tr>
                <tr>
                    <th><label for="site-address">Адрес</label></th>
                    <td><input type="text" name="site_address" id="site-address" value="<?php echo esc_attr($settings['address']); ?>" class="regular-text" placeholder="г. Киров, ул. Улица, д. 12"></td>
                </tr>
            </table>

            <h2>Контакты</h2>
            <p class="description">Соцсети, мессенджеры и прочие каналы связи. Для каждого можно задать SVG-иконку, подпись и ссылку.</p>

            <div id="contacts-repeater" style="margin-top: 12px;">
                <?php foreach ($settings['contacts'] as $i => $contact):
                    $icon_url = $contact['icon_id'] ? wp_get_attachment_url($contact['icon_id']) : '';
                ?>
                <div class="contact-row" style="display: flex; align-items: flex-start; gap: 12px; padding: 16px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 8px;">
                    <div style="flex: 0 0 80px; text-align: center;">
                        <div class="contact-icon-preview" style="width: 40px; height: 40px; margin: 0 auto 8px; border: 1px dashed #c3c4c7; display: flex; align-items: center; justify-content: center; border-radius: 4px; overflow: hidden;">
                            <?php if ($icon_url): ?>
                                <img src="<?php echo esc_url($icon_url); ?>" style="max-width: 100%; max-height: 100%;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="contacts[<?php echo $i; ?>][icon_id]" class="contact-icon-id" value="<?php echo esc_attr($contact['icon_id']); ?>">
                        <button type="button" class="button button-small upload-contact-icon">Иконка</button>
                    </div>
                    <div style="flex: 1;">
                        <input type="text" name="contacts[<?php echo $i; ?>][label]" value="<?php echo esc_attr($contact['label']); ?>" class="regular-text" placeholder="Подпись" style="width: 100%; margin-bottom: 8px;">
                        <input type="url" name="contacts[<?php echo $i; ?>][url]" value="<?php echo esc_url($contact['url']); ?>" class="regular-text" placeholder="https://" style="width: 100%;">
                    </div>
                    <button type="button" class="button remove-contact" title="Удалить" style="color: #b32d2e;">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button" id="add-contact" style="margin-top: 4px;">+ Добавить контакт</button>

            <?php submit_button('Сохранить настройки'); ?>
        </form>
    </div>

    <script>
    jQuery(function($) {
        function openMediaFrame(callback) {
            var frame = wp.media({
                multiple: false,
                library: { type: ['image/svg+xml', 'image'] }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                callback(attachment);
            });
            frame.open();
        }

        // Logo
        $('#upload-logo').on('click', function() {
            openMediaFrame(function(att) {
                $('#logo-id').val(att.id);
                $('#logo-preview').html('<img src="' + att.url + '" style="max-width:200px;height:auto;">');
                $('#remove-logo').show();
            });
        });

        $('#remove-logo').on('click', function() {
            $('#logo-id').val(0);
            $('#logo-preview').empty();
            $(this).hide();
        });

        // Contacts repeater
        var contactIndex = <?php echo count($settings['contacts']); ?>;

        $('#add-contact').on('click', function() {
            var html = '<div class="contact-row" style="display:flex;align-items:flex-start;gap:12px;padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:4px;margin-bottom:8px;">'
                + '<div style="flex:0 0 80px;text-align:center;">'
                + '<div class="contact-icon-preview" style="width:40px;height:40px;margin:0 auto 8px;border:1px dashed #c3c4c7;display:flex;align-items:center;justify-content:center;border-radius:4px;overflow:hidden;"></div>'
                + '<input type="hidden" name="contacts[' + contactIndex + '][icon_id]" class="contact-icon-id" value="0">'
                + '<button type="button" class="button button-small upload-contact-icon">Иконка</button>'
                + '</div>'
                + '<div style="flex:1;">'
                + '<input type="text" name="contacts[' + contactIndex + '][label]" class="regular-text" placeholder="Подпись" style="width:100%;margin-bottom:8px;">'
                + '<input type="url" name="contacts[' + contactIndex + '][url]" class="regular-text" placeholder="https://" style="width:100%;">'
                + '</div>'
                + '<button type="button" class="button remove-contact" title="Удалить" style="color:#b32d2e;">&times;</button>'
                + '</div>';
            $('#contacts-repeater').append(html);
            contactIndex++;
        });

        $('#contacts-repeater').on('click', '.remove-contact', function() {
            $(this).closest('.contact-row').remove();
        });

        $('#contacts-repeater').on('click', '.upload-contact-icon', function() {
            var $row = $(this).closest('.contact-row');
            openMediaFrame(function(att) {
                $row.find('.contact-icon-id').val(att.id);
                $row.find('.contact-icon-preview').html('<img src="' + att.url + '" style="max-width:100%;max-height:100%;">');
            });
        });
    });
    </script>
    <?php
}
