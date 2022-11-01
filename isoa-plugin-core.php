<?php

add_action( 'plugins_loaded', 'isoa_init_gateway_class' );
function isoa_init_gateway_class() {
    class WC_ISOA_Gateway extends WC_Payment_Gateway {
        public $key;
        public $hash;
        public $rate;
        public $endpointAPI;
        protected $regexPhone;
        protected $regexCiRif;
        protected $regexTokenC2P;
        protected $regexBank;
        protected $regexExpiryDate;
        protected $regexTDC;
        protected $regexCVV;
        protected $regexAccount;

 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct($id, $title, $description) {
            $this->id = $id; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = $title;
            $this->method_description = $description; // will be displayed on the options page
            $this->regexPhone = "/(0412|0414|0416|0426|0424)([0-9]){7}/";
            $this->regexCiRif = "/[0-9]{5,}/";
            $this->regexTokenC2P = "/[0-9]{5,}/";
            $this->regexBank = "/[0-9]{4}/";
            $this->regexExpiryDate = "/[0-9]{2}\/[0-9]{2}/";
            $this->regexTDC = "/[0-9]{16}/";
            $this->regexCVV = "/[0-9]{3}/";
            $this->regexAccount = "/(0104)([0-9]){16}/";
            $this->regexAccount2 = "/([0-9]){20}/";

            //Set the options
            if($this->get_option( 'prod_key' ) != get_option( 'isoa_payment_settings_AES_KEY' )) {
                $this->update_option('prod_key', get_option( 'isoa_payment_settings_AES_KEY' ));
            }

            if($this->get_option( 'prod_hash' ) != get_option( 'isoa_payment_settings_API_KEY' )) {
                $this->update_option('prod_hash', get_option( 'isoa_payment_settings_API_KEY' ));
            }

            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields($title, $description);

            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
            $this->testmode = 'yes' === $this->get_option( 'testmode' );
            $this->rate = $this->get_option( 'rate' );
            $this->key = $this->testmode ? $this->get_option( 'test_key' ) : $this->get_option( 'prod_key' );
            //$this->vector = $this->testmode ? $this->get_option( 'test_vector' ) : $this->get_option( 'prod_vector' );
            $this->hash = $this->testmode ? $this->get_option( 'test_hash' ) : $this->get_option( 'prod_hash' );
            $this->endpointAPI = $this->testmode ? $this->get_option( 'test_url' ) : $this->get_option( 'prod_url' );

            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            // We need custom JavaScript to obtain a token
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
            
            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
 		}

		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
 		public function init_form_fields($title, $description){
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Activar / Desactivar',
                    'label'       => 'Activar Botón de Pago ISOA',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Título',
                    'type'        => 'text',
                    'description' => 'Boton de Pago',
                    'default'     => $description,
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Descripción',
                    'type'        => 'textarea',
                    'description' => 'Descripcion del plugin',
                    'default'     => $description,
                ),
                'rate' => array(
                    'title'       => 'Tasa de USD',
                    'type'        => 'number',
                    'description' => 'Contiene la tasa a la que se le cobrará el dolar',
                ),
                'testmode' => array(
                    'title'       => 'Ambiente de desarrolo',
                    'label'       => 'Activar ambiente de desarrollo',
                    'type'        => 'checkbox',
                    'description' => 'Para activar ambiente de desarrollo',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ),
                'test_hash' => array(
                    'title'       => 'API Key Desarrollo',
                    'type'        => 'text'
                ),
                'test_key' => array(
                    'title'       => 'Llave Desarrollo',
                    'type'        => 'text'
                ),
                'test_url' => array(
                    'title'       => 'URL Desarrollo',
                    'type'        => 'text',
                ),
                'prod_hash' => array(
                    'title'       => 'API Key Producción',
                    'type'        => 'text'
                ),
                'prod_key' => array(
                    'title'       => 'Llave Producción',
                    'type'        => 'text'
                ),
                'prod_url' => array(
                    'title'       => 'URL Producción',
                    'type'        => 'text',
                )
            );
	 	}

		/*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
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
            if ( empty( $this->key ) || empty( $this->hash ) || empty( $this->rate ) || $this->rate <= 0) {
                return;
            }

            // do not work with card detailes without SSL unless your website is in a test mode
            if ( ! $this->testmode && ! is_ssl() ) {
                return;
            }

	 	}

		/*
		 * In case you need a webhook, like PayPal IPN etc
		 */
		public function webhook() {
					
	 	}

        
        protected function encrypt_decrypt($action, $string, $key)
        {
            /* =================================================
            * ENCRYPTION-DECRYPTION
            * =================================================
            * ENCRYPTION: encrypt_decrypt('encrypt', $string);
            * DECRYPTION: encrypt_decrypt('decrypt', $string) ;
            */
            $output = '';
            $encrypt_method = "AES-256-CBC";
            if ($action == 'encrypt') {
                echo ">>>JSON STRING\n\n";
                echo "$string\n\n";
                $ivlen = openssl_cipher_iv_length($encrypt_method);
                $iv = $this->generateRandomString($ivlen);
                $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, OPENSSL_RAW_DATA, $iv));
                $output = substr($output, 0, 10) . $iv . substr($output, 10);
                echo "$output\n\n";

                $ivExtracted = substr($output, 10, $ivlen);
                echo "$ivExtracted\n\n";
                $cont = 10 + $ivlen;
                echo "$cont\n\n";
                $string = substr($output, 0, 10) . substr($output, $cont);
                
                echo "$string\n\n";
            } else {
                if ($action == 'decrypt') {
                    try {
                        $ivlen = openssl_cipher_iv_length($encrypt_method);
                        $iv = substr($string, 10, $ivlen);
                        echo "$iv\n\n";
                        $cont = 10 + $ivlen;
                        echo "$cont\n\n";
                        echo "$string\n\n";
                        $string = substr($string, 0, 10) . substr($string, $cont);
                        echo "$string\n\n";
                        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, OPENSSL_RAW_DATA, $iv);
                        echo "$output\n\n";
                    }
                    catch(Exception $e) {
                        echo "Esto revento";
                        $output = '';
                    }
                }
            }
            return $output;
        }

        private function generateRandomString($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }
 	}
}
?>