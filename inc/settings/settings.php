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
        'isoa_payment_settings_name',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_name',
        'Nombre',
        'isoa_payment_settings_name_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_description',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_description',
        'Descripcion',
        'isoa_payment_settings_description_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_email',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_email',
        'Email',
        'isoa_payment_settings_email_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_terceroId',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_terceroId',
        'Tercero ID',
        'isoa_payment_settings_terceroId_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_send',
        array(
            'type' => 'boolean',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_send',
        'Enviar a Ramon',
        'isoa_payment_settings_send_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    
/**
 * 
 */

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_API_KEY',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_API_KEY',
        'API KEY',
        'isoa_payment_settings_API_KEY_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_AES_KEY',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_AES_KEY',
        'API KEY',
        'isoa_payment_settings_AES_KEY_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

  }

  add_action('admin_init', 'isoa_payment_settings_init');

  function isoa_payment_settings_description_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_description');
    ?>

    <input type="text" name="isoa_payment_settings_description" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_terceroId_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_terceroId');
    ?>

    <input type="text" name="isoa_payment_settings_terceroId" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_name_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_name');
    ?>

    <input type="text" name="isoa_payment_settings_name" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_email_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_email');
    ?>

    <input type="text" name="isoa_payment_settings_email" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_send_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_send');
    ?>

    <button name="isoa_payment_settings_send" onclick = "event.preventDefault();sendToRamon()">Enviar</button>
    <script>
        function sendToRamon() {
            console.log('SE ENVIO LA BROMA');
            //document.getElementById('api').value="abc123"
            jQuery('#api').val("abc123")
            jQuery('#aes').val("def456")
        }
    </script>

    <?php
  }

  //After sending to ramon
  function isoa_payment_settings_API_KEY_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_API_KEY');
    ?>

    <input type="text" id ="api" name="isoa_payment_settings_API_KEY" class="regular-text" readonly value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_AES_KEY_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_AES_KEY');
    ?>

    <input type="text" id ="aes" name="isoa_payment_settings_AES_KEY" class="regular-text" readonly value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }
  