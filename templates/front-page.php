<?php
/**
 * Шаблон главной страницы
 */

get_header();
?>

<!-- Features Section -->
<?php get_template_part('parts/slider'); ?>

<!-- Features Section -->
<section class="mt-[36px] lg:mt-[64px]" id="features">
    <div class="container mx-auto px-3">
        <h2 class="inline-block relative mb-[24px] lg:mb-[36px] text-xl lg:text-3xl m-w-[50%]">Начнем работу на любой стадии</h2>
        <div class="flex flex-wrap justify-center gap-y-[24px]">
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => 'catalog',
                'color' => 'primary',
                'title' => 'Каталог',
                'text'  => 'Вы ещё не знаете, что вам нравится? Заходите в каталог, ставьте фильтры и листайте'
            ]);
            ?>
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => 'calculate',
                'color' => 'primary',
                'title' => 'Расчет',
                'text'  => 'Вы понимаете, как должна выглядеть ваша кухня? Жмите расчёт, мы сориентируем вас по цене'
            ]);
            ?>
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => '3d',
                'color' => 'primary',
                'title' => '3D проект',
                'text'  => 'Хотите представить, как новая кухня будет смотреться в вашем интерьере? Жмите Бесплатный 3D проект'
            ]);
            ?>
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => 'measuring',
                'color' => 'primary',
                'title' => 'Замер',
                'text'  => 'Для проекта не хватает точных размеров? Запишитесь на бесплатный замер'
            ]);
            ?>
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => 'decency',
                'color' => 'primary',
                'title' => 'Порядочность',
                'text'  => 'Нет времени, чтобы ехать в салон? Запишитесь на бесплатный выезд дизайнера'
            ]);
            ?>
        </div>
    </div>
</section>

<!-- Works Section -->
<section class="mt-[36px] lg:mt-[64px]" id="works">
    <div class="container mx-auto px-3">
        <div class="flex flex-wrap items-start justify-between gap-4 w-[100%]">
            <h2 class="inline-block relative mb-[24px] lg:mb-[36px] text-xl lg:text-3xl m-w-[50%]">Наши работы</h2>
            <a href="/" class="text-xs lg:text-xl font-medium text-black border-2 border-[#20436C] rounded-[24px] lg:rounded-[44px] px-[16px] py-[6px] lg:px-[28px] lg:py-[12px] transition hover:text-white hover:bg-[#20436C]" title="Подробнее">Подробнее</a>
        </div>
    </div>
</section>

<?php get_footer(); ?>