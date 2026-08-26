<?php
/**
 * Развёрнутая карточка отзыва: текст слева, сетка фото справа.
 * @param int $args['id'] — ID отзыва
 */
$review_id = $args['id'] ?? get_the_ID();

$name   = get_post_meta($review_id, '_review_author_name', true) ?: 'Без имени';
$rating = (int)(get_post_meta($review_id, '_review_rating', true) ?: 5);
$date   = get_post_meta($review_id, '_review_date', true);
$text   = apply_filters('the_content', get_post_field('post_content', $review_id));
$photos = array_values(array_filter(
    deline_get_review_photos($review_id),
    fn($p) => !empty($p['photo_id'])
));
$photo_alt = 'Фото к отзыву — ' . $name;

// Одно фото — во всю ширину, два — пополам, больше — сеткой
$count = count($photos);
$grid_cols = $count === 1 ? 'grid-cols-1' : 'grid-cols-2';
$photo_ratio = $count === 1 ? 'aspect-[16/10]' : 'aspect-[4/3]';
?>

<article class="bg-[#F9F9F9] p-4 lg:p-8 rounded-lg border border-[#E6E6E6]">
    <div class="flex items-center gap-4 mb-4 lg:mb-6">
        <svg class="shrink-0" width="61" height="61" viewBox="0 0 61 61" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="30.5" cy="30.5" r="30.5" fill="#E4E4E4" />
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M23.6111 21.8889C23.6111 20.0618 24.3369 18.3096 25.6288 17.0177C26.9207 15.7258 28.673 15 30.5 15C32.327 15 34.0793 15.7258 35.3712 17.0177C36.6631 18.3096 37.3889 20.0618 37.3889 21.8889C37.3889 23.7159 36.6631 25.4682 35.3712 26.7601C34.0793 28.052 32.327 28.7778 30.5 28.7778C28.673 28.7778 26.9207 28.052 25.6288 26.7601C24.3369 25.4682 23.6111 23.7159 23.6111 21.8889ZM23.6111 32.2222C21.3273 32.2222 19.137 33.1295 17.5221 34.7444C15.9072 36.3593 15 38.5495 15 40.8333C15 42.2036 15.5443 43.5178 16.5133 44.4867C17.4822 45.4557 18.7964 46 20.1667 46H40.8333C42.2036 46 43.5178 45.4557 44.4867 44.4867C45.4557 43.5178 46 42.2036 46 40.8333C46 38.5495 45.0928 36.3593 43.4779 34.7444C41.863 33.1295 39.6727 32.2222 37.3889 32.2222H23.6111Z"
                fill="white" />
        </svg>
        <div class="flex flex-col gap-1">
            <h3 class="text-base lg:text-2xl text-black font-medium"><?php echo esc_html($name); ?></h3>
            <?php echo deline_render_stars($rating); ?>
        </div>
        <?php if ($date): ?>
        <div class="text-sm lg:text-xl text-[#BFBFBF] font-medium ml-auto whitespace-nowrap"><?php echo esc_html($date); ?></div>
        <?php endif; ?>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <div class="lg:flex-1 text-black text-sm lg:text-base review-text">
            <?php echo wp_kses_post($text); ?>
        </div>
        <?php if (!empty($photos)): ?>
        <div class="grid <?php echo $grid_cols; ?> gap-3 lg:flex-1 self-start">
            <?php foreach ($photos as $pi => $photo):
                $p_url = $photo['photo_id'] ? wp_get_attachment_url($photo['photo_id']) : '';
                $a_url = ($photo['avif_id'] ?? 0) ? wp_get_attachment_url($photo['avif_id']) : '';
                if (!$p_url) continue;
            ?>
            <a href="<?php echo esc_url($p_url); ?>"
               class="glightbox rounded-lg overflow-hidden block"
               data-gallery="review-<?php echo $review_id; ?>"
               <?php if ($a_url): ?>data-avif="<?php echo esc_url($a_url); ?>"<?php endif; ?>
               title="<?php echo esc_attr($photo_alt); ?>">
                <picture>
                    <?php if ($a_url): ?>
                    <source srcset="<?php echo esc_url($a_url); ?>" type="image/avif">
                    <?php endif; ?>
                    <img src="<?php echo esc_url($p_url); ?>"
                         class="w-full <?php echo $photo_ratio; ?> object-cover block"
                         alt="<?php echo esc_attr($photo_alt . ' ' . ($pi + 1)); ?>"
                         title="<?php echo esc_attr($photo_alt); ?>"
                         loading="lazy">
                </picture>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</article>
