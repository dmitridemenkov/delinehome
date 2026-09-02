<?php
/**
 * Список блоков «Материалы».
 * Используется и на главной, и на странице «Поставщики материалов».
 */
$materials = deline_get_materials();
if (empty($materials)) return;
?>

<div class="flex flex-col gap-2">
    <?php foreach ($materials as $item):
        $img_url  = $item['image_id'] ? wp_get_attachment_url($item['image_id']) : '';
        $avif_url = ($item['avif_id'] ?? 0) ? wp_get_attachment_url($item['avif_id']) : '';
        $title    = $item['title'];
        $img_alt  = $title ?: 'Фото материала';
    ?>
    <div class="material-item relative bg-[#F4F4F4] flex justify-between">
        <div class="material-text flex flex-col justify-center py-3 ps-4 lg:ps-12">
            <?php if ($title): ?>
            <div class="text-base lg:text-2xl font-medium text-black mb-3 lg:mb-4">
                <?php echo esc_html($title); ?>
            </div>
            <?php endif; ?>
            <div class="content text-sm lg:text-xl text-black font-regular">
                <?php echo wp_kses_post($item['content']); ?>
            </div>
        </div>
        <?php if ($img_url): ?>
        <div class="material-img">
            <picture>
                <?php if ($avif_url): ?>
                <source srcset="<?php echo esc_url($avif_url); ?>" type="image/avif">
                <?php endif; ?>
                <img src="<?php echo esc_url($img_url); ?>"
                    class="h-[100%] w-[140px] lg:w-[360px] inline-block object-contain"
                    alt="<?php echo esc_attr($img_alt); ?>"
                    title="<?php echo esc_attr($img_alt); ?>"
                    loading="lazy">
            </picture>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
