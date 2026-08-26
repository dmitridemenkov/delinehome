<?php
/**
 * Template Name: О Компании — Наше производство
 */

get_header();
?>

<section>
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, ['title' => get_the_title()]); ?>
        <?php get_template_part('parts/about-tabs'); ?>

        <h1 class="inline-block relative mt-8 lg:mt-[42px] mb-5 text-xl lg:text-4xl font-bold text-black"><?php the_title(); ?></h1>

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
