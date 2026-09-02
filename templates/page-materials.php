<?php
/**
 * Template Name: Материалы — вкладка
 *
 * Один шаблон на все вкладки раздела. Активная определяется текущей страницей,
 * содержимое берётся из её редактора.
 */

get_header();
?>

<section>
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, ['title' => get_the_title()]); ?>
        <?php get_template_part('parts/materials-tabs'); ?>

        <h1 class="inline-block relative mt-8 lg:mt-[42px] mb-5 text-xl lg:text-4xl font-bold text-black"><?php the_title(); ?></h1>

        <?php while (have_posts()): the_post(); ?>
            <?php if (has_post_thumbnail()): ?>
            <div class="mb-8">
                <?php the_post_thumbnail('large', [
                    'class'   => 'w-full rounded-lg',
                    'alt'     => the_title_attribute(['echo' => false]),
                    'loading' => 'lazy',
                ]); ?>
            </div>
            <?php endif; ?>

            <div class="page-content">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>

    </div>
</section>

<?php get_footer(); ?>
