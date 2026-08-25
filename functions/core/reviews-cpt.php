<?php

add_action('init', function () {
    register_post_type('review', [
        'labels' => [
            'name'               => 'Отзывы',
            'singular_name'      => 'Отзыв',
            'add_new'            => 'Добавить отзыв',
            'add_new_item'       => 'Новый отзыв',
            'edit_item'          => 'Редактировать отзыв',
            'view_item'          => 'Смотреть отзыв',
            'search_items'       => 'Искать отзывы',
            'not_found'          => 'Отзывов не найдено',
            'not_found_in_trash' => 'В корзине нет отзывов',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-star-filled',
        'menu_position' => 5,
        'supports'     => ['title', 'editor'],
        'has_archive'  => false,
    ]);
});

add_action('admin_enqueue_scripts', function ($screen) {
    if ($screen !== 'post.php' && $screen !== 'post-new.php') return;
    if (get_post_type() !== 'review') return;
    wp_enqueue_media();
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'review_meta',
        'Данные отзыва',
        'deline_review_meta_render',
        'review',
        'normal',
        'high'
    );
});

function deline_review_meta_render($post) {
    wp_nonce_field('deline_review_meta', 'deline_review_nonce');

    $name   = get_post_meta($post->ID, '_review_author_name', true);
    $rating = get_post_meta($post->ID, '_review_rating', true) ?: 5;
    $date   = get_post_meta($post->ID, '_review_date', true);

    $img_fields = [
        ['key' => '_review_photo_desktop',      'label' => 'Десктоп (jpg/png)'],
        ['key' => '_review_photo_desktop_avif',  'label' => 'Десктоп (avif)'],
        ['key' => '_review_photo_mobile',        'label' => 'Мобилка (jpg/png)'],
        ['key' => '_review_photo_mobile_avif',   'label' => 'Мобилка (avif)'],
    ];
    ?>
    <table class="form-table">
        <tr>
            <th><label for="review_author_name">Имя автора</label></th>
            <td><input type="text" id="review_author_name" name="review_author_name" value="<?php echo esc_attr($name); ?>" class="regular-text" placeholder="Алексей"></td>
        </tr>
        <tr>
            <th><label for="review_rating">Рейтинг (1–5)</label></th>
            <td><input type="number" id="review_rating" name="review_rating" value="<?php echo esc_attr($rating); ?>" min="1" max="5" style="width: 70px;"></td>
        </tr>
        <tr>
            <th><label for="review_date">Дата отзыва</label></th>
            <td><input type="text" id="review_date" name="review_date" value="<?php echo esc_attr($date); ?>" class="regular-text" placeholder="12.01.2026"></td>
        </tr>
    </table>

    <h3 style="margin-top: 20px;">Фото к отзыву</h3>
    <p class="description">Десктоп — для экранов от 768px. Мобилка — для мобильных. AVIF-версии опциональны.</p>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 12px;" id="review-images">
        <?php foreach ($img_fields as $field):
            $val = get_post_meta($post->ID, $field['key'], true);
            $url = $val ? wp_get_attachment_image_url($val, 'thumbnail') : '';
        ?>
        <div style="text-align: center;">
            <div class="review-img-preview" style="width: 100%; height: 80px; border: 1px dashed #c3c4c7; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 6px; background: #f9f9f9;">
                <?php if ($url): ?>
                    <img src="<?php echo esc_url($url); ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                <?php endif; ?>
            </div>
            <input type="hidden" name="<?php echo $field['key']; ?>" class="review-img-id" value="<?php echo esc_attr($val); ?>">
            <button type="button" class="button button-small upload-review-img"><?php echo $field['label']; ?></button>
            <?php if ($val): ?>
            <button type="button" class="button button-small remove-review-img" style="color: #b32d2e; margin-top: 4px;">&times;</button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <p class="description" style="margin-top: 8px;">Текст отзыва — в основном редакторе выше.</p>

    <script>
    jQuery(function($) {
        $('#review-images').on('click', '.upload-review-img', function() {
            var $wrap = $(this).parent();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                $wrap.find('.review-img-id').val(att.id);
                $wrap.find('.review-img-preview').html('<img src="' + thumb + '" style="max-width:100%;max-height:100%;object-fit:cover;">');
                if (!$wrap.find('.remove-review-img').length) {
                    $wrap.append('<button type="button" class="button button-small remove-review-img" style="color:#b32d2e;margin-top:4px;">&times;</button>');
                }
            });
            frame.open();
        });

        $('#review-images').on('click', '.remove-review-img', function() {
            var $wrap = $(this).parent();
            $wrap.find('.review-img-id').val('');
            $wrap.find('.review-img-preview').html('');
            $(this).remove();
        });
    });
    </script>
    <?php
}

add_action('save_post_review', function ($post_id) {
    if (!isset($_POST['deline_review_nonce']) ||
        !wp_verify_nonce($_POST['deline_review_nonce'], 'deline_review_meta')) return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    update_post_meta($post_id, '_review_author_name', sanitize_text_field($_POST['review_author_name'] ?? ''));
    update_post_meta($post_id, '_review_rating', max(1, min(5, absint($_POST['review_rating'] ?? 5))));
    update_post_meta($post_id, '_review_date', sanitize_text_field($_POST['review_date'] ?? ''));

    $img_keys = ['_review_photo_desktop', '_review_photo_desktop_avif', '_review_photo_mobile', '_review_photo_mobile_avif'];
    foreach ($img_keys as $key) {
        $val = absint($_POST[$key] ?? 0);
        if ($val) {
            update_post_meta($post_id, $key, $val);
        } else {
            delete_post_meta($post_id, $key);
        }
    }
});

function deline_render_stars($rating) {
    $star = '<svg width="18" height="17" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.56088 13.7L4.41088 16.2C4.22754 16.3167 4.03588 16.3667 3.83588 16.35C3.63588 16.3333 3.46088 16.2667 3.31088 16.15C3.16088 16.0333 3.04421 15.8877 2.96088 15.713C2.87754 15.5383 2.86088 15.3423 2.91088 15.125L4.01088 10.4L0.335876 7.225C0.169209 7.075 0.0652091 6.904 0.0238758 6.712C-0.0174575 6.52-0.00512426 6.33267 0.0608757 6.15C0.126876 5.96733 0.226876 5.81733 0.360876 5.7C0.494876 5.58267 0.678209 5.50767 0.910876 5.475L5.76088 5.05L7.63588 0.6C7.71921 0.4 7.84854 0.25 8.02388 0.15C8.19921 0.0499999 8.37821 0 8.56088 0C8.74354 0 8.92254 0.0499999 9.09788 0.15C9.27321 0.25 9.40254 0.4 9.48588 0.6L11.3609 5.05L16.2109 5.475C16.4442 5.50833 16.6275 5.58333 16.7609 5.7C16.8942 5.81667 16.9942 5.96667 17.0609 6.15C17.1275 6.33333 17.1402 6.521 17.0989 6.713C17.0575 6.905 16.9532 7.07567 16.7859 7.225L13.1109 10.4L14.2109 15.125C14.2609 15.3417 14.2442 15.5377 14.1609 15.713C14.0775 15.8883 13.9609 16.034 13.8109 16.15C13.6609 16.266 13.4859 16.3327 13.2859 16.35C13.0859 16.3673 12.8942 16.3173 12.7109 16.2L8.56088 13.7Z" fill="#FFA91D"/></svg>';
    $empty = '<svg width="18" height="17" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.56088 13.7L4.41088 16.2C4.22754 16.3167 4.03588 16.3667 3.83588 16.35C3.63588 16.3333 3.46088 16.2667 3.31088 16.15C3.16088 16.0333 3.04421 15.8877 2.96088 15.713C2.87754 15.5383 2.86088 15.3423 2.91088 15.125L4.01088 10.4L0.335876 7.225C0.169209 7.075 0.0652091 6.904 0.0238758 6.712C-0.0174575 6.52-0.00512426 6.33267 0.0608757 6.15C0.126876 5.96733 0.226876 5.81733 0.360876 5.7C0.494876 5.58267 0.678209 5.50767 0.910876 5.475L5.76088 5.05L7.63588 0.6C7.71921 0.4 7.84854 0.25 8.02388 0.15C8.19921 0.0499999 8.37821 0 8.56088 0C8.74354 0 8.92254 0.0499999 9.09788 0.15C9.27321 0.25 9.40254 0.4 9.48588 0.6L11.3609 5.05L16.2109 5.475C16.4442 5.50833 16.6275 5.58333 16.7609 5.7C16.8942 5.81667 16.9942 5.96667 17.0609 6.15C17.1275 6.33333 17.1402 6.521 17.0989 6.713C17.0575 6.905 16.9532 7.07567 16.7859 7.225L13.1109 10.4L14.2109 15.125C14.2609 15.3417 14.2442 15.5377 14.1609 15.713C14.0775 15.8883 13.9609 16.034 13.8109 16.15C13.6609 16.266 13.4859 16.3327 13.2859 16.35C13.0859 16.3673 12.8942 16.3173 12.7109 16.2L8.56088 13.7Z" fill="#E4E4E4"/></svg>';

    $html = '<div class="flex gap-1">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating ? $star : $empty;
    }
    $html .= '</div>';
    return $html;
}
