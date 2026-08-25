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
                    $preview_url      = $work['preview_id'] ? wp_get_attachment_url($work['preview_id']) : '';
                    $preview_avif_url = $work['preview_avif_id'] ? wp_get_attachment_url($work['preview_avif_id']) : '';
                    $main_url         = $work['main_id'] ? wp_get_attachment_url($work['main_id']) : '';
                    $main_avif_url    = $work['main_avif_id'] ? wp_get_attachment_url($work['main_avif_id']) : '';

                    if (!$preview_url) continue;

                    $lightbox_url = $main_url ?: $preview_url;
                    $title = $work['title'] ?: 'Работа ' . ($i + 1);
                    $city  = $work['city'] ?: '';
                ?>
                <div class="swiper-slide">
                    <a href="<?php echo esc_url($lightbox_url); ?>"
                       class="glightbox work-card block relative rounded-[12px] overflow-hidden"
                       data-gallery="works"
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
                </div>
                <?php endforeach; ?>
            </div>

            <div class="swiper-button-prev works-prev"></div>
            <div class="swiper-button-next works-next"></div>
        </div>
    </div>
</section>
