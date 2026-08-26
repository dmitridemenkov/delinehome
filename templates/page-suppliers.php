<?php
/**
 * Template Name: О Компании — Поставщики материалов
 */

get_header();
?>

<section class="mt-[36px] lg:mt-[64px]">
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, ['title' => get_the_title()]); ?>
        <?php get_template_part('parts/about-tabs'); ?>

        <h1 class="page-title inline-block relative mt-8 mb-[24px] lg:mb-[36px] text-xl lg:text-3xl"><?php the_title(); ?></h1>

        <div class="page-content">
            <?php
            while (have_posts()) {
                the_post();
                the_content();
            }
            ?>
        </div>

    </div>
</section>

<?php get_footer(); ?>
