<?php
$materials = deline_get_materials();
if (empty($materials)) return;

$materials_url = deline_page_url('materials') ?: '#';
?>

<section class="mt-[36px] lg:mt-[64px]" id="materials">
    <div class="container mx-auto px-3">
        <div class="flex flex-wrap items-start justify-between gap-4 w-[100%]">
            <h2 class="inline-block relative mb-[24px] lg:mb-[36px] text-xl lg:text-3xl">Материалы</h2>
            <a href="<?php echo esc_url($materials_url); ?>"
                class="text-xs lg:text-xl font-medium text-black border-2 border-[#20436C] rounded-[24px] lg:rounded-[44px] px-[16px] py-[6px] lg:px-[32px] lg:py-[12px] transition hover:text-white hover:bg-[#20436C]"
                title="Смотреть все материалы">Подробнее</a>
        </div>
        <div class="flex flex-col gap-2">
            <?php foreach ($materials as $item):
                $img_url  = $item['image_id'] ? wp_get_attachment_url($item['image_id']) : '';
                $avif_url = ($item['avif_id'] ?? 0) ? wp_get_attachment_url($item['avif_id']) : '';
                $title    = $item['title'];
                $img_alt  = $title ?: 'Фото материала';
            ?>
            <div class="material-item relative bg-[#F4F4F4] flex items-center justify-between">
                <div class="material-text py-3 ps-4 lg:ps-12">
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
                            class="h-[100%] w-[140px] lg:w-[360px] inline-block object-cover"
                            alt="<?php echo esc_attr($img_alt); ?>"
                            title="<?php echo esc_attr($img_alt); ?>"
                            loading="lazy">
                    </picture>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
