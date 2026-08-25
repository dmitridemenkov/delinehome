<?php
/**
 * Template Name: Наши работы
 */

get_header();
$works = deline_get_works();
?>

<section class="mt-[36px] lg:mt-[64px] mb-[36px] lg:mb-[64px]">
    <div class="container mx-auto px-3">
        <h1 class="inline-block relative mb-[24px] lg:mb-[36px] text-xl lg:text-3xl">Наши работы</h1>

        <?php if (!empty($works)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($works as $i => $work):
                $preview_url      = $work['preview_id'] ? wp_get_attachment_url($work['preview_id']) : '';
                $preview_avif_url = $work['preview_avif_id'] ? wp_get_attachment_url($work['preview_avif_id']) : '';
                $main_url         = $work['main_id'] ? wp_get_attachment_url($work['main_id']) : '';
                $main_avif_url    = $work['main_avif_id'] ? wp_get_attachment_url($work['main_avif_id']) : '';

                if (!$preview_url) continue;

                $lightbox_url = $main_url ?: $preview_url;
                $title = $work['title'] ?: 'Работа ' . ($i + 1);
                $city  = $work['city'] ?: '';
            ?>
            <a href="<?php echo esc_url($lightbox_url); ?>"
               class="glightbox work-card block relative rounded-[12px] overflow-hidden"
               data-gallery="works-page"
               data-title="<?php echo esc_attr($title); ?>"
               <?php if ($main_avif_url): ?>data-avif="<?php echo esc_url($main_avif_url); ?>"<?php endif; ?>
               title="<?php echo esc_attr($title); ?>">
                <picture>
                    <?php if ($preview_avif_url): ?>
                    <source srcset="<?php echo esc_url($preview_avif_url); ?>" type="image/avif">
                    <?php endif; ?>
                    <img src="<?php echo esc_url($preview_url); ?>"
                         alt="<?php echo esc_attr($title); ?>"
                         title="<?php echo esc_attr($title); ?>"
                         loading="lazy"
                         class="w-full aspect-[4/3] object-cover block">
                </picture>
                <?php if ($city): ?>
                <span class="absolute bottom-3 right-3 bg-white/90 text-xs font-medium px-3 py-1 rounded-full"><?php echo esc_html($city); ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p>Работы пока не добавлены.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
