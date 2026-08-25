<?php
/**
 * Template Name: Отзывы
 */

get_header();

$paged = get_query_var('paged') ?: 1;
$query = new WP_Query([
    'post_type'      => 'review',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<section class="mt-[36px] lg:mt-[64px]" id="all-reviews">
    <div class="container mx-auto px-3">
        <h2 class="inline-block relative mb-[24px] lg:mb-[36px] text-xl lg:text-3xl">Отзывы клиентов</h2>

        <?php if ($query->have_posts()): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
            <?php while ($query->have_posts()): $query->the_post();
                $name   = get_post_meta(get_the_ID(), '_review_author_name', true) ?: 'Без имени';
                $rating = (int)(get_post_meta(get_the_ID(), '_review_rating', true) ?: 5);
                $date   = get_post_meta(get_the_ID(), '_review_date', true);
                $thumb  = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
            ?>
            <div class="bg-[#F9F9F9] p-3 lg:p-6 rounded-sm lg:rounded border border-[#E6E6E6]">
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
                    <?php the_content(); ?>
                </div>
                <?php if ($thumb): ?>
                <div class="mt-4 lg:mt-6">
                    <a href="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>"
                       class="glightbox"
                       data-gallery="all-reviews"
                       title="Фото к отзыву — <?php echo esc_attr($name); ?>">
                        <img src="<?php echo esc_url($thumb); ?>"
                             class="h-[160px] lg:h-[290px] inline-block object-cover rounded"
                             alt="Фото к отзыву — <?php echo esc_attr($name); ?>"
                             title="Фото к отзыву — <?php echo esc_attr($name); ?>"
                             loading="lazy">
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($query->max_num_pages > 1): ?>
        <div class="mt-8 flex justify-center gap-2">
            <?php
            echo paginate_links([
                'total'     => $query->max_num_pages,
                'current'   => $paged,
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type'      => 'list',
            ]);
            ?>
        </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
        <?php else: ?>
        <p class="text-lg text-gray-500">Отзывов пока нет.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
