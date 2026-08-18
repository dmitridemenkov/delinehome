<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('min-h-screen flex flex-col'); ?>>
    <header class="py-[14px]">
        <div class="container mx-auto">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="<?php echo home_url(); ?>" class="text-2xl font-bold text-primary">
                        <?php bloginfo('name'); ?>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container' => false,
                        'menu_class' => 'flex space-x-8',
                        'fallback_cb' => false,
                    ]);
                    ?>
                </nav>
            </div>
        </div>
        <!-- Mobile menu -->
        <div class="md:hidden hidden py-4" id="mobile-menu">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'flex flex-col space-y-4',
                'fallback_cb' => false,
            ]);
            ?>
        </div>
    </header>
