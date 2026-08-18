<?php

/**
 * Шаблон одиночной записи
 */

get_header();
?>

<main class="py-12">
    <div class="container mx-auto px-4">
        <?php while (have_posts()) : the_post(); ?>

            <article class="bg-white rounded-lg shadow-lg overflow-hidden">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="aspect-video">
                        <?php the_post_thumbnail('hero', ['class' => 'w-full h-full object-cover']); ?>
                    </div>
                <?php endif; ?>

                <div class="p-8">
                    <h1 class="text-4xl font-bold text-dark mb-4">
                        <?php the_title(); ?>
                    </h1>

                    <div class="text-gray-500 mb-6">
                        <?php echo get_the_date(); ?> • <?php the_author(); ?>
                    </div>

                    <div class="prose max-w-none">
                        <?php the_content(); ?>
                    </div>
                </div>
            </article>

        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>