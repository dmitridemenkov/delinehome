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
                if (empty($work['images'])) continue;
                $cover_id  = $work['images'][0];
                $cover_url = wp_get_attachment_url($cover_id);
                if (!$cover_url) continue;

                $title = $work['title'] ?: 'Работа ' . ($i + 1);
                $city  = $work['city'] ?: '';
                $gallery_id = 'work-page-' . $i;
            ?>
            <div>
                <a href="<?php echo esc_url($cover_url); ?>"
                   class="glightbox work-card block relative rounded-[12px] overflow-hidden"
                   data-gallery="<?php echo esc_attr($gallery_id); ?>"
                   data-title="<?php echo esc_attr($title); ?>"
                   title="<?php echo esc_attr($title); ?>">
                    <img src="<?php echo esc_url(wp_get_attachment_image_url($cover_id, 'large')); ?>"
                         alt="<?php echo esc_attr($title); ?>"
                         title="<?php echo esc_attr($title); ?>"
                         loading="lazy"
                         class="w-full aspect-[4/3] object-cover block">
                    <?php if ($city): ?>
                    <span class="absolute bottom-3 right-3 bg-white/90 text-xs font-medium px-3 py-1 rounded-full"><?php echo esc_html($city); ?></span>
                    <?php endif; ?>
                </a>
                <?php for ($j = 1; $j < count($work['images']); $j++):
                    $img_url = wp_get_attachment_url($work['images'][$j]);
                    if (!$img_url) continue;
                ?>
                <a href="<?php echo esc_url($img_url); ?>"
                   class="glightbox hidden"
                   data-gallery="<?php echo esc_attr($gallery_id); ?>"
                   data-title="<?php echo esc_attr($title . ' — фото ' . ($j + 1)); ?>"
                   title="<?php echo esc_attr($title); ?>"></a>
                <?php endfor; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p>Работы пока не добавлены.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
