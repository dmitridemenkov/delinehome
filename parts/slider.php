<?php
$slider = deline_get_slider();
if (empty($slider['slides'])) return;
?>

<section class="hero-slider swiper">
    <div class="swiper-wrapper">
        <?php foreach ($slider['slides'] as $slide):
            $desktop_url      = $slide['desktop_id'] ? wp_get_attachment_url($slide['desktop_id']) : '';
            $desktop_avif_url = $slide['desktop_avif_id'] ? wp_get_attachment_url($slide['desktop_avif_id']) : '';
            $mobile_url       = $slide['mobile_id'] ? wp_get_attachment_url($slide['mobile_id']) : '';
            $mobile_avif_url  = $slide['mobile_avif_id'] ? wp_get_attachment_url($slide['mobile_avif_id']) : '';

            if (!$desktop_url) continue;
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

                <img src="<?php echo esc_url($desktop_url); ?>" alt="" loading="lazy" class="w-full block">
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
</section>
