<?php
/**
 * Сетка фотографий производства.
 * Панорамные кадры занимают строку целиком, остальные — половину.
 */
$items = deline_get_production();
if (empty($items)) return;
?>

<div class="production-grid">
    <?php foreach ($items as $item):
        $img_url  = wp_get_attachment_url($item['image_id']);
        if (!$img_url) continue;

        $avif_url = ($item['avif_id'] ?? 0) ? wp_get_attachment_url($item['avif_id']) : '';
        $full_url = wp_get_attachment_image_url($item['image_id'], 'full');
        $caption  = $item['caption'];
        $alt      = $caption ?: 'Наше производство';
        $layout   = deline_production_layout($item);
    ?>
    <a href="<?php echo esc_url($full_url ?: $img_url); ?>"
       class="production-item production-item--<?php echo esc_attr($layout); ?> glightbox"
       data-gallery="production"
       <?php if ($avif_url): ?>data-avif="<?php echo esc_url($avif_url); ?>"<?php endif; ?>
       title="<?php echo esc_attr($alt); ?>">
        <picture>
            <?php if ($avif_url): ?>
            <source srcset="<?php echo esc_url($avif_url); ?>" type="image/avif">
            <?php endif; ?>
            <img src="<?php echo esc_url($img_url); ?>"
                 alt="<?php echo esc_attr($alt); ?>"
                 title="<?php echo esc_attr($alt); ?>"
                 loading="lazy">
        </picture>
        <?php if ($caption): ?>
        <span class="production-item__caption"><?php echo esc_html($caption); ?></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>
