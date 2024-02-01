<?php
add_action( 'plugins_loaded', 'tusPagos_init_gateway_class' );
function tusPagos_init_gateway_class() {
    class TusPagos_Gateway extends WC_Payment_Gateway {
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
        protected $banks;
        protected $currency;

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

            /** BANKS */
            $this->banks = array(
                array(
                    "codigo"=> "0001",
                    "nombre"=> "Banco Central de Venezuela",
                    "rif"=> "G200001100"
                ),
                array(
                    "codigo"=> "0102",
                    "nombre"=> "Banco de Venezuela S.A.C.A. Banco Universal",
                    "rif"=> "G200099976"
                ),
                array(
                    "codigo"=> "0104",
                    "nombre"=> "Venezolano de Crédito, S.A. Banco Universal",
                    "rif"=> "J000029709"
                ),
                array(
                    "codigo"=> "0105",
                    "nombre"=> "Banco Mercantil, C.A. Banco Universal",
                    "rif"=> "J000029610"
                ),
                array(
                    "codigo"=> "0108",
                    "nombre"=> "Banco Provincial, S.A. Banco Universal",
                    "rif"=> "J000029679"
                ),
                array(
                    "codigo"=> "0114",
                    "nombre"=> "Bancaribe C.A. Banco Universal",
                    "rif"=> "J000029490"
                ),
                array(
                    "codigo"=> "0115",
                    "nombre"=> "Banco Exterior C.A. Banco Universal",
                    "rif"=> "J000029504"
                ),
                array(
                    "codigo"=> "0116",
                    "nombre"=> "Banco Occidental de Descuento, Banco Universal C.A",
                    "rif"=> "J300619460"
                ),
                array(
                    "codigo"=> "0128",
                    "nombre"=> "Banco Caroní C.A. Banco Universal",
                    "rif"=> "J095048551"
                ),
                array(
                    "codigo"=> "0134",
                    "nombre"=> "Banesco Banco Universal S.A.C.A.",
                    "rif"=> "J070133805"
                ),
                array(
                    "codigo"=> "0137",
                    "nombre"=> "Banco Sofitasa, Banco Universal",
                    "rif"=> "J090283846"
                ),
                array(
                    "codigo"=> "0138",
                    "nombre"=> "Banco Plaza, Banco Universal",
                    "rif"=> "J002970553"
                ),
                array(
                    "codigo"=> "0146",
                    "nombre"=> "Banco de la Gente Emprendedora C.A",
                    "rif"=> "J301442040"
                ),
                array(
                    "codigo"=> "0151",
                    "nombre"=> "BFC Banco Fondo Común C.A. Banco Universal",
                    "rif"=> "J000723060"
                ),
                array(
                    "codigo"=> "0156",
                    "nombre"=> "100% Banco, Banco Universal C.A.",
                    "rif"=> "J085007768"
                ),
                array(
                    "codigo"=> "0157",
                    "nombre"=> "DelSur Banco Universal C.A.",
                    "rif"=> "J000797234"
                ),
                array(
                    "codigo"=> "0163",
                    "nombre"=> "Banco del Tesoro, C.A. Banco Universal",
                    "rif"=> "G200051876"
                ),
                array(
                    "codigo"=> "0166",
                    "nombre"=> "Banco Agrícola de Venezuela, C.A. Banco Universal",
                    "rif"=> "G200057955"
                ),
                array(
                    "codigo"=> "0168",
                    "nombre"=> "Bancrecer, S.A. Banco Microfinanciero",
                    "rif"=> "J316374173"
                ),
                array(
                    "codigo"=> "0169",
                    "nombre"=> "Mi Banco, Banco Microfinanciero C.A.",
                    "rif"=> "J315941023"
                ),
                array(
                    "codigo"=> "0171",
                    "nombre"=> "Banco Activo, Banco Universal",
                    "rif"=> "J080066227"
                ),
                array(
                    "codigo"=> "0172",
                    "nombre"=> "Bancamica, Banco Microfinanciero C.A.",
                    "rif"=> "J316287599"
                ),
                array(
                    "codigo"=> "0173",
                    "nombre"=> "Banco Internacional de Desarrollo, C.A. Banco Universal",
                    "rif"=> "J294640109"
                ),
                array(
                    "codigo"=> "0174",
                    "nombre"=> "Banplus Banco Universal, C.A",
                    "rif"=> "J000423032"
                ),
                array(
                    "codigo"=> "0175",
                    "nombre"=> "Banco Bicentenario del Pueblo de la Clase Obrera, Mujer y Comunas B.U.",
                    "rif"=> "G200091487"
                ),
                array(
                    "codigo"=> "0176",
                    "nombre"=> "Novo Banco, S.A. Sucursal Venezuela Banco Universal",
                    "rif"=> "J308918644"
                ),
                array(
                    "codigo"=> "0177",
                    "nombre"=> "Banco de la Fuerza Armada Nacional Bolivariana, B.U.",
                    "rif"=> "G200106573"
                ),
                array(
                    "codigo"=> "0190",
                    "nombre"=> "Citibank N.A.",
                    "rif"=> "J000526621"
                ),
                array(
                    "codigo"=> "0191",
                    "nombre"=> "Banco Nacional de Crédito, C.A. Banco Universal",
                    "rif"=> "J309841327"
                ),
                array(
                    "codigo"=> "0601",
                    "nombre"=> "Instituto Municipal de Crédito Popular",
                    "rif"=> "G200068973"
                )
            );
            /** END BANKS */

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
            $this->init_form_fields();

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
            $this->currency = get_option( 'isoa_payment_settings_currency' );

            if($this->rate <= 0) {
                $this->rate = 1;
             }

            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            // We need custom JavaScript to obtain a token
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

            wp_register_style( 'generalStyle', plugins_url( 'assets/styles.css?v=1.2', __FILE__ ) );
            wp_enqueue_style( 'generalStyle' );

            wp_register_script( 'woocommerce_bank_isoa', plugins_url( 'assets/banks.js?v=2.1', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'woocommerce_bank_isoa' );

            wp_enqueue_script('sweetAlert2', plugins_url('assets/sweetalert2.js', __FILE__ ));
	        wp_register_script( 'woocommerce_bvc_isoa', plugins_url( 'assets/bvc.js?v=1.1', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'woocommerce_bvc_isoa' );
            
            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
 		}

		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
 		public function init_form_fields(){
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Activar / Desactivar',
                    'label'       => 'Activar Botón de Pago TusPagos',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Título',
                    'type'        => 'text',
                    'description' => 'Boton de Pago',
                    'default'     => $this->method_title,
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Descripción',
                    'type'        => 'textarea',
                    'description' => 'Descripcion del plugin',
                    'default'     => $this->method_description,
                ),
                'rate' => array(
                    'title'       => 'Tasa de USD',
                    'type'        => 'number',
                    'description' => 'Contiene la tasa a la que se le cobrará el dolar',
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

        protected function obtenerTasa() {
            $endpoint = "https://venecodollar.vercel.app/api/v1/dollar/entity?name=BCV";
            $tasa = 1;
            $args = array(
                'headers' => array( 
                    'Content-Type' => 'application/json'
                  ),
                  'method'      => 'GET'
            );
            $response = wp_remote_get($endpoint, $args );

            if( !is_wp_error( $response ) ) {
                $body = json_decode( $response['body'], true );
                if($body['OK'] == "1") {
                    $tasa = $body['Data']['info']['dollar'];
                }
                echo $body;
            }

            return $tasa;
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
                $ivlen = openssl_cipher_iv_length($encrypt_method);
                $iv = $this->generateRandomString($ivlen);
                $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, OPENSSL_RAW_DATA, $iv));
                $output = substr($output, 0, 10) . $iv . substr($output, 10);

                $ivExtracted = substr($output, 10, $ivlen);
                $cont = 10 + $ivlen;
                $string = substr($output, 0, 10) . substr($output, $cont);
                
            } else {
                if ($action == 'decrypt') {
                    try {
                        $ivlen = openssl_cipher_iv_length($encrypt_method);
                        $iv = substr($string, 10, $ivlen);
                        $cont = 10 + $ivlen;
                        $string = substr($string, 0, 10) . substr($string, $cont);
                        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, OPENSSL_RAW_DATA, $iv);
                    }
                    catch(Exception $e) {
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