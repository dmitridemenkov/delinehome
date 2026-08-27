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

        <?php get_template_part('parts/breadcrumbs', null, ['title' => woocommerce_page_title(false)]); ?>

        <div class="flex flex-wrap items-center justify-between gap-4 mt-8 lg:mt-[42px] mb-8">
            <h1 class="text-xl lg:text-4xl font-bold text-black"><?php woocommerce_page_title(); ?></h1>
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
            // Категория или результаты поиска — стандартный цикл WooCommerce
            if (woocommerce_product_loop()):

                woocommerce_output_all_notices();

                do_action('woocommerce_before_shop_loop');

                woocommerce_product_loop_start();

                if (wc_get_loop_prop('total')) {
                    while (have_posts()) {
                        the_post();
                        do_action('woocommerce_shop_loop');
                        wc_get_template_part('content', 'product');
                    }
                }

                woocommerce_product_loop_end();

                do_action('woocommerce_after_shop_loop');
            else:
                do_action('woocommerce_no_products_found');
            endif;
        endif;
        ?>

    </div>
</section>

<?php get_footer(); ?>
