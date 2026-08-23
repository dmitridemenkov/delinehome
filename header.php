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
    <header class="py-[14px] bg-[#20436C]">
        <div class="container mx-auto">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <div class="flex items-center border-r border-white pr-4 me-4">
                        <a href="<?php echo home_url(); ?>" class="inline-block w-[122px] bg-white rounded-[8px] py-[4px] px-[8px] transition hover:-translate-y-[2px]">
                            <?php if ($site['logo_id'] && $logo_url = wp_get_attachment_url($site['logo_id'])): ?>
                                <img src="<?php echo esc_url($logo_url); ?>"
                                    alt="<?php echo esc_attr($site['logo_alt']); ?>"
                                    title="<?php echo esc_attr($site['logo_title']); ?>">
                            <?php else: ?>
                                <span class="text-2xl font-bold text-primary"><?php bloginfo('name'); ?></span>
                            <?php endif; ?>
                        </a>
                        <div>Кухни & Шкафы</div>
                    </div>
                </div>

                <?php if (!empty($site['contacts'])): ?>
                <!-- Contacts -->
                <div class="flex items-center justify-between border-right">
                    <?php foreach ($site['contacts'] as $contact):
                        $icon_url = $contact['icon_id'] ? wp_get_attachment_url($contact['icon_id']) : '';
                    ?>
                    <a href="<?php echo esc_url($contact['url']); ?>"
                       title="<?php echo esc_attr($contact['label']); ?>"
                       class="flex gap-[18px] items-center">
                        <?php if ($icon_url): ?>
                        <div>
                            <img src="<?php echo esc_url($icon_url); ?>"
                                 alt="<?php echo esc_attr($contact['label']); ?>"
                                 title="<?php echo esc_attr($contact['label']); ?>">
                        </div>
                        <?php endif; ?>
                        <span><?php echo esc_html($contact['label']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($site['phone'] || $site['address']): ?>
                <!-- Phone & Address -->
                <div class="flex items-center justify-between border-right">
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $site['phone'])); ?>"
                       title="Телефон"
                       class="flex gap-[18px] items-center">
                        <div>
                            <?php if ($site['phone']): ?>
                            <div class="phone"><?php echo esc_html($site['phone']); ?></div>
                            <?php endif; ?>
                            <?php if ($site['address']): ?>
                            <div class="address"><?php echo esc_html($site['address']); ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endif; ?>
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
