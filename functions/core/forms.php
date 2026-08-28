<?php
/**
 * Формы заявок: приём через AJAX, письмо и сохранение в базу.
 */

// Заявки храним отдельным типом записи: почта на шареде может не дойти,
// и тогда единственным следом останется запись в базе.
add_action('init', function () {
    register_post_type('lead', [
        'labels' => [
            'name'          => 'Заявки',
            'singular_name' => 'Заявка',
            'edit_item'     => 'Просмотр заявки',
            'search_items'  => 'Искать заявки',
            'not_found'     => 'Заявок пока нет',
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-email-alt',
        'menu_position' => 9,
        'supports'      => ['title'],
        'capabilities'  => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'  => true,
    ]);
});

add_filter('manage_lead_posts_columns', function ($cols) {
    return [
        'cb'         => $cols['cb'],
        'title'      => 'Заявка',
        'lead_phone' => 'Телефон',
        'lead_form'  => 'Форма',
        'lead_page'  => 'Страница',
        'date'       => 'Дата',
    ];
});

add_action('manage_lead_posts_custom_column', function ($col, $post_id) {
    switch ($col) {
        case 'lead_phone':
            echo esc_html(get_post_meta($post_id, '_lead_phone', true));
            break;
        case 'lead_form':
            echo esc_html(get_post_meta($post_id, '_lead_form', true));
            break;
        case 'lead_page':
            $url   = get_post_meta($post_id, '_lead_page_url', true);
            $title = get_post_meta($post_id, '_lead_page_title', true);
            if ($url) {
                printf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($url), esc_html($title ?: $url));
            }
            break;
    }
}, 10, 2);

add_action('add_meta_boxes', function () {
    add_meta_box('lead_meta', 'Данные заявки', function ($post) {
        $rows = [
            'Имя'      => get_post_meta($post->ID, '_lead_name', true),
            'Телефон'  => get_post_meta($post->ID, '_lead_phone', true),
            'Форма'    => get_post_meta($post->ID, '_lead_form', true),
            'Страница' => get_post_meta($post->ID, '_lead_page_title', true),
            'Адрес'    => get_post_meta($post->ID, '_lead_page_url', true),
        ];
        echo '<table class="form-table">';
        foreach ($rows as $label => $value) {
            printf('<tr><th style="width:140px;">%s</th><td>%s</td></tr>', esc_html($label), esc_html($value));
        }
        $comment = get_post_meta($post->ID, '_lead_comment', true);
        if ($comment) {
            printf('<tr><th>Комментарий</th><td>%s</td></tr>', nl2br(esc_html($comment)));
        }
        echo '</table>';
    }, 'lead', 'normal', 'high');
});

/**
 * Ключ nonce отдаём на фронт через локализацию скрипта.
 */
add_action('wp_enqueue_scripts', function () {
    wp_localize_script('kickstarter-script', 'delineForm', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('deline_form'),
    ]);
}, 20);

function deline_handle_form_submit() {
    check_ajax_referer('deline_form', 'nonce');

    // Ловушка для ботов: поле скрыто из вёрстки, человек его не заполнит
    if (!empty($_POST['website'])) {
        wp_send_json_success(['message' => 'Заявка отправлена.']);
    }

    $name    = sanitize_text_field($_POST['name'] ?? '');
    $phone   = sanitize_text_field($_POST['phone'] ?? '');
    $comment = sanitize_textarea_field($_POST['comment'] ?? '');
    $form    = sanitize_text_field($_POST['form'] ?? 'Заявка');

    $page_url   = esc_url_raw($_POST['page_url'] ?? '');
    $page_title = sanitize_text_field($_POST['page_title'] ?? '');

    if (!$name || !$phone) {
        wp_send_json_error(['message' => 'Укажите имя и телефон.'], 400);
    }

    $lead_id = wp_insert_post([
        'post_type'   => 'lead',
        'post_status' => 'publish',
        'post_title'  => sprintf('%s — %s', $form, $name),
    ]);

    if ($lead_id && !is_wp_error($lead_id)) {
        update_post_meta($lead_id, '_lead_name', $name);
        update_post_meta($lead_id, '_lead_phone', $phone);
        update_post_meta($lead_id, '_lead_comment', $comment);
        update_post_meta($lead_id, '_lead_form', $form);
        update_post_meta($lead_id, '_lead_page_url', $page_url);
        update_post_meta($lead_id, '_lead_page_title', $page_title);
    }

    $settings = deline_get_settings();
    $to = !empty($settings['form_email']) ? $settings['form_email'] : get_option('admin_email');

    $lines = [
        'Форма: ' . $form,
        'Имя: ' . $name,
        'Телефон: ' . $phone,
    ];
    if ($comment) {
        $lines[] = 'Комментарий: ' . $comment;
    }
    $lines[] = '';
    $lines[] = 'Страница: ' . ($page_title ?: '—');
    $lines[] = 'Адрес: ' . ($page_url ?: '—');

    wp_mail(
        $to,
        sprintf('[%s] %s', get_bloginfo('name'), $form),
        implode("\n", $lines),
        ['Content-Type: text/plain; charset=UTF-8']
    );

    wp_send_json_success(['message' => 'Спасибо! Мы свяжемся с вами в ближайшее время.']);
}

add_action('wp_ajax_deline_form', 'deline_handle_form_submit');
add_action('wp_ajax_nopriv_deline_form', 'deline_handle_form_submit');
