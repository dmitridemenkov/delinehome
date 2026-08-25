<?php
$reviews = get_posts([
    'post_type'      => 'review',
    'posts_per_page' => 12,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
if (empty($reviews)) return;

$reviews_page = get_page_by_path('reviews');
$reviews_url  = $reviews_page ? get_permalink($reviews_page) : '#';
?>

<section class="mt-[36px] lg:mt-[64px]" id="reviews">
    <div class="container mx-auto px-3">
        <div class="flex flex-wrap items-start justify-between gap-4 w-[100%]">
            <h2 class="inline-block relative mb-[24px] lg:mb-[36px] text-xl lg:text-3xl">Отзывы клиентов</h2>
            <a href="<?php echo esc_url($reviews_url); ?>"
                class="text-xs lg:text-xl font-medium text-black border-2 border-[#20436C] rounded-[24px] lg:rounded-[44px] px-[16px] py-[6px] lg:px-[32px] lg:py-[12px] transition hover:text-white hover:bg-[#20436C]"
                title="Смотреть все отзывы">Подробнее</a>
        </div>

        <div class="reviews-slider swiper">
            <div class="swiper-wrapper">
                <?php foreach ($reviews as $review):
                    $name   = get_post_meta($review->ID, '_review_author_name', true) ?: 'Без имени';
                    $rating = (int)(get_post_meta($review->ID, '_review_rating', true) ?: 5);
                    $date   = get_post_meta($review->ID, '_review_date', true);
                    $text   = apply_filters('the_content', $review->post_content);

                    $desktop_id      = get_post_meta($review->ID, '_review_photo_desktop', true);
                    $desktop_avif_id = get_post_meta($review->ID, '_review_photo_desktop_avif', true);
                    $mobile_id       = get_post_meta($review->ID, '_review_photo_mobile', true);
                    $mobile_avif_id  = get_post_meta($review->ID, '_review_photo_mobile_avif', true);

                    $desktop_url      = $desktop_id ? wp_get_attachment_url($desktop_id) : '';
                    $desktop_avif_url = $desktop_avif_id ? wp_get_attachment_url($desktop_avif_id) : '';
                    $mobile_url       = $mobile_id ? wp_get_attachment_url($mobile_id) : '';
                    $mobile_avif_url  = $mobile_avif_id ? wp_get_attachment_url($mobile_avif_id) : '';

                    $has_photo = $desktop_url || $mobile_url;
                    $lightbox_url = $desktop_url ?: $mobile_url;
                    $lightbox_avif = $desktop_avif_url ?: $mobile_avif_url;
                    $photo_alt = 'Фото к отзыву — ' . $name;
                ?>
                <div class="swiper-slide">
                    <div class="bg-[#F9F9F9] p-3 lg:p-6 rounded-sm lg:rounded border border-[#E6E6E6] h-full">
                        <div class="flex w-[100%] items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <svg class="shrink-0" width="61" height="61" viewBox="0 0 61 61" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="30.5" cy="30.5" r="30.5" fill="#E4E4E4" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M23.6111 21.8889C23.6111 20.0618 24.3369 18.3096 25.6288 17.0177C26.9207 15.7258 28.673 15 30.5 15C32.327 15 34.0793 15.7258 35.3712 17.0177C36.6631 18.3096 37.3889 20.0618 37.3889 21.8889C37.3889 23.7159 36.6631 25.4682 35.3712 26.7601C34.0793 28.052 32.327 28.7778 30.5 28.7778C28.673 28.7778 26.9207 28.052 25.6288 26.7601C24.3369 25.4682 23.6111 23.7159 23.6111 21.8889ZM23.6111 32.2222C21.3273 32.2222 19.137 33.1295 17.5221 34.7444C15.9072 36.3593 15 38.5495 15 40.8333C15 42.2036 15.5443 43.5178 16.5133 44.4867C17.4822 45.4557 18.7964 46 20.1667 46H40.8333C42.2036 46 43.5178 45.4557 44.4867 44.4867C45.4557 43.5178 46 42.2036 46 40.8333C46 38.5495 45.0928 36.3593 43.4779 34.7444C41.863 33.1295 39.6727 32.2222 37.3889 32.2222H23.6111Z"
                                        fill="white" />
                                </svg>
                                <div class="flex flex-col gap-2">
                                    <div class="text-sm lg:text-2xl text-black font-medium">
                                        <?php echo esc_html($name); ?>
                                    </div>
                                    <?php echo deline_render_stars($rating); ?>
                                </div>
                            </div>
                            <?php if ($date): ?>
                            <div class="text-sm lg:text-xl text-[#BFBFBF] font-medium whitespace-nowrap">
                                <?php echo esc_html($date); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 lg:mt-6 text-black text-sm lg:text-xl review-text">
                            <?php echo wp_kses_post($text); ?>
                        </div>
                        <?php if ($has_photo): ?>
                        <div class="mt-4 lg:mt-6">
                            <a href="<?php echo esc_url($lightbox_url); ?>"
                               class="glightbox"
                               data-gallery="reviews"
                               <?php if ($lightbox_avif): ?>data-avif="<?php echo esc_url($lightbox_avif); ?>"<?php endif; ?>
                               title="<?php echo esc_attr($photo_alt); ?>">
                                <picture>
                                    <?php if ($mobile_avif_url): ?>
                                    <source srcset="<?php echo esc_url($mobile_avif_url); ?>" type="image/avif" media="(max-width: 767px)">
                                    <?php endif; ?>
                                    <?php if ($mobile_url): ?>
                                    <source srcset="<?php echo esc_url($mobile_url); ?>" media="(max-width: 767px)">
                                    <?php endif; ?>
                                    <?php if ($desktop_avif_url): ?>
                                    <source srcset="<?php echo esc_url($desktop_avif_url); ?>" type="image/avif">
                                    <?php endif; ?>
                                    <img src="<?php echo esc_url($desktop_url ?: $mobile_url); ?>"
                                         class="h-[160px] lg:h-[290px] w-[100%] inline-block object-cover rounded"
                                         alt="<?php echo esc_attr($photo_alt); ?>"
                                         title="<?php echo esc_attr($photo_alt); ?>"
                                         loading="lazy">
                                </picture>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="swiper-pagination reviews-pagination"></div>
            <div class="swiper-button-prev reviews-prev"></div>
            <div class="swiper-button-next reviews-next"></div>
        </div>
    </div>
</section>
