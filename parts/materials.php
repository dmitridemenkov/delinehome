<?php
$materials = deline_get_materials();
if (empty($materials)) return;

$materials_url = deline_page_url('suppliers') ?: '#';
?>

<section class="mt-[36px] lg:mt-[64px]" id="materials">
    <div class="container mx-auto px-3">
        <div class="flex flex-wrap items-start justify-between gap-4 w-[100%]">
            <h2 class="inline-block relative mb-[24px] lg:mb-[36px] text-xl lg:text-3xl">Материалы</h2>
            <a href="<?php echo esc_url($materials_url); ?>"
                class="text-xs lg:text-xl font-medium text-black border-2 border-[#20436C] rounded-[24px] lg:rounded-[44px] px-[16px] py-[6px] lg:px-[32px] lg:py-[12px] transition hover:text-white hover:bg-[#20436C]"
                title="Смотреть все материалы">Подробнее</a>
        </div>
        <?php // На главной показываем только первые три, остальное — на странице «Поставщики» ?>
        <?php get_template_part('parts/materials-list', null, ['limit' => 3]); ?>
    </div>
</section>
