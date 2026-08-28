<?php
/**
 * Карточка товара.
 * Переопределяет woocommerce/templates/single-product.php
 *
 * Каталог без корзины: вместо цены и «В корзину» — общие CTA-кнопки из настроек.
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()):
    the_post();
    global $product;

    // Крошки: Главная / Каталог / Категория / Товар
    $crumbs = [];
    $shop_id = wc_get_page_id('shop');
    if ($shop_id > 0) {
        $crumbs[] = ['label' => get_the_title($shop_id), 'url' => get_permalink($shop_id)];
    }
    $terms = get_the_terms(get_the_ID(), 'product_cat');
    if ($terms && !is_wp_error($terms)) {
        $term = reset($terms);
        $crumbs[] = ['label' => $term->name, 'url' => get_term_link($term)];
    }

    // Галерея: главное фото + вложения
    $image_ids = [];
    if ($thumb_id = get_post_thumbnail_id()) {
        $image_ids[] = $thumb_id;
    }
    if ($product) {
        $image_ids = array_merge($image_ids, $product->get_gallery_image_ids());
    }
    $image_ids = array_values(array_unique(array_filter($image_ids)));

    $buttons = deline_get_product_buttons();
    $steps   = deline_get_product_steps();
?>

<section>
    <div class="container mx-auto px-3">

        <?php get_template_part('parts/breadcrumbs', null, [
            'title' => get_the_title(),
            'items' => $crumbs,
        ]); ?>

        <div class="product-layout">

            <?php if ($image_ids): ?>
            <div class="product-gallery">
                <div class="product-gallery__main swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($image_ids as $id):
                            $full = wp_get_attachment_image_url($id, 'full');
                        ?>
                        <div class="swiper-slide">
                            <a href="<?php echo esc_url($full); ?>" class="glightbox"
                               data-gallery="product-<?php the_ID(); ?>"
                               title="<?php the_title_attribute(); ?>">
                                <?php echo wp_get_attachment_image($id, 'large', false, [
                                    'alt'     => the_title_attribute(['echo' => false]),
                                    'title'   => the_title_attribute(['echo' => false]),
                                    'loading' => 'lazy',
                                ]); ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (count($image_ids) > 1): ?>
                <div class="product-gallery__thumbs-wrap">
                    <div class="product-gallery__thumbs swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($image_ids as $id): ?>
                            <div class="swiper-slide">
                                <?php echo wp_get_attachment_image($id, 'thumbnail', false, [
                                    'alt'     => the_title_attribute(['echo' => false]),
                                    'loading' => 'lazy',
                                ]); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="button" class="product-gallery__nav product-gallery__nav--prev" aria-label="Предыдущее фото">
                        <svg width="8" height="13" viewBox="0 0 8 13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 1 1.5 6.5 7 12"/></svg>
                    </button>
                    <button type="button" class="product-gallery__nav product-gallery__nav--next" aria-label="Следующее фото">
                        <svg width="8" height="13" viewBox="0 0 8 13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m1 1 5.5 5.5L1 12"/></svg>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="product-summary">
                <h1 class="product-summary__title"><?php the_title(); ?></h1>
                <div class="product-summary__text content"><?php the_content(); ?></div>
            </div>
        </div>

        <?php if ($buttons): ?>
        <div class="product-actions">
            <?php foreach ($buttons as $btn):
                $icon_url = ($btn['icon_id'] ?? 0) ? wp_get_attachment_url($btn['icon_id']) : '';
            ?>
            <a href="<?php echo esc_url($btn['url'] ?: '#'); ?>"
               class="product-actions__btn"
               title="<?php echo esc_attr($btn['label']); ?>">
                <span><?php echo esc_html($btn['label']); ?></span>
                <?php if ($icon_url): ?>
                <img src="<?php echo esc_url($icon_url); ?>" alt="" aria-hidden="true"
                     class="product-actions__icon" loading="lazy">
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($steps): ?>
        <div class="product-steps">
            <?php foreach ($steps as $i => $step): ?>
            <div class="product-steps__item">
                <span class="product-steps__num"><?php echo $i + 1; ?></span>
                <span class="product-steps__text"><?php echo esc_html($step['text']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php
endwhile;

get_footer();
