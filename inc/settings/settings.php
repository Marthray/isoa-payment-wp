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

 /**
  * Setting template page
  */

  function isoa_payment_settings_tempalte_callback() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form action="options.php" method = "post">
            <?php

                //putting input fields and button
                settings_fields('isoa_payment_settings_page');

                //putting section
                do_settings_sections('isoa_payment_settings_page');

                //button
                submit_button('Save Changes');

            ?>
        </form>
    </div>
    <?php
  }

  function isoa_payment_settings_init() {
    add_settings_section(
        'isoa_payment_settings_section',
        'ISOA Payment Page',
        '',
        'isoa_payment_settings_page'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_input_field',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_input_field',
        'Input Field',
        'isoa_payment_settings_input_field_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_input_field2',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_input_field2',
        'Input Field 2',
        'isoa_payment_settings_input_field2_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );
  }

  add_action('admin_init', 'isoa_payment_settings_init');

  function isoa_payment_settings_input_field_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_input_field');
    ?>

    <input type="text" name="isoa_payment_settings_input_field" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_input_field2_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_input_field2');
    ?>

    <input type="text" name="isoa_payment_settings_input_field2" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }