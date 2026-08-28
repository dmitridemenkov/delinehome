<?php
/**
 * Каталог товаров.
 * Переопределяет woocommerce/templates/archive-product.php
 *
 * На корне магазина показываем плитку категорий, внутри категории и в поиске —
 * обычный цикл товаров WooCommerce.
 */

defined('ABSPATH') || exit;

get_header();

$is_root = is_shop() && !is_search();
?>

<section>
    <div class="container mx-auto px-3">

        <?php
        // На корне магазина «Каталог» — сама текущая страница, звеньев не нужно.
        // Внутри категории он становится ссылкой, плюс подмешиваем родительские категории.
        $crumbs = [];

        if (!is_shop()) {
            $shop_id = wc_get_page_id('shop');
            if ($shop_id > 0) {
                $crumbs[] = [
                    'label' => get_the_title($shop_id),
                    'url'   => get_permalink($shop_id),
                ];
            }

            if (is_product_category()) {
                $term = get_queried_object();
                foreach (array_reverse(get_ancestors($term->term_id, 'product_cat')) as $ancestor_id) {
                    $ancestor = get_term($ancestor_id, 'product_cat');
                    if ($ancestor && !is_wp_error($ancestor)) {
                        $crumbs[] = [
                            'label' => $ancestor->name,
                            'url'   => get_term_link($ancestor),
                        ];
                    }
                }
            }
        }

        get_template_part('parts/breadcrumbs', null, [
            'title' => woocommerce_page_title(false),
            'items' => $crumbs,
        ]);
        ?>

        <div class="flex flex-nowrap items-center justify-between gap-4 mt-8 lg:mt-[42px] mb-8">
            <h1 class="min-w-0 text-xl lg:text-4xl font-bold text-black"><?php woocommerce_page_title(); ?></h1>
            <?php get_template_part('parts/product-search'); ?>
        </div>

        <?php if ($is_root):

            // Служебную категорию WooCommerce («Misc») в каталоге не показываем
            $default_cat = (int) get_option('default_product_cat');

            $categories = get_terms([
                'taxonomy'   => 'product_cat',
                'parent'     => 0,
                'hide_empty' => false,
                'exclude'    => $default_cat ? [$default_cat] : [],
            ]);

            if (!is_wp_error($categories) && $categories):
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
            <?php foreach ($categories as $category):
                $thumb_id  = get_term_meta($category->term_id, 'thumbnail_id', true);
                $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : wc_placeholder_img_src('large');
            ?>
            <a href="<?php echo esc_url(get_term_link($category)); ?>"
               class="category-card bg-[#F4F4F4] rounded-lg p-6 lg:p-10 flex flex-col items-center justify-between gap-6 transition hover:-translate-y-[2px]"
               title="<?php echo esc_attr($category->name); ?>">
                <img src="<?php echo esc_url($thumb_url); ?>"
                     alt="<?php echo esc_attr($category->name); ?>"
                     title="<?php echo esc_attr($category->name); ?>"
                     class="w-full h-[160px] lg:h-[220px] object-contain"
                     loading="lazy">
                <span class="text-lg lg:text-2xl font-bold text-black text-center">
                    <?php echo esc_html($category->name); ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php
            else:
                echo '<p class="text-lg text-gray-500">Категории пока не созданы.</p>';
            endif;

        else:
            // Категория или результаты поиска — своя сетка с врезкой акций
            if (have_posts()):

                woocommerce_output_all_notices();

                $promos   = deline_get_promos();
                $interval = (int) deline_get_promos_options()['interval'];
                $promo_i  = 0;
                $shown    = 0;
        ?>
        <div class="catalog-grid">
            <?php while (have_posts()): the_post();
                $shown++;
                $thumb_id = get_post_thumbnail_id();
            ?>
            <a href="<?php the_permalink(); ?>" class="catalog-card" title="<?php the_title_attribute(); ?>">
                <div class="catalog-card__media">
                    <?php if ($thumb_id): ?>
                        <?php the_post_thumbnail('medium_large', [
                            'loading' => 'lazy',
                            'alt'     => the_title_attribute(['echo' => false]),
                            'title'   => the_title_attribute(['echo' => false]),
                        ]); ?>
                    <?php else: ?>
                        <img src="<?php echo esc_url(wc_placeholder_img_src('medium_large')); ?>"
                             alt="<?php the_title_attribute(); ?>"
                             title="<?php the_title_attribute(); ?>" loading="lazy">
                    <?php endif; ?>
                </div>
                <span class="catalog-card__title"><?php the_title(); ?></span>
            </a>
            <?php
                // Акцию не ставим после последнего товара — иначе сетка кончается баннером
                $is_last = ($wp_query->current_post + 1) >= $wp_query->post_count;

                if ($promos && $interval > 0 && !$is_last && $shown % $interval === 0) {
                    get_template_part('parts/promo-card', null, [
                        'promo' => $promos[$promo_i % count($promos)],
                    ]);
                    $promo_i++;
                }
            endwhile; ?>
        </div>
        <?php
            else:
                do_action('woocommerce_no_products_found');
            endif;
        endif;
        ?>

    </div>
</section>

<?php get_footer(); ?>
