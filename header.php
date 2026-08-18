<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right');
    bloginfo('name'); ?></title>
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
                <!-- Right Block -->
                <div class="flex items-center justify-between border-right">
                    <!-- Contacts -->
                     <a href="https://t.me/" title="Telegram" class="flex gap-[18px] items-center">
                        <div>
                            <img src="/tg.svg" alt="Telegram" title="Telegram">
                        </div>
                        <span>
                            Telegram
                        </span>
                     </a>
                     <a href="https://vk.ru/" title="Вконтакте" class="flex gap-[18px] items-center">
                        <div>
                            <img src="/vk.svg" alt="Вконтакте" title="Вконтакте">
                        </div>
                        <span>
                            Вконтакте
                        </span>
                     </a>
                     <a href="https://max.ru/" title="Max" class="flex gap-[18px] items-center">
                        <div>
                            <img src="/max.svg" alt="Max" title="Max">
                        </div>
                        <span>
                            Max
                        </span>
                     </a>
                </div>
                <div class="flex items-center justify-between border-right">
                    <!-- Phone -->
                     <a href="tel:+78332224294" title="Телефон" class="flex gap-[18px] items-center">
                        <div>
                            <img src="/phone.svg" alt="Телефон" title="Телефон">
                        </div>
                        <div>
                            <div class="phone">+7(8332) 22-42-94</div>
                            <div class="address">г. Киров, ул. Улица, д. 12</div>
                        </div>
                     </a>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex space-x-[12px] justify-between">
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