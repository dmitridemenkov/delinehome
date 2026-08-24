<?php
$slider = deline_get_slider();
if (empty($slider['slides'])) return;
$site_name = get_bloginfo('name');
?>

<section class="hero mt-[24px] md:mt-[0px]">
    <div class="container mx-auto px-3">
        <div class="hero-slider swiper" data-autoplay="<?php echo $slider['autoplay'] ? '1' : '0'; ?>">
            <div class="swiper-wrapper">
                <?php foreach ($slider['slides'] as $i => $slide):
                    $desktop_url      = $slide['desktop_id'] ? wp_get_attachment_url($slide['desktop_id']) : '';
                    $desktop_avif_url = $slide['desktop_avif_id'] ? wp_get_attachment_url($slide['desktop_avif_id']) : '';
                    $mobile_url       = $slide['mobile_id'] ? wp_get_attachment_url($slide['mobile_id']) : '';
                    $mobile_avif_url  = $slide['mobile_avif_id'] ? wp_get_attachment_url($slide['mobile_avif_id']) : '';

                    if (!$desktop_url) continue;

                    $alt   = $site_name . ' — слайд ' . ($i + 1);
                    $title = $site_name;
                ?>
                <div class="swiper-slide">
                    <picture>
                        <?php if ($mobile_avif_url): ?>
                        <source media="(max-width: 767px)" srcset="<?php echo esc_url($mobile_avif_url); ?>" type="image/avif">
                        <?php endif; ?>

                        <?php if ($mobile_url): ?>
                        <source media="(max-width: 767px)" srcset="<?php echo esc_url($mobile_url); ?>">
                        <?php endif; ?>

                        <?php if ($desktop_avif_url): ?>
                        <source srcset="<?php echo esc_url($desktop_avif_url); ?>" type="image/avif">
                        <?php endif; ?>

                        <img src="<?php echo esc_url($desktop_url); ?>"
                             alt="<?php echo esc_attr($alt); ?>"
                             title="<?php echo esc_attr($title); ?>"
                             loading="lazy"
                             class="w-full block rounded-[6px] md:rounded-[8px]">
                    </picture>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($slider['show_bullets']): ?>
            <div class="swiper-pagination"></div>
            <?php endif; ?>

            <?php if ($slider['show_arrows']): ?>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <?php endif; ?>
        </div>
    </div>
</section>
