<?php
/**
 * Промо-блок в сетке каталога. Занимает всю ширину строки.
 * @param array $args['promo']
 */
$promo = $args['promo'] ?? null;
if (!$promo) return;

$img_url  = $promo['image_id'] ? wp_get_attachment_url($promo['image_id']) : '';
$avif_url = ($promo['avif_id'] ?? 0) ? wp_get_attachment_url($promo['avif_id']) : '';
$img_alt  = $promo['title'] ?: 'Акция';

$buttons = $promo['buttons'] ?? [];
?>

<div class="promo-card p-6 rounded-[6px] lg:rounded-[16px]">
    <div class="promo-card__body">
        <?php if (!$buttons): ?>
        <?php // Кнопок нет — вместо них некликабельный шильдик ?>
        <span class="promo-card__badge">Акция</span>
        <?php endif; ?>

        <?php if ($promo['title']): ?>
        <h3 class="font-medium text-xl lg:text-4xl text-white mb-4 lg:mb-6"><?php echo esc_html($promo['title']); ?></h3>
        <?php endif; ?>

        <?php if ($promo['content']): ?>
        <div class="promo-card__text content"><?php echo wp_kses_post($promo['content']); ?></div>
        <?php endif; ?>

        <?php if ($buttons): ?>
        <div class="promo-card__actions mt-5 md:mt-10 gap-3 md:gap-6">
            <?php foreach ($buttons as $btn):
                if (empty($btn['label'])) continue;
                $icon_url = ($btn['icon_id'] ?? 0) ? wp_get_attachment_url($btn['icon_id']) : '';
            ?>
            <a href="<?php echo esc_url($btn['url'] ?: '#'); ?>"
               class="promo-card__btn"
               title="<?php echo esc_attr($btn['label']); ?>">
                <span><?php echo esc_html($btn['label']); ?></span>
                <?php if ($icon_url): ?>
                <img src="<?php echo esc_url($icon_url); ?>" alt="" aria-hidden="true"
                     class="promo-card__btn-icon" loading="lazy">
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($img_url): ?>
    <div class="promo-card__media">
        <picture>
            <?php if ($avif_url): ?>
            <source srcset="<?php echo esc_url($avif_url); ?>" type="image/avif">
            <?php endif; ?>
            <img src="<?php echo esc_url($img_url); ?>"
                class="rounded-[6px] lg:rounded-[16px]"
                 alt="<?php echo esc_attr($img_alt); ?>"
                 title="<?php echo esc_attr($img_alt); ?>"
                 loading="lazy">
        </picture>
    </div>
    <?php endif; ?>
</div>
