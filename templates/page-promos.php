<?php
/**
 * Template Name: Акции
 *
 * Выводит те же акции, что врезаются в сетку каталога.
 */

get_header();

$page_title   = get_the_title();
$page_content = apply_filters('the_content', get_post_field('post_content', get_the_ID()));
$promos       = deline_get_promos();
?>

<section>
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, ['title' => $page_title]); ?>

        <h1 class="inline-block relative mt-8 lg:mt-[42px] mb-5 text-xl lg:text-4xl font-bold text-black"><?php echo esc_html($page_title); ?></h1>

        <?php if (trim($page_content)): ?>
        <div class="page-content max-w-[860px] mb-8"><?php echo wp_kses_post($page_content); ?></div>
        <?php endif; ?>

        <?php if ($promos): ?>
        <div class="promo-list">
            <?php foreach ($promos as $promo): ?>
                <?php get_template_part('parts/promo-card', null, ['promo' => $promo]); ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-lg text-gray-500">Сейчас активных акций нет.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>
