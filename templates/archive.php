<?php
/**
 * Архив записей: рубрики, метки, даты, автор.
 */

get_header();
?>

<section>
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, [
            'title' => wp_strip_all_tags(get_the_archive_title()),
        ]); ?>

        <h1 class="inline-block relative mt-8 lg:mt-[42px] mb-5 text-xl lg:text-4xl font-bold text-black">
            <?php echo esc_html(wp_strip_all_tags(get_the_archive_title())); ?>
        </h1>

        <?php if ($description = get_the_archive_description()): ?>
        <div class="page-content max-w-[860px] mb-8"><?php echo wp_kses_post($description); ?></div>
        <?php endif; ?>

        <?php if (have_posts()): ?>
        <div class="post-list">
            <?php while (have_posts()): the_post(); ?>
                <?php get_template_part('parts/post-card'); ?>
            <?php endwhile; ?>
        </div>

        <div class="reviews-pagination-links mt-8 flex justify-center">
            <?php the_posts_pagination([
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type'      => 'list',
                'mid_size'  => 1,
            ]); ?>
        </div>
        <?php else: ?>
        <p class="text-lg text-gray-500">Записей пока нет.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>
