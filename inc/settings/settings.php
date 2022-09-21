<?php
/**
 * Menu PHP
 */

 function isoa_payment_settings_menu() {
    add_menu_page(
        'Isoa Payment Settings',
        'Isoa Payment Settings',
        'manage_options',
        'isoa_payment_settings_page',
        'isoa_payment_settings_tempalte_callback',
        '',
        null
    );
 }

 add_action('admin_menu', 'isoa_payment_settings_menu');