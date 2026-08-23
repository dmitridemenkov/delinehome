<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right');
    bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<?php $site = deline_get_settings(); ?>

<body <?php body_class('min-h-screen flex flex-col'); ?>>
    <header class="pt-[14px] pb-[14px] lg:pb-[0px] bg-[#20436C]">
        <div class="container mx-auto">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <div class="flex items-center">
                        <div class="flex items-center border-r border-white pr-4 me-4">
                            <a href="<?php echo home_url(); ?>"
                                class="inline-block w-[122px] bg-white rounded-[8px] py-[4px] px-[8px] transition hover:-translate-y-[2px]"
                                title="Перейти на Главную">
                                <?php if ($site['logo_id'] && $logo_url = wp_get_attachment_url($site['logo_id'])): ?>
                                    <img src="<?php echo esc_url($logo_url); ?>"
                                        alt="<?php echo esc_attr($site['logo_alt']); ?>"
                                        title="<?php echo esc_attr($site['logo_title']); ?>">
                                <?php else: ?>
                                    <span class="text-2xl font-bold text-primary"><?php bloginfo('name'); ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="text-white text-xs md:text-base">Кухни & Шкафы</div>
                    </div>
                </div>

                <div class="flex flex-wrap">
                    <?php if (!empty($site['contacts'])): ?>
                        <!-- Contacts -->
                        <div class="flex items-center justify-between flex-wrap gap-[12px] lg:gap-[44px] border-right">
                            <?php foreach ($site['contacts'] as $contact):
                                $icon_url = $contact['icon_id'] ? wp_get_attachment_url($contact['icon_id']) : '';
                                ?>
                                <a href="<?php echo esc_url($contact['url']); ?>"
                                    title="<?php echo esc_attr($contact['label']); ?>"
                                    class="flex gap-[18px] items-center transition hover:-translate-y-[2px]">
                                    <?php if ($icon_url): ?>
                                        <div>
                                            <img src="<?php echo esc_url($icon_url); ?>"
                                                alt="<?php echo esc_attr($contact['label']); ?>"
                                                title="<?php echo esc_attr($contact['label']); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <span
                                        class="text-white text-xs md:text-base"><?php echo esc_html($contact['label']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($site['phone'] || $site['address']): ?>
                        <!-- Phone & Address -->
                        <div class="flex items-center justify-between border-l border-[#ffffff4d] ps-[28px] ms-[28px]">
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $site['phone'])); ?>"
                                title="Телефон" class="flex gap-[18px] items-center transition hover:-translate-y-[2px]">
                                <div>
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M5.63111 12.1178C7.87111 16.52 11.48 20.1133 15.8822 22.3689L19.3044 18.9467C19.7244 18.5267 20.3467 18.3867 20.8911 18.5733C22.6333 19.1489 24.5156 19.46 26.4444 19.46C27.3 19.46 28 20.16 28 21.0156V26.4444C28 27.3 27.3 28 26.4444 28C11.8378 28 0 16.1622 0 1.55556C0 0.7 0.7 0 1.55556 0H7C7.85556 0 8.55555 0.7 8.55555 1.55556C8.55555 3.5 8.86667 5.36667 9.44222 7.10889C9.61333 7.65333 9.48889 8.26 9.05333 8.69556L5.63111 12.1178Z"
                                            fill="white" />
                                    </svg>
                                </div>
                                <div>
                                    <?php if ($site['phone']): ?>
                                        <div class="phone text-white text-xs"><?php echo esc_html($site['phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($site['address']): ?>
                                        <div class="address text-white text-xs"><?php echo esc_html($site['address']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                    <button
                        class="flex items-center relative ps-[36px] ms-[36px] transition hover:-translate-y-[2px] cursor-pointer"
                        title="Мини-корзина " aria-label="Открыть мини-корзину">
                        <span id="counter">0</span>
                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M22.4 22.4C20.846 22.4 19.6 23.646 19.6 25.2C19.6 25.9426 19.895 26.6548 20.4201 27.1799C20.9452 27.705 21.6574 28 22.4 28C23.1426 28 23.8548 27.705 24.3799 27.1799C24.905 26.6548 25.2 25.9426 25.2 25.2C25.2 24.4574 24.905 23.7452 24.3799 23.2201C23.8548 22.695 23.1426 22.4 22.4 22.4ZM0 0V2.8H2.8L7.84 13.426L5.936 16.856C5.726 17.248 5.6 17.71 5.6 18.2C5.6 18.9426 5.895 19.6548 6.4201 20.1799C6.9452 20.705 7.65739 21 8.4 21H25.2V18.2H8.988C8.89517 18.2 8.80615 18.1631 8.74051 18.0975C8.67487 18.0318 8.638 17.9428 8.638 17.85C8.638 17.78 8.652 17.724 8.68 17.682L9.94 15.4H20.37C21.42 15.4 22.344 14.812 22.82 13.958L27.832 4.9C27.93 4.676 28 4.438 28 4.2C28 3.8287 27.8525 3.4726 27.59 3.21005C27.3274 2.9475 26.9713 2.8 26.6 2.8H5.894L4.578 0M8.4 22.4C6.846 22.4 5.6 23.646 5.6 25.2C5.6 25.9426 5.895 26.6548 6.4201 27.1799C6.9452 27.705 7.65739 28 8.4 28C9.14261 28 9.8548 27.705 10.3799 27.1799C10.905 26.6548 11.2 25.9426 11.2 25.2C11.2 24.4574 10.905 23.7452 10.3799 23.2201C9.8548 22.695 9.14261 22.4 8.4 22.4Z"
                                fill="white" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Navigation -->
        <nav class="hidden lg:block bg-white mb-[14px] py-[36px]">
            <div class="container mx-auto">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'flex gap-8 justify-between',
                    'fallback_cb' => false,
                ]);
                ?>
            </div>
        </nav>
        <!-- Mobile menu -->
        <div class="md:hidden" id="mobile-menu">
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