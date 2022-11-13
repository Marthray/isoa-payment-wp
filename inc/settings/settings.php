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
        'Nombre Empresa',
        'isoa_payment_settings_name_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_firstName',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_firstName',
        'Nombre Solicitante',
        'isoa_payment_settings_firstName_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_lastName',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_lastName',
        'Apellido Solicitante',
        'isoa_payment_settings_lastName_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_userid',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_userid',
        'Nombre de Usuario',
        'isoa_payment_settings_userid_callback',
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
        'isoa_payment_settings_phone',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_phone',
        'Telefono',
        'isoa_payment_settings_phone_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

    //registe input
    register_setting(
        'isoa_payment_settings_page',
        'isoa_payment_settings_password',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    //add settings fields
    add_settings_field(
        'isoa_payment_settings_password',
        'Contraseña',
        'isoa_payment_settings_password_callback',
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
        'Generar Credenciales',
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
        'Private Key',
        'isoa_payment_settings_AES_KEY_callback',
        'isoa_payment_settings_page',
        'isoa_payment_settings_section'
    );

  }

  add_action('admin_init', 'isoa_payment_settings_init');

  function isoa_payment_settings_description_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_description');
    ?>

    <input type="text" name="isoa_payment_settings_description" id="description" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_terceroId_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_terceroId');
    ?>

    <input type="text" name="isoa_payment_settings_terceroId" id="terceroId" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_name_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_name');
    ?>

    <input type="text" name="isoa_payment_settings_name" id="name" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_phone_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_phone');
    ?>

    <input type="text" name="isoa_payment_settings_phone" id="phone" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_email_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_email');
    ?>

    <input type="text" name="isoa_payment_settings_email" id="email" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_userid_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_userid');
    ?>

    <input type="text" name="isoa_payment_settings_userid" id="userid" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_password_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_password');
    ?>

    <input type="password" name="isoa_payment_settings_password" id="password" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_firstName_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_firstName');
    ?>

    <input type="text" name="isoa_payment_settings_firstName" id="firstName" class="regular-text" value="<?php 
        echo isset($isoa_payment_input_field) ? esc_attr($isoa_payment_input_field) : '';
    ?>">

    <?php
  }

  function isoa_payment_settings_lastName_callback() {
    $isoa_payment_input_field = get_option('isoa_payment_settings_lastName');
    ?>

    <input type="text" name="isoa_payment_settings_lastName" id="lastName" class="regular-text" value="<?php 
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
            
            if(jQuery('#name').val() == '' || jQuery('#email').val() == '' || jQuery('#phone').val() == '' ||
                jQuery('#terceroId').val() == '' || jQuery('#description').val() == '' || jQuery('#userid').val() == '') {
                    alert('Datos faltantes, verificar');
                    return false;
                }

            let json = {
                "business": {
                    "name": jQuery('#name').val(),
                    "description": jQuery('#description').val(),
                    "email": jQuery('#email').val(),
                    "phone": jQuery('#phone').val(),
                    "terceroId": jQuery('#terceroId').val(),
                    "slogan": '',
                    "categoriaComercio": '',
                    "userid": jQuery('#userid').val(),
                    "pais": '',
                    "ciudad": '',
                    "address": {}
                },
                "user": {
                    "name": jQuery('#firstName').val(),
                    "lastName": jQuery('#lastName').val(),
                    "userid": jQuery('#userid').val(),
                    "password": jQuery('#password').val(),
                    "email": jQuery('#email').val()
                }
            }

            console.log(JSON.stringify(json))

            jQuery.ajax({
                url: 'https://api.tuspagos.net:8443/api/business/new_wp',
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(json),
                cache: false,
                success: function(data) {
                    console.log(data)
                    jQuery('#api').val(data.API_KEY)
                    jQuery('#aes').val(data.PRIVATE_KEY)

                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(xhr);
                    console.log(thrownError);
                    if(xhr.responseText != null) {
                        alert(xhr.responseText)
                    }
                }
            })

            //document.getElementById('api').value="abc123"
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
  