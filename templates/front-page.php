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
        
        <h2 class="inline-block relative mb-24px lg:mb-[36px] text-xl lg:text-3xl mw-[50%]">Начнем работу на любой стадии</h2>

        <div class="grid grid-cols-2 xl:grid-cols-5">
            
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => 'catalog',
                'color' => 'primary',
                'title' => 'Быстрая разработка',
                'text'  => 'Vite + Tailwind CSS для мгновенной сборки и современных стилей'
            ]);
            ?>
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => 'calculate',
                'color' => 'primary',
                'title' => 'Быстрая разработка',
                'text'  => 'Vite + Tailwind CSS для мгновенной сборки и современных стилей'
            ]);
            ?>
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => '3d',
                'color' => 'primary',
                'title' => 'Быстрая разработка',
                'text'  => 'Vite + Tailwind CSS для мгновенной сборки и современных стилей'
            ]);
            ?>
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => 'measuring',
                'color' => 'primary',
                'title' => 'Быстрая разработка',
                'text'  => 'Vite + Tailwind CSS для мгновенной сборки и современных стилей'
            ]);
            ?>
            <?php
            get_template_part('parts/card-feature', null, [
                'icon'  => 'decency',
                'color' => 'primary',
                'title' => 'Быстрая разработка',
                'text'  => 'Vite + Tailwind CSS для мгновенной сборки и современных стилей'
            ]);
            ?>
        </div>
    </div>
</section>

<!-- Blog Posts Section -->
<?php
$recent_posts = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
]);

if ($recent_posts->have_posts()) :
?>
<section class="py-16 md:py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-bold text-dark mb-4">
                Последние статьи
            </h2>
            <p class="text-xl text-gray-600">
                Новости, обновления и полезные материалы
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
            <article class="bg-light rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition group">
                <?php if (has_post_thumbnail()) : ?>
                <div class="overflow-hidden">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('card', ['class' => 'w-full h-48 object-cover group-hover:scale-105 transition duration-300']); ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2">
                        <?php echo get_the_date(); ?>
                    </div>
                    
                    <h3 class="text-xl font-bold text-dark mb-3 group-hover:text-primary transition">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    
                    <p class="text-gray-600 mb-4">
                        <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                    </p>
                    
                    <a href="<?php the_permalink(); ?>" class="text-primary font-semibold hover:underline">
                        Читать далее →
                    </a>
                </div>
            </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section id="contact" class="py-16 md:py-24 bg-gradient-to-r from-primary to-secondary text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-5xl font-bold mb-6">
            Готовы начать проект?
        </h2>
        <p class="text-xl mb-8 text-white/90 max-w-2xl mx-auto">
            Свяжитесь с нами и мы поможем воплотить ваши идеи в жизнь
        </p>
        <a href="mailto:info@example.com" class="inline-block bg-white text-primary px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition">
            Написать нам
        </a>
    </div>
</section>

<?php get_footer(); ?>