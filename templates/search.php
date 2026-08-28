<?php
/**
 * Результаты поиска.
 * Поиск по товарам уходит в шаблон WooCommerce, сюда попадает всё остальное.
 */

get_header();

$query = get_search_query();
$found = (int) $GLOBALS['wp_query']->found_posts;
?>

<section>
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, ['title' => 'Поиск']); ?>

        <h1 class="inline-block relative mt-8 lg:mt-[42px] mb-5 text-xl lg:text-4xl font-bold text-black">
            Поиск<?php echo $query ? ': ' . esc_html($query) : ''; ?>
        </h1>

        <?php if (have_posts()): ?>
        <p class="mb-8 text-[#BFBFBF]">Найдено: <?php echo $found; ?></p>

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
        <p class="mb-6 text-lg text-gray-500">По запросу ничего не нашлось. Попробуйте изменить формулировку.</p>

        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="max-w-[420px]">
            <label class="sr-only" for="search-again">Поиск по сайту</label>
            <input type="search" id="search-again" name="s" value="<?php echo esc_attr($query); ?>"
                   class="w-full rounded-full border border-[#E6E6E6] bg-white py-3 px-5 text-base text-black placeholder:text-[#BFBFBF] focus:outline-none focus:border-[#20436C] transition"
                   placeholder="Что ищем?">
        </form>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>
