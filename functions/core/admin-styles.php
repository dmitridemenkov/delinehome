<?php
add_action('admin_head', function() {
    ?>
    <style>
        #adminmenu, 
        #adminmenu .wp-submenu, 
        #adminmenuback, 
        #adminmenuwrap {
            background-color: #265d94ff !important;
        }
        #wpadminbar {
            background-color: #164a72ff !important;
        }
        #adminmenu li.menu-top:hover, #adminmenu li.opensub>a.menu-top, #adminmenu li>a.menu-top:focus {
            background-color: #17385fff !important;
        }
        #wpadminbar .menupop .ab-sub-wrapper, #wpadminbar .shortlink-input {
            background-color: #164a72ff !important;
        }
        #wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item, #wpadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus {
            background-color: #17385fff !important;
        }
        #wpadminbar .quicklinks .menupop ul.ab-sub-secondary, #wpadminbar .quicklinks .menupop ul.ab-sub-secondary .ab-submenu {
            background: #0d3d61 !important;
        }
        #collapse-button {
            color: #6c98c1;
        }
        #collapse-button:hover {
            color: #96c0e4ff;
        }
        li#wp-admin-bar-wp-logo {
            display: none;
        }
        body {
            background: rgb(0 0 0 / 1%);
        }
    </style>
    <?php
});

add_action('admin_head', function() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geologica:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body,
        #wpadminbar * {
            font-family: 'Geologica', -apple-system, BlinkMacSystemFont, sans-serif;
        }
    </style>
    <?php
});