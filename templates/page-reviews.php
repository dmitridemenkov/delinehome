<?php
/**
 * Template Name: О Компании — Отзывы клиентов
 */

get_header();

$page_title   = get_the_title();
$page_content = apply_filters('the_content', get_post_field('post_content', get_the_ID()));

$paged = get_query_var('paged') ?: 1;
$query = new WP_Query([
    'post_type'      => 'review',
    'posts_per_page' => 10,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<section class="mt-[36px] lg:mt-[64px]">
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, ['title' => $page_title]); ?>
        <?php get_template_part('parts/about-tabs'); ?>

        <h1 class="page-title inline-block relative mt-8 mb-[24px] lg:mb-[36px] text-xl lg:text-3xl"><?php echo esc_html($page_title); ?></h1>

        <?php if (trim($page_content)): ?>
        <div class="page-content mb-8">
            <?php echo wp_kses_post($page_content); ?>
        </div>
        <?php endif; ?>

        <?php if ($query->have_posts()): ?>
        <div class="flex flex-col gap-6">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php get_template_part('parts/review-card', null, ['id' => get_the_ID()]); ?>
            <?php endwhile; ?>
        </div>

        <?php if ($query->max_num_pages > 1): ?>
        <div class="reviews-pagination-links mt-8 flex justify-center">
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

        <?php
        deline_reviews_schema(get_posts([
            'post_type'      => 'review',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]));
        ?>
        <?php else: ?>
        <p class="text-lg text-gray-500">Отзывов пока нет.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>
