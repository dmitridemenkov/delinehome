<?php
/**
 * Template Name: Материалы — вкладка
 *
 * Один шаблон на все четыре вкладки: какие блоки показать, определяется
 * слагом страницы. Поэтому слаг должен совпадать со слагом вкладки
 * из deline_materials_tabs().
 */

get_header();

$page_title   = get_the_title();
$page_content = apply_filters('the_content', get_post_field('post_content', get_the_ID()));

$slug  = get_post_field('post_name', get_the_ID());
$slugs = array_column(deline_materials_tabs(), 'slug');
$group = in_array($slug, $slugs, true) ? $slug : '';
?>

<section>
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, ['title' => $page_title]); ?>
        <?php get_template_part('parts/materials-tabs'); ?>

        <h1 class="inline-block relative mt-8 lg:mt-[42px] mb-5 text-xl lg:text-4xl font-bold text-black"><?php echo esc_html($page_title); ?></h1>

        <?php if (trim(wp_strip_all_tags($page_content))): ?>
        <div class="page-content max-w-[860px] mb-8"><?php echo wp_kses_post($page_content); ?></div>
        <?php endif; ?>

        <?php
        // Слаг страницы не совпал ни с одной вкладкой — показывать нечего,
        // иначе без фильтра высыпались бы все блоки разом
        if ($group):
            get_template_part('parts/materials-list', null, ['group' => $group]);
        endif;
        ?>

    </div>
</section>

<?php get_footer(); ?>
