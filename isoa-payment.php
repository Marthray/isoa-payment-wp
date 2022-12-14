<?php
/*
 * Plugin Name: ISOA Custom Payment Gateway
 * Plugin URI: 
 * Description: Process payments through Venezolano de Credito's API REST (BVC)
 * Author: Brian Amaya
 * Author URI: http://isoatec.com
 * Version: 1.0.0
 */

// Defining root DIR
include_once( WP_PLUGIN_DIR . '/pluginISOA/isoa-plugin-core.php' );

//Menu
require_once WP_PLUGIN_DIR . '/pluginISOA/inc/settings/settings.php';

/**
 * BVC PAYMENT GATEWAY
 */

 /*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'acc_add_gateway_isoa');
function acc_add_gateway_isoa( $gateways ) {
	$gateways[] = 'WC_ACC_Gateway_ISOA'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'acc_init_gateway_isoa' );
function acc_init_gateway_isoa() {

	class WC_ACC_Gateway_ISOA extends WC_ISOA_Gateway {
 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {
            parent::__construct("accisoa", "Pago por cuenta BVC ISOA", "Procesar con cuenta BVC ISOA");
 		}

		/**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		public function payment_fields() {
            // ok, let's display some description before the payment form
            if ( $this->description ) {
                // you can instructions for test mode, I mean test card numbers etc.
                if ( $this->testmode ) {
                    $this->description .= ' MODO DESARROLLO ACTIVADO.';
                    $this->description  = trim( $this->description );
                }
                // display the description with <p> tags etc.
                echo wpautop( wp_kses_post( $this->description ) );
            }
        
            // I will echo() the form, but you can close PHP tags and print it directly in HTML
            echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-form" class="wc-payment-form" style="background:transparent;">';
        
            // Add this action hook if you want your custom payment gateway to support it
            do_action( 'woocommerce_isoa_bvc_form_start', $this->id );
        
            // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
            echo '<div class="form-row form-row-wide"><label>N??mero de Cuenta <span class="required">*</span></label>
                <input id="bvc_accNumber_2" name="bvc_accNumber_2" type="text" maxlength = "20" inputmode="decimal" autocomplete="off" class="w-100">
                </div>
                <div class="form-row form-row-wide">
                    <label>Nacionalidad <span class="required">*</span></label>
                    <select id ="bvc_precirif_2" name="bvc_precirif_2">
                        <option value="">-</option>
                        <option value="V">V</option>
                        <option value="E">E</option>
                        <option value="P">P</option>
                    </select>
                </div>
                <div class="form-row form-row-wide">
                    <label>C??dula <span class="required">*</span></label>
                    <input id="bvc_cirif_2" name="bvc_cirif_2" type="text" autocomplete="off" placeholder="1234568" class="w-100">
                </div>
                <div class="clear"></div>';
        
            do_action( 'woocommerce_isoa_bvc_form_end', $this->id );
        
            echo '<div class="clear"></div></fieldset>';
		}

		/*
 		 * Fields validation, more in Step 5
		 */
		public function validate_fields() {
            if( empty( $_POST[ 'bvc_accNumber_2' ]) || !preg_match($this->regexAccount, $_POST[ 'bvc_accNumber_2' ]) ) {
                wc_add_notice(  'Numero de cuenta inv??lida!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'bvc_cirif_2' ]) || !preg_match($this->regexCiRif, $_POST[ 'bvc_cirif_2' ]) ) {
                wc_add_notice(  'Identificacion inv??lida! ', 'error' );
                return false;
            }
            if( empty( $_POST[ 'bvc_precirif_2' ]) ) {
                wc_add_notice(  'Nacionalidad inv??lida!', 'error' );
                return false;
            }
            return true;
		}

        public function payment_scripts() {
            // we need JavaScript to process a token only on cart/checkout pages, right?
            if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
                return;
            }

            // if our payment gateway is disabled, we do not have to enqueue JS too
            if ( 'no' === $this->enabled ) {
                return;
            }

            // no reason to enqueue JavaScript if API keys are not set
            if ( empty( $this->key ) || empty( $this->vector ) || empty( $this->hash ) || empty( $this->rate ) || $this->rate <= 0) {
                return;
            }

            // do not work with card detailes without SSL unless your website is in a test mode
            if ( ! $this->testmode && ! is_ssl() ) {
                return;
            }

            // and this is our custom JS in your plugin directory that works with token.js
            wp_register_style( 'formStyle', plugins_url( 'assets/styles.css', __FILE__ ) );
            wp_enqueue_style( 'formStyle' );

            wp_enqueue_script('sweetAlert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11');
	        wp_register_script( 'woocommerce_bvc_isoa', plugins_url( 'assets/bvc.js', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'woocommerce_bvc_isoa' );

	 	}

		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {
            global $woocommerce;
 
            // we need it to get any order detailes
            $order = wc_get_order( $order_id );
            $data = $order->get_data(); // order data

            //TODO: Preguntar el tipo de pago, ahorita es solo TDC
            $dtArray = null;
            if ($_POST['getTokenStep'] == "1") {
                $dtArr = array(
                    'method' => 'TRN',
                    'tipoPago' => 'BVC',
                    'preCiRif'=> $_POST[ 'bvc_precirif_2' ],
                    'ciRif' => $_POST[ 'bvc_cirif_2' ],
                    'monto' => $order->get_total(),
                    'account' => $_POST[ 'bvc_accNumber_2' ],
                    'concept'=> $order->get_customer_note(),
                    'clientName' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'postalCode' => '',
                    'address' => '',
                    'email' => $order->get_billing_email(),
                    'coin' => ''
                );
            } else {
                $dtArr = array(
                    'method' => 'TRN',
                    'tipoPago'=> 'BVC-2',
                    'idPago'=> $_POST[ 'idPago' ],
                    'token' => $_POST[ 'token' ]
                );
            }

            $dt = json_encode($dtArr);
            $dt = $this->encrypt_decrypt('encrypt', $dt, $this->key, $this->vector);

            //setting array body
            $bodyIni = array(
                'rmv' => $dt
            );
         
            /*
            * Array with parameters for API interaction
            */
            $args = array(
                'headers' => array( 
                    'Content-Type' => 'application/json',
                    'API_KEY' => $this->hash
                  ),
                  'body' => json_encode($bodyIni),
                  'method'      => 'POST',
                  'data_format' => 'body'
            );

            //TODO: CAMBIAR POR OTROS METODOS DE PAGO
            $endpoint = $this->endpointAPI;
            $response = wp_remote_post($endpoint, $args );

            //Validar si es token o no

            if( !is_wp_error( $response ) ) {
        
                $body = json_decode( $response['body'], true );
                // it could be different depending on your payment processor
                // it could be different depending on your payment processor
                if ( !is_null($body['response']) ) {

                    //validate response from API
                    if (is_null($body['response']['encrypted']) || $body['response']['encrypted'] != '1') {
                        $responseDecrypted = $this->encrypt_decrypt('decrypt', $body['response'], $this->key, $this->vector);
                        if(!is_null($responseDecrypted) && $responseDecrypted != '') {
                            //Get the object from json string
                            $responseObject = json_decode($responseDecrypted, true);
                            if (wp_remote_retrieve_response_code( $response ) == 200) {
                                if ($_POST[ 'getTokenStep' ] == "1") {
                                    wc_add_notice(  "BVC_" . $responseObject['idPago'] , 'error');
                                    return;
                                } else {
                                    if($responseObject['estatus'] == 'Pagado') {
                                        // we received the payment
                                        $order->payment_complete();
                                        $order->reduce_order_stock();
                            
                                        // some notes to customer (replace true with false to make it private)
                                        $order->add_order_note( 'Hey, your order is paid! Thank you!', true );
                            
                                        // Empty cart
                                        $woocommerce->cart->empty_cart();
                            
                                        // Redirect to the thank you page
                                        return array(
                                            'result' => 'success',
                                            'redirect' => $this->get_return_url( $order )
                                        );
                                    } else {
                                        if(!is_null($responseObject['mensaje'])) {
    
                                            wc_add_notice( $responseObject['mensaje'] , 'error' );
                                            return;
                    
                                        } else {
                    
                                            wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                                            return;
                                            
                                        }
                                    }
                                }
                            } else {
                                if(!is_null($responseObject['mensaje'])) {

                                    wc_add_notice( $responseObject['mensaje'] , 'error' );
                                    return;
            
                                } else {
            
                                    wc_add_notice( json_encode($response)  , 'error' );
                                    return;
                                    
                                } 
                            }   
                        }

                    } else if(!is_null($body['response']['mensaje'])) {

                        wc_add_notice( $body['response']['mensaje'] , 'error' );
                        return;

                    } else {

                        wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                        return;
                        
                    }
        
                } else {
                    if ( !is_null($body['response']) ) {
                        if (is_null($body['response']['encrypted']) || $body['response']['encrypted'] != '1') {
                            
                            $responseDecrypted = $this->encrypt_decrypt('decrypt', $body['response'], $this->key, $this->vector);
                            $responseObject = json_decode($responseDecrypted, true);
                            wc_add_notice( $responseObject['mensaje'] , 'error' );
                            return;

                        } else if(!is_null($body['response']['mensaje'])) {

                            wc_add_notice( $body['response']['mensaje'] , 'error' );
                            return;

                        } else {

                            wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                            return;

                        }
                    }
                    
                    return;
                }
        
            } else {
                wc_add_notice(  'Connection error.', 'error' );
                return;
            }
	 	}
 	}
}

/**
 * END BVC PAYMENT GATEWAY***************************************************************************
 */

 /**
  * C2P PAYMENT GATEWAY
  */

  /*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'c2p_add_gateway_isoa' );
function c2p_add_gateway_isoa( $gateways ) {
	$gateways[] = 'WC_C2P_Gateway_ISOA'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'c2p_init_gateway_isoa' );
function c2p_init_gateway_isoa() {

	class WC_C2P_Gateway_ISOA extends WC_ISOA_Gateway {
 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {
            parent::__construct("c2pisoa", "Pago por C2P ISOA", "Procesar con pago C2P ISOA");
 		}

		/**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		public function payment_fields() {
            // ok, let's display some description before the payment form
            if ( $this->description ) {
                // you can instructions for test mode, I mean test card numbers etc.
                if ( $this->testmode ) {
                    $this->description .= ' MODO DESARROLLO ACTIVADO.';
                    $this->description  = trim( $this->description );
                }
                // display the description with <p> tags etc.
                echo wpautop( wp_kses_post( $this->description ) );
            }
        
            // I will echo() the form, but you can close PHP tags and print it directly in HTML
            echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-form" class="wc-payment-form" style="background:transparent;">';
        
            // Add this action hook if you want your custom payment gateway to support it
            do_action( 'woocommerce_isoa_c2p_form_start', $this->id );
        
            // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
            $optionsHTML = '';

            foreach ($this->banks as $b) {
                $optionsHTML .= '<option value="'.$b['codigo'].'">'.$b['nombre'].'</option>';
            }


            echo '<div class="form-row form-row-wide"><label>N??mero Tel??fono <span class="required">*</span></label>
                <input id="c2p_phoneNumber_2" name="c2p_phoneNumber_2" type="text" autocomplete="off" class="w-100">
                </div>
                <div class="form-row form-row-wide">
                    <label>C??digo de Banco <span class="required">*</span></label>
                    <select id ="c2p_bank_2" name="c2p_bank_2" class="banks w-100">
                        <option value="">Seleccione</option>
                        '.$optionsHTML.'
                    </select>
                </div>
                <div class="form-row form-row-wide">
                    <label>Nacionalidad <span class="required">*</span></label>
                    <select id ="c2p_precirif_2" name="c2p_precirif_2">
                        <option value="">-</option>
                        <option value="V">V</option>
                        <option value="E">E</option>
                        <option value="P">P</option>
                    </select>
                </div>
                <div class="form-row form-row-wide">
                    <label>C??dula <span class="required">*</span></label>
                    <input id="c2p_cirif_2" name="c2p_cirif_2" type="text" class="w-100" autocomplete="off" placeholder="1234568">
                </div>
                <div class="form-row form-row-wide">
                    <label>Token <span class="required">*</span></label>
                    <input id="c2p_token_2" name="c2p_token_2" type="text" class="w-100" autocomplete="off" placeholder="12345678">
                </div>
                <div class="clear"></div>';
        
            do_action( 'woocommerce_isoa_c2p_form_end', $this->id );
        
            echo '<div class="clear"></div></fieldset>';
		}

		/*
 		 * Fields validation, more in Step 5
		 */
		public function validate_fields() {
            

            if( empty( $_POST[ 'c2p_phoneNumber_2' ]) || !preg_match($this->regexPhone, $_POST[ 'c2p_phoneNumber_2' ]) ) {
                wc_add_notice(  'Numero de telefono inv??lido!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'c2p_cirif_2' ]) || !preg_match($this->regexCiRif, $_POST[ 'c2p_cirif_2' ]) ) {
                wc_add_notice(  'Identificacion inv??lida! ', 'error' );
                return false;
            }
            if( empty( $_POST[ 'c2p_precirif_2' ]) ) {
                wc_add_notice(  'Nacionalidad inv??lida!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'c2p_bank_2' ]) || !preg_match($this->regexBank, $_POST[ 'c2p_bank_2' ]) ) {
                wc_add_notice(  'Codigo de banco inv??lido!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'c2p_token_2' ]) || !preg_match($this->regexTokenC2P, $_POST[ 'c2p_token_2' ]) ) {
                wc_add_notice(  'Codigo de banco inv??lido!', 'error' );
                return false;
            }
            return true;
		}

		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {
            global $woocommerce;
 
            // we need it to get any order detailes
            $order = wc_get_order( $order_id );
            $data = $order->get_data(); // order data

            //TODO: Preguntar el tipo de pago, ahorita es solo C2P
            $dtArr = array(
                'method' => 'PGM',
                'tipoPago' => 'C2P',
                'otp' => $_POST[ 'c2p_token_2' ],
                'preCiRif'=> $_POST[ 'c2p_precirif_2' ],
                'ciRif' => $_POST[ 'c2p_cirif_2' ],
                'bank' => $_POST[ 'c2p_bank_2' ],
                'monto' => $order->get_total(),
                'phone' => '58' . substr($_POST[ 'c2p_phoneNumber_2' ], 1),
                'concept'=> $order->get_customer_note(),
                'clientName' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'postalCode' => '',
                'address' => '',
                'email' => '',
                'coin' => ''
            );

            echo $dtArr;
            $dt = json_encode($dtArr);
            $dt = $this->encrypt_decrypt('encrypt', $dt, $this->key);

            echo ">>>>>>>>>>>Encriptando: " . $this->key . "\n\n";

            //setting array body
            $bodyIni = array(
                'rmv' => $dt
            );
         
            /*
            * Array with parameters for API interaction
            */
            $args = array(
                'headers' => array( 
                    'Content-Type' => 'application/json',
                    'API_KEY' => $this->hash
                  ),
                  'body' => json_encode($bodyIni),
                  'method'      => 'POST',
                  'data_format' => 'body'
            );
            

            //TODO: CAMBIAR POR OTROS METODOS DE PAGO
            $response = wp_remote_post($this->endpointAPI, $args );
            //wc_add_notice( $body , 'error' );
            if( !is_wp_error( $response ) ) {
        
                $body = json_decode( $response['body'], true );
                echo $response['body'] . "\n\n";
                // it could be different depending on your payment processor
                if ( !is_null($body['response']) ) {

                    //validate response from API
                    if (is_null($body['response']['encrypted']) || $body['response']['encrypted'] != '1') {
                        $responseDecrypted = $this->encrypt_decrypt('decrypt', $body['response'], $this->key);
                        echo "hhh\n\n".$responseDecrypted."\n\n";
                        if(!is_null($responseDecrypted) && $responseDecrypted != '') {
                            //Get the object from json string
                            $responseObject = json_decode($responseDecrypted, true);
                            if (wp_remote_retrieve_response_code( $response ) == 200) {

                                if($responseObject['status'] == 'A') {
                                    // we received the payment
                                    $order->payment_complete();
                                    $order->reduce_order_stock();
                        
                                    // some notes to customer (replace true with false to make it private)
                                    $order->add_order_note( 'Hey, your order is paid! Thank you!', true );
                        
                                    // Empty cart
                                    $woocommerce->cart->empty_cart();
                        
                                    // Redirect to the thank you page
                                    return array(
                                        'result' => 'success',
                                        'redirect' => $this->get_return_url( $order )
                                    );
                                } else {
                                    if(!is_null($responseObject['mensaje'])) {

                                        wc_add_notice( $responseObject['mensaje'] , 'error' );
                
                                    } else {
                
                                        wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                                        
                                    }
                                }
                            } else {
                                if(!is_null($responseObject['mensaje'])) {

                                    wc_add_notice( $responseObject['mensaje'] , 'error' );
            
                                } else {
            
                                    wc_add_notice( 'Por favor, intente m??s tarde'  , 'error' );
                                    
                                } 
                            }   
                        }

                    } else if(!is_null($body['response']['mensaje'])) {

                        wc_add_notice( $body['response']['mensaje'] , 'error' );

                    } else {

                        wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                        
                    }
        
                } else {
                    if ( !is_null($body['response']) ) {
                        if (is_null($body['response']['encrypted']) || $body['response']['encrypted'] != '1') {
                            
                            $responseDecrypted = $this->encrypt_decrypt('decrypt', $body['response'], $this->key, $this->vector);
                            $responseObject = json_decode($responseDecrypted, true);
                            wc_add_notice( $responseObject['mensaje'] , 'error' );

                        } else if(!is_null($body['response']['mensaje'])) {

                            wc_add_notice( $body['response']['mensaje'] , 'error' );

                        } else {

                            wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );

                        }
                    }
                    
                    return;
                }
        
            } else {
                echo ">>>>>>ES ERROR \n\n";
                wc_add_notice(  'Connection error.', 'error' );
                return;
            }
	 	}
 	}
}
/**
 * END C2P **************************************
 */

 /**
  * DEBITO INMEDIATO PAYMENT GATEWAY
  */

  /*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'dbi_add_gateway_isoa' );
function dbi_add_gateway_isoa( $gateways ) {
	$gateways[] = 'WC_DBI_Gateway_ISOA'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'dbi_init_gateway_isoa' );
function dbi_init_gateway_isoa() {

	class WC_DBI_Gateway_ISOA extends WC_ISOA_Gateway {
 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {
            parent::__construct("dbiisoa", "Pago por Debito Inmediato ISOA", "Procesar con pago Debito Inmediato ISOA");
 		}

		/**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		public function payment_fields() {
            // ok, let's display some description before the payment form
            if ( $this->description ) {
                // you can instructions for test mode, I mean test card numbers etc.
                if ( $this->testmode ) {
                    $this->description .= ' MODO DESARROLLO ACTIVADO.';
                    $this->description  = trim( $this->description );
                }
                // display the description with <p> tags etc.
                echo wpautop( wp_kses_post( $this->description ) );
            }
        
            // I will echo() the form, but you can close PHP tags and print it directly in HTML
            echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-form" class="wc-payment-form" style="background:transparent;">';
        
            // Add this action hook if you want your custom payment gateway to support it
            do_action( 'woocommerce_isoa_dbi_form_start', $this->id );

            $optionsHTML = '';

            foreach ($this->banks as $b) {
                $optionsHTML .= '<option value="'.$b['codigo'].'">'.$b['nombre'].'</option>';
            }
        
            // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
            echo '<div class="form-row form-row-wide"><label>N??mero de Cuenta <span class="required">*</span></label>
                <input id="dbi_account" class="w-100" name="dbi_account" type="text" autocomplete="off">
                </div>
                <div class="form-row form-row-wide">
                    <label>C??digo de Banco <span class="required">*</span></label>
                    <select id ="dbi_bank" name="dbi_bank" class="banks w-100">
                        <option value="">Seleccione</option>
                        '.$optionsHTML.'
                    </select>
                </div>
                <div class="form-row form-row-wide">
                    <label>Nacionalidad <span class="required">*</span></label>
                    <select id ="dbi_precirif" class="w-100" name="dbi_precirif">
                        <option value="">-</option>
                        <option value="V">V</option>
                        <option value="E">E</option>
                        <option value="P">P</option>
                        <option value="J">J</option>
                        <option value="G">G</option>
                    </select>
                </div>
                <div class="form-row form-row-wide">
                    <label>C??dula <span class="required">*</span></label>
                    <input id="dbi_cirif" class="w-100" name="dbi_cirif" type="text" autocomplete="off" placeholder="1234568">
                </div>
                <div class="form-row form-row-wide">
                    <label>Token <span class="required">*</span></label>
                    <input id="dbi_token" class="w-100" name="dbi_token" type="text" autocomplete="off" placeholder="12345678">
                </div>
                <div class="clear"></div>';
                
        
            do_action( 'woocommerce_isoa_dbi_form_end', $this->id );
        
            echo '<div class="clear"></div></fieldset>';
		}

		/*
 		 * Fields validation, more in Step 5
		 */
		public function validate_fields() {
            

            if( empty( $_POST[ 'dbi_account' ]) ) {
                wc_add_notice(  'Numero de cuenta inv??lido!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'dbi_cirif' ]) || !preg_match($this->regexCiRif, $_POST[ 'dbi_cirif' ]) ) {
                wc_add_notice(  'Identificacion inv??lida! ', 'error' );
                return false;
            }
            if( empty( $_POST[ 'dbi_precirif' ]) ) {
                wc_add_notice(  'Nacionalidad inv??lida!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'dbi_bank' ]) || !preg_match($this->regexBank, $_POST[ 'dbi_bank' ]) ) {
                wc_add_notice(  'Codigo de banco inv??lido!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'dbi_token' ]) ) {
                wc_add_notice(  'Token inv??lido!', 'error' );
                return false;
            }
            return true;
		}

		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {
            global $woocommerce;
 
            // we need it to get any order detailes
            $order = wc_get_order( $order_id );
            $data = $order->get_data(); // order data

            //TODO: Preguntar el tipo de pago, ahorita es solo C2P
            $dtArr = array(
                'method' => 'DEI',
                'tipoPago' => 'DEI',
                'otp' => $_POST[ 'dbi_token' ],
                'preCiRif'=> $_POST[ 'dbi_precirif' ],
                'ciRif' => $_POST[ 'dbi_cirif' ],
                'bank' => $_POST[ 'dbi_bank' ],
                'monto' => $order->get_total(),
                'phone' => $_POST[ 'dbi_account' ],
                'concept'=> $order->get_customer_note(),
                'clientName' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'postalCode' => '',
                'address' => '',
                'email' => '',
                'coin' => ''
            );

            $dt = json_encode($dtArr);
            $dt = $this->encrypt_decrypt('encrypt', $dt, $this->key);

            echo ">>>>>>>>>>>Encriptando: " . $this->key . "\n\n";

            //setting array body
            $bodyIni = array(
                'rmv' => $dt
            );
         
            /*
            * Array with parameters for API interaction
            */
            $args = array(
                'headers' => array( 
                    'Content-Type' => 'application/json',
                    'API_KEY' => $this->hash
                  ),
                  'body' => json_encode($bodyIni),
                  'method'      => 'POST',
                  'data_format' => 'body'
            );
            

            //TODO: CAMBIAR POR OTROS METODOS DE PAGO
            $response = wp_remote_post($this->endpointAPI, $args );
            //wc_add_notice( $body , 'error' );
            if( !is_wp_error( $response ) ) {
        
                $body = json_decode( $response['body'], true );
                echo $response['body'] . "\n\n";
                // it could be different depending on your payment processor
                if ( !is_null($body['response']) ) {

                    //validate response from API
                    if (is_null($body['response']['encrypted']) || $body['response']['encrypted'] != '1') {
                        $responseDecrypted = $this->encrypt_decrypt('decrypt', $body['response'], $this->key);
                        echo "hhh\n\n".$responseDecrypted."\n\n";
                        if(!is_null($responseDecrypted) && $responseDecrypted != '') {
                            //Get the object from json string
                            $responseObject = json_decode($responseDecrypted, true);
                            if (wp_remote_retrieve_response_code( $response ) == 200) {

                                if($responseObject['status'] == 'A') {
                                    // we received the payment
                                    $order->payment_complete();
                                    $order->reduce_order_stock();
                        
                                    // some notes to customer (replace true with false to make it private)
                                    $order->add_order_note( 'Hey, your order is paid! Thank you!', true );
                        
                                    // Empty cart
                                    $woocommerce->cart->empty_cart();
                        
                                    // Redirect to the thank you page
                                    return array(
                                        'result' => 'success',
                                        'redirect' => $this->get_return_url( $order )
                                    );
                                } else {
                                    if(!is_null($responseObject['mensaje'])) {

                                        wc_add_notice( $responseObject['mensaje'] , 'error' );
                
                                    } else {
                
                                        wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                                        
                                    }
                                }
                            } else {
                                if(!is_null($responseObject['mensaje'])) {

                                    wc_add_notice( $responseObject['mensaje'] , 'error' );
            
                                } else {
            
                                    wc_add_notice( 'Por favor, intente m??s tarde'  , 'error' );
                                    
                                } 
                            }   
                        }

                    } else if(!is_null($body['response']['mensaje'])) {

                        wc_add_notice( $body['response']['mensaje'] , 'error' );

                    } else {

                        wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                        
                    }
        
                } else {
                    if ( !is_null($body['response']) ) {
                        if (is_null($body['response']['encrypted']) || $body['response']['encrypted'] != '1') {
                            
                            $responseDecrypted = $this->encrypt_decrypt('decrypt', $body['response'], $this->key, $this->vector);
                            $responseObject = json_decode($responseDecrypted, true);
                            wc_add_notice( $responseObject['mensaje'] , 'error' );

                        } else if(!is_null($body['response']['mensaje'])) {

                            wc_add_notice( $body['response']['mensaje'] , 'error' );

                        } else {

                            wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );

                        }
                    }
                    
                    return;
                }
        
            } else {
                echo ">>>>>>ES ERROR \n\n";
                wc_add_notice(  'Connection error.', 'error' );
                return;
            }
	 	}
 	}
}
/**
 * END DEBITO INMEDIATO **************************************
 */

 /**
  * START TDC
  */
/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'tdc_add_gateway_isoa' );
function tdc_add_gateway_isoa( $gateways ) {
	$gateways[] = 'WC_TDC_Gateway_ISOA'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'tdc_init_gateway_isoa' );
function tdc_init_gateway_isoa() {

	class WC_TDC_Gateway_ISOA extends WC_ISOA_Gateway {
 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {
            parent::__construct("tdcisoa", "Pago por TDC ISOA", "Procesar con pago TDC ISOA");
 		}

		/**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		public function payment_fields() {
            // ok, let's display some description before the payment form
            if ( $this->description ) {
                // you can instructions for test mode, I mean test card numbers etc.
                if ( $this->testmode ) {
                    $this->description .= ' MODO DESARROLLO ACTIVADO.';
                    $this->description  = trim( $this->description );
                }
                // display the description with <p> tags etc.
                echo wpautop( wp_kses_post( $this->description ) );
            }
        
            // I will echo() the form, but you can close PHP tags and print it directly in HTML
            echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-form" class="wc-payment-form" style="background:transparent;">';
        
            // Add this action hook if you want your custom payment gateway to support it
            do_action( 'woocommerce_isoa_tdc_form_start', $this->id );
        
            // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
            echo '<div class="form-row form-row-wide"><label>N??mero de Tarjeta <span class="required">*</span></label>
                <input id="tdc_cardNumber_2" class="w-100" name="tdc_cardNumber_2" type="text" maxlength = "16" inputmode="decimal" autocomplete="off">
                </div>
                <div class="form-row form-row-wide">
                    <label>Nacionalidad <span class="required">*</span></label>
                    <select id ="tdc_precirif_2" name="tdc_precirif_2">
                        <option value="">-</option>
                        <option value="V">V</option>
                        <option value="E">E</option>
                        <option value="P">P</option>
                        <option value="J">J</option>
                        <option value="G">G</option>
                    </select>
                </div>
                <div class="form-row form-row-wide">
                    <label>C??dula <span class="required">*</span></label>
                    <input id="tdc_cirif_2" class="w-100" name="tdc_cirif_2" type="text" autocomplete="off" placeholder="1234568">
                </div>
                <div class="form-row form-row-wide">
                    <label>Fecha de expiraci??n <span class="required">*</span></label>
                    <input id="tdc_expiry_2" class="w-100" name="tdc_expiry_2" type="text" autocomplete="off" placeholder="MM/AA" maxlength="5">
                </div>
                <div class="form-row form-row-wide">
                    <label>CVV <span class="required">*</span></label>
                    <input id="tdc_cvv_2" class="w-100" name="tdc_cvv_2" type="password" maxlength="3" autocomplete="off" placeholder="123">
                </div>
                <div class="clear"></div>';
        
            do_action( 'woocommerce_isoa_tdc_form_end', $this->id );
        
            echo '<div class="clear"></div></fieldset>';
		}

		/*
 		 * Fields validation, more in Step 5
		 */
		public function validate_fields() {
            if( empty( $_POST[ 'tdc_cardNumber_2' ]) || !preg_match($this->regexTDC, $_POST[ 'tdc_cardNumber_2' ]) ) {
                wc_add_notice(  'Numero de telefono inv??lido!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'tdc_cirif_2' ]) || !preg_match($this->regexCiRif, $_POST[ 'tdc_cirif_2' ]) ) {
                wc_add_notice(  'Identificacion inv??lida! ', 'error' );
                return false;
            }
            if( empty( $_POST[ 'tdc_precirif_2' ]) ) {
                wc_add_notice(  'Nacionalidad inv??lida!', 'error' );
                return false;
            }
            if( empty( $_POST[ 'tdc_expiry_2' ]) || !preg_match($this->regexExpiryDate, $_POST[ 'tdc_expiry_2' ]) ) {
                wc_add_notice(  'Fecha de expiracion inv??lida', 'error' );
                return false;
            }
            if( empty( $_POST[ 'tdc_cvv_2' ]) || !preg_match($this->regexCVV, $_POST[ 'tdc_cvv_2' ]) ) {
                wc_add_notice(  'Codigo de verificaci??n inv??lido!', 'error' );
                return false;
            }
            return true;
		}

        public function payment_scripts() {
            // we need JavaScript to process a token only on cart/checkout pages, right?
            if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
                return;
            }

            // if our payment gateway is disabled, we do not have to enqueue JS too
            if ( 'no' === $this->enabled ) {
                return;
            }

            // no reason to enqueue JavaScript if API keys are not set
            if ( empty( $this->key ) || empty( $this->vector ) || empty( $this->hash ) || empty( $this->rate ) || $this->rate <= 0) {
                return;
            }

            // do not work with card detailes without SSL unless your website is in a test mode
            if ( ! $this->testmode && ! is_ssl() ) {
                return;
            }

            // and this is our custom JS in your plugin directory that works with token.js
            wp_register_style( 'tdcStyle', plugins_url( 'assets/styles.css', __FILE__ ) );
            wp_enqueue_style( 'tdcStyle' );

	 	}

		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {
            global $woocommerce;
 
            // we need it to get any order detailes
            $order = wc_get_order( $order_id );
            $data = $order->get_data(); // order data

            $dtArr = array(
                'method' => 'TDC',
                'tipoPago' => 'TDC',
                'preCiRif'=> $_POST[ 'tdc_precirif_2' ],
                'ciRif' => $_POST[ 'tdc_precirif_2' ] . $_POST[ 'tdc_cirif_2' ],
                'monto' => $order->get_total(),
                'numTarjeta' => $_POST[ 'tdc_cardNumber_2' ],
                'expiryDate' => $_POST[ 'tdc_expiry_2' ],
                'cvv' => $_POST[ 'tdc_cvv_2' ],
                'concept'=> $order->get_customer_note(),
                'clientName' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), //TOMAR DE DE SESION
                'postalCode' => '',
                'address' => '',
                'email' => $order->get_billing_email(),
                'coin' => ''
            );

            $dt = json_encode($dtArr);
            $dt = $this->encrypt_decrypt('encrypt', $dt, $this->key);

            //setting array body
            $bodyIni = array(
                'rmv' => $dt
            );
         
            /*
            * Array with parameters for API interaction
            */
            $args = array(
                'headers' => array( 
                    'Content-Type' => 'application/json',
                    'API_KEY' => $this->hash
                  ),
                  'body' => json_encode($bodyIni),
                  'method'      => 'POST',
                  'data_format' => 'body'
            );

            //TODO: CAMBIAR POR OTROS METODOS DE PAGO
            $response = wp_remote_post($this->endpointAPI, $args );
            //wc_add_notice( $body , 'error' );
            if( !is_wp_error( $response ) ) {
        
                $body = json_decode( $response['body'], true );
                // it could be different depending on your payment processor
                if ( !is_null($body['response']) ) {

                    //validate response from API
                    if (is_null($body['response']['encrypted']) || $body['response']['encrypted'] != '1') {
                        $responseDecrypted = $this->encrypt_decrypt('decrypt', $body['response'], $this->key);
                        echo "hhh\n\n".$responseDecrypted."\n\n";
                        if(!is_null($responseDecrypted) && $responseDecrypted != '') {
                            //Get the object from json string
                            $responseObject = json_decode($responseDecrypted, true);
                            if (wp_remote_retrieve_response_code( $response ) == 200) {

                                if($responseObject['statusCode'] == 'P') {
                                    // we received the payment
                                    $order->payment_complete();
                                    $order->reduce_order_stock();
                        
                                    // some notes to customer (replace true with false to make it private)
                                    $order->add_order_note( 'Hey, your order is paid! Thank you!', true );
                        
                                    // Empty cart
                                    $woocommerce->cart->empty_cart();
                        
                                    // Redirect to the thank you page
                                    return array(
                                        'result' => 'success',
                                        'redirect' => $this->get_return_url( $order )
                                    );
                                } else {
                                    if(!is_null($responseObject['mensaje'])) {

                                        wc_add_notice( $responseObject['mensaje'] , 'error' );
                
                                    } else {
                
                                        wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                                        
                                    }
                                }
                            } else {
                                if(!is_null($responseObject['mensaje'])) {

                                    wc_add_notice( $responseObject['mensaje'] , 'error' );
            
                                } else {
            
                                    wc_add_notice( 'Por favor, intente m??s tarde'  , 'error' );
                                    
                                } 
                            }   
                        }

                    } else if(!is_null($body['response']['mensaje'])) {

                        wc_add_notice( $body['response']['mensaje'] , 'error' );

                    } else {

                        wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );
                        
                    }
        
                } else {
                    if ( !is_null($body['response']) ) {
                        if (is_null($body['response']['encrypted']) || $body['response']['encrypted'] != '1') {
                            
                            $responseDecrypted = $this->encrypt_decrypt('decrypt', $body['response'], $this->key, $this->vector);
                            $responseObject = json_decode($responseDecrypted, true);
                            wc_add_notice( $responseObject['mensaje'] , 'error' );

                        } else if(!is_null($body['response']['mensaje'])) {

                            wc_add_notice( $body['response']['mensaje'] , 'error' );

                        } else {

                            wc_add_notice( 'Por favor, intente m??s tarde' , 'error' );

                        }
                    }
                    
                    return;
                }
        
            } else {
                wc_add_notice(  'Connection error.', 'error' );
                return;
            }
	 	}
 	}
}
  /**
   * END TDC ******************************************************
   */
?>