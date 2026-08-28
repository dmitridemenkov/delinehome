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

$buttons = [];
foreach ([1, 2] as $n) {
    $label = $promo["btn{$n}_label"] ?? '';
    $url   = $promo["btn{$n}_url"] ?? '';
    if ($label) {
        $buttons[] = ['label' => $label, 'url' => $url ?: '#'];
    }
}
?>

<div class="promo-card p-6">
    <div class="promo-card__body">
        <?php if ($promo['title']): ?>
        <h3 class="font-medium font-xl lg:font-4xl text-white mb-4 lg:mb-6"><?php echo esc_html($promo['title']); ?></h3>
        <?php endif; ?>

        <?php if ($promo['content']): ?>
        <div class="promo-card__text content"><?php echo wp_kses_post($promo['content']); ?></div>
        <?php endif; ?>

        <?php if ($buttons): ?>
        <div class="promo-card__actions">
            <?php foreach ($buttons as $btn): ?>
            <a href="<?php echo esc_url($btn['url']); ?>"
               class="promo-card__btn"
               title="<?php echo esc_attr($btn['label']); ?>">
                <span><?php echo esc_html($btn['label']); ?></span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"></path>
                </svg>
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
