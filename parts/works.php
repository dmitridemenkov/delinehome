<?php
$works = deline_get_works();
if (empty($works)) return;
$works_page = get_page_by_path('works');
$works_url  = $works_page ? get_permalink($works_page) : '#';
?>

<section class="mt-[36px] lg:mt-[64px]" id="works">
    <div class="container mx-auto px-3">
        <div class="flex flex-wrap items-start justify-between gap-4 w-[100%]">
            <h2 class="inline-block relative mb-[24px] lg:mb-[36px] text-xl lg:text-3xl">Наши работы</h2>
            <a href="<?php echo esc_url($works_url); ?>"
               class="text-xs lg:text-xl font-medium text-black border-2 border-[#20436C] rounded-[24px] lg:rounded-[44px] px-[16px] py-[6px] lg:px-[32px] lg:py-[12px] transition hover:text-white hover:bg-[#20436C]"
               title="Смотреть все работы">Подробнее</a>
        </div>

        <div class="works-slider swiper">
            <div class="swiper-wrapper">
                <?php foreach ($works as $i => $work):
                    if (empty($work['images'])) continue;
                    $cover_id  = $work['images'][0];
                    $cover_url = wp_get_attachment_url($cover_id);
                    if (!$cover_url) continue;

                    $title = $work['title'] ?: 'Работа ' . ($i + 1);
                    $city  = $work['city'] ?: '';
                    $gallery_id = 'work-' . $i;
                ?>
                <div class="swiper-slide">
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

            <div class="swiper-button-prev works-prev"></div>
            <div class="swiper-button-next works-next"></div>
        </div>
    </div>
</section>
