<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_BusinessPay_Gateway extends WC_Payment_Gateway {
	private
		$gateway_demo_url, $gateway_live_url, $gateway_demo_version, $gateway_live_version, $antifraud_demo_url,
		$antifraud_live_url, $antifraud_demo_version, $antifraud_live_version, $authentication_api, $authentication_key,
		$name_in_invoice, $invoice_prefix, $installments, $installments_minimum, $installments_maximum,
		$installment_minimum_value, $billet_logo_url, $billet_number_days, $billet_instruction_1, $sandbox, $debug,
		$authority, $auth_client_id, $auth_install_id, $auth_last, $auth_next, $auth_interval,
		$enable_client, $enable_install, $antifraud_product_id, $antifraud_login_demo, $antifraud_login_live,
		$antifraud_password_demo, $antifraud_password_live, $antifraud_app_id_demo, $antifraud_app_id_live,
		$antifraud_auth_token, $antifraud_auth_expiration, $auth_api, $auth_scope, $auth_version, $auth_schema,
		$auth_gateway, $default_session_interval, $default_billet_expiration_days, $logger;

	public
		$credit_card, $debit_card, $transfer, $billet, $enable_antifraud;

	public function __construct() {
		$this->id                 = 'businesspay_gateway';
		$this->icon               = apply_filters( 'woocommerce_pagseguro_icon', plugins_url( 'assets/img/BusinessPay-Logo-Positivo.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = true;
		$this->method_title       = esc_html__( 'BusinessPay', 'woocommerce-businesspay' );
		$this->method_description = esc_html__( 'Accept payments using BusinessPay Gateway.', 'woocommerce-businesspay' );
		$this->supports           = array( 'products', 'refunds' );

		$this->gateway_demo_url       = 'https://apidemo.gate2all.com.br';
		$this->gateway_live_url       = 'https://api.gate2all.com.br';
		$this->gateway_demo_version   = 'v1';
		$this->gateway_live_version   = 'v1';
		$this->antifraud_demo_url     = 'https://homologacao.clearsale.com.br';
		$this->antifraud_live_url     = 'https://api.clearsale.com.br';
		$this->antifraud_demo_version = 'v1';
		$this->antifraud_live_version = 'v1';

		$this->init_form_fields();
		$this->init_settings();

		//Settings
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->enabled            = $this->get_option( 'enabled' );
		$this->authentication_api = $this->get_option( 'authentication_api' );
		$this->authentication_key = $this->get_option( 'authentication_key' );
		$this->credit_card        = $this->get_option( 'credit_card' );
		$this->name_in_invoice    = $this->get_option( 'name_in_invoice' );
		$this->invoice_prefix     = $this->get_option( 'invoice_prefix' );

		//Installments
		$this->installments              = $this->get_option( 'installments' );
		$this->installments_minimum      = $this->get_option( 'installments_minimum' );
		$this->installments_maximum      = $this->get_option( 'installments_maximum' );
		$this->installment_minimum_value = $this->get_option( 'installment_minimum_value' );

		//Debit
		$this->debit_card = $this->get_option( 'debit_card' );

		//Debit
		$this->transfer = $this->get_option( 'transfer' );

		//Billet
		$this->billet               = $this->get_option( 'billet' );
		$this->billet_logo_url      = $this->get_option( 'billet_logo_url' );
		$this->billet_number_days   = $this->get_option( 'billet_number_days' );
		$this->billet_instruction_1 = $this->get_option( 'billet_instruction_1' );

		//Tools
		$this->sandbox = $this->get_option( 'sandbox' );
		$this->debug   = $this->get_option( 'debug' );

		//Auth
		$this->auth_client_id            = get_option( 'businesspay_auth_client_id' );
		$this->auth_install_id           = get_option( 'businesspay_auth_install_id' );
		$this->enable_client             = get_option( 'businesspay_enable_client' );
		$this->enable_install            = get_option( 'businesspay_enable_install' );
		$this->enable_antifraud          = get_option( 'businesspay_enable_antifraud' );
		$this->antifraud_product_id      = get_option( 'businesspay_antifraud_product_id' );
		$this->antifraud_login_demo      = get_option( 'businesspay_antifraud_login_demo' );
		$this->antifraud_login_live      = get_option( 'businesspay_antifraud_login_live' );
		$this->antifraud_password_demo   = get_option( 'businesspay_antifraud_password_demo' );
		$this->antifraud_password_live   = get_option( 'businesspay_antifraud_password_live' );
		$this->antifraud_app_id_demo     = get_option( 'businesspay_antifraud_app_id_demo' );
		$this->antifraud_app_id_live     = get_option( 'businesspay_antifraud_app_id_live' );
		$this->antifraud_auth_token      = get_option( 'businesspay_antifraud_auth_token' );
		$this->antifraud_auth_expiration = get_option( 'businesspay_antifraud_auth_expiration' );
		$this->default_session_interval  = '12';

		$this->auth_api     = 'wp-json';
		$this->authority    = 'agenciahypelab';
		$this->auth_scope   = 'authorization';
		$this->auth_version = 'v1';
		$this->auth_schema  = 'https';
		$this->auth_gateway = 'gate2all';

		//Actions
		add_action( 'woocommerce_api_wc_' . $this->id, array(
				$this,
				'webhook_callback'
			)
		);
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array(
			$this,
			'businesspay_admin_panel_gateway'
		) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array(
			$this,
			'businesspay_admin_panel_antifraud'
		) );
		add_action( 'wp_enqueue_scripts', array(
				$this,
				'enqueue_scripts'
			)
		);
		add_action( 'wp_footer', array( $this, 'antifraud_insert_mapper' ) );

		//Filters
		add_filter( 'woocommerce_billing_fields', array(
				$this,
				'set_neighborhood_required'
			)
		);

		//Admin notices
		$this->admin_notices();
	}

	public function is_available() {
		$available = ($this->enabled == 'yes') && is_ssl() && $this->is_configurated() && $this->is_brazilian() && $this->get_session();

		return $available;
	}

	public function admin_options() {
		wp_enqueue_script( 'wc-businesspay-admin-js', plugins_url( 'assets/js/admin.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_BusinessPay::VERSION, true );
		wp_enqueue_style( 'wc-businesspay-admin-css', plugins_url( 'assets/css/admin.css', plugin_dir_path( __FILE__ ) ), null, WC_BusinessPay::VERSION, 'all' );

		//Get session Info
		$this->get_session_renew();

		echo '<table class="businesspay-table">';

		$logo_businesspay = '<img src="' . plugins_url( 'assets/img/BusinessPay-Logotipo-admin.png', plugin_dir_path( __FILE__ ) ) . '">';
		echo $logo_businesspay;

		// Generate the HTML For the settings form.
		$this->generate_settings_html();

		$html_table_start = '<table class="form-table"><tbody>';
		$html_row         = '<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="woocommerce_businesspay_gateway_genericlabel" style="cursor: initial; font-size: 18px; margin-top: 5px;">%s</label>
						</th>
						<td class="forminp">
							<fieldset>
								<legend class="screen-reader-text"><span>%s</span></legend>
								<label for="woocommerce_businesspay_gateway_genericlabel" style="cursor: initial;">%s</label><br>
							</fieldset>
						</td>
					</tr>';
		$html_table_end   = '</table></tbody>';

		$icon_ok  = '<img src="' . plugins_url( 'assets/img/businesspay-icone-habilitado.png', plugin_dir_path( __FILE__ ) ) . '" style="margin-bottom: -12px; margin-right: 10px;">';
		$icon_nok = '<img src="' . plugins_url( 'assets/img/businesspay-icone-desabilitado.png', plugin_dir_path( __FILE__ ) ) . '" style="margin-bottom: -12px; margin-right: 10px;">';

		$client_str    = ( $this->enable_client ) ? $icon_ok . esc_html__( 'Account Enabled', 'woocommerce-businesspay' ) : $icon_nok . esc_html__( 'Account Disabled', 'woocommerce-businesspay' );
		$install_str   = ( $this->enable_install ) ? $icon_ok . esc_html__( 'Installation Enabled', 'woocommerce-businesspay' ) : $icon_nok . esc_html__( 'Installation Disabled', 'woocommerce-businesspay' );
		$antifraud_str = ( $this->enable_antifraud ) ? $icon_ok . esc_html__( 'Antifraud Enabled', 'woocommerce-businesspay' ) : $icon_nok . esc_html__( 'Antifraud Disabled', 'woocommerce-businesspay' );
		$product_str   = ( ! empty( $this->antifraud_product_id ) ) ? $icon_ok . $this->get_antifraud_order_product_name() : $icon_nok . esc_html__( 'Product not configured', 'woocommerce-businesspay' );

		$client_title    = esc_html__( 'Client Account:', 'woocommerce-businesspay' );
		$install_title   = esc_html__( 'Client Installation:', 'woocommerce-businesspay' );
		$antifraud_title = esc_html__( 'Client Antifraud:', 'woocommerce-businesspay' );
		$product_title   = esc_html__( 'Antifraud Product:', 'woocommerce-businesspay' );

		$html_row_str = sprintf( $html_row, $client_title, $client_str, $client_str );
		$html_row_str .= sprintf( $html_row, $install_title, $install_str, $install_str );
		$html_row_str .= sprintf( $html_row, $antifraud_title, $antifraud_str, $antifraud_str );

		if ( $this->enable_antifraud ) {
			$html_row_str .= sprintf( $html_row, $product_title, $product_str, $product_str );
		}

		echo $html_table_start . $html_row_str . $html_table_end;

		$img_src = plugins_url( 'assets/img/businesspay-logotipo-clearsale.png', plugin_dir_path( __FILE__ ) );
		$img_str = '<label style="cursor: initial;">' . esc_html__( "Antifraud by ", 'woocommerce-businesspay' ) . '</label><img src="' . $img_src . '" style="margin-bottom: -8px;">';
		echo '<p class="businesspay-antifraud-p">' . $img_str . '</p>';
		echo '</table>';
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'general'                   => array(
				'type'        => 'title',
				'title'       => esc_html__( 'BusinessPay Payment Gateway', 'woocommerce-businesspay' ),
				'description' => '',
			),
			'enabled'                   => array(
				'type'        => 'checkbox',
				'title'       => esc_html__( 'Enable BusinessPay', 'woocommerce-businesspay' ),
				'label'       => esc_html__( 'Enable BusinessPay Gateway Plugin', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Enable BusinessPay gateway plugin options.', 'woocommerce-businesspay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                     => array(
				'type'        => 'text',
				'title'       => esc_html__( 'Title', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Payment method title seen on the checkout page.', 'woocommerce-businesspay' ),
				'default'     => esc_html__( 'BusinessPay', 'woocommerce-businesspay' ),
				'desc_tip'    => true,
			),
			'description'               => array(
				'type'        => 'textarea',
				'title'       => esc_html__( 'Description', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Payment method description seen on the checkout page.', 'woocommerce-businesspay' ),
				'default'     => esc_html__( 'Pay with credit or debit card, billet and bank transfer using BusinessPay.', 'woocommerce-businesspay' ),
				'desc_tip'    => true,
			),
			'gateway_settings'          => array(
				'type'        => 'title',
				'title'       => esc_html__( 'Gateway Settings', 'woocommerce-businesspay' ),
				'description' => ''
			),
			'authentication_api'        => array(
				'type'              => 'text',
				'title'             => esc_html__( 'Authentication API', 'woocommerce-businesspay' ),
				'description'       => esc_html__( 'Use your BusinessPay Authentication API', 'woocommerce-businesspay' ),
				'default'           => 'san.martins',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
			),
			'authentication_key'        => array(
				'type'              => 'text',
				'title'             => esc_html__( 'Authentication Key', 'woocommerce-businesspay' ),
				'description'       => esc_html__( 'Use your BusinessPay Authentication Key', 'woocommerce-businesspay' ),
				'default'           => '8ECB839697AFC52888B0',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
			),
			'credit_card'               => array(
				'type'        => 'checkbox',
				'title'       => esc_html__( 'Credit Card', 'woocommerce-businesspay' ),
				'label'       => esc_html__( 'Enable credit card payments with BusinessPay', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Enable credit card payment with BusinessPay on checkout page', 'woocommerce-businesspay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'credit_card_settings'      => array(
				'title'       => esc_html__( 'Credit Card Settings', 'woocommerce-businesspay' ),
				'type'        => 'title',
				'description' => '',
			),
			'name_in_invoice'           => array(
				'type'              => 'text',
				'title'             => esc_html__( 'Name in Invoice', 'woocommerce-businesspay' ),
				'description'       => esc_html__( 'Name in Invoice to identify Shop. Its important to avoid gateway chargebacks.', 'woocommerce-businesspay' ),
				'default'           => 'BusinessPay',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
			),
			'invoice_prefix'            => array(
				'type'              => 'text',
				'title'             => esc_html__( 'Invoice Prefix', 'woocommerce-businesspay' ),
				'description'       => esc_html__( 'Shop prefix to identify invoice shop origin', 'woocommerce-businesspay' ),
				'default'           => 'BP-',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
			),
			'installments'              => array(
				'title'   => esc_html__( 'Installments', 'woocommerce-businesspay' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable Installments', 'woocommerce-businesspay' ),
				'default' => 'no'
			),
			'installments_settings'     => array(
				'title'       => esc_html__( 'Installments Settings', 'woocommerce-businesspay' ),
				'type'        => 'title',
				'description' => '',
			),
			'installments_minimum'      => array(
				'title'       => esc_html__( 'Minimum Installment', 'woocommerce-businesspay' ),
				'type'        => 'select',
				'description' => esc_html__( 'Indicate the minimum installments.', 'woocommerce-businesspay' ),
				'desc_tip'    => true,
				'default'     => '1',
				'options'     => array(
					1  => '1',
					2  => '2',
					3  => '3',
					4  => '4',
					5  => '5',
					6  => '6',
					7  => '7',
					8  => '8',
					9  => '9',
					10 => '10',
					11 => '11',
					12 => '12'
				)
			),
			'installments_maximum'      => array(
				'title'       => esc_html__( 'Maximum Installment', 'woocommerce-businesspay' ),
				'type'        => 'select',
				'description' => esc_html__( 'Indicate the Maximum installments.', 'woocommerce-businesspay' ),
				'desc_tip'    => true,
				'default'     => '12',
				'options'     => array(
					2  => '2',
					3  => '3',
					4  => '4',
					5  => '5',
					6  => '6',
					7  => '7',
					8  => '8',
					9  => '9',
					10 => '10',
					11 => '11',
					12 => '12'
				)
			),
			'installment_minimum_value' => array(
				'type'              => 'text',
				'title'             => esc_html__( 'Minimum Value', 'woocommerce-businesspay' ),
				'description'       => esc_html__( 'Minimum value by installment', 'woocommerce-businesspay' ),
				'default'           => '5',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
			),
			'debit_card'                => array(
				'type'        => 'checkbox',
				'title'       => esc_html__( 'Debit Card', 'woocommerce-businesspay' ),
				'label'       => esc_html__( 'Enable debit card payments with BusinessPay', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Enable debit card payment on checkout page', 'woocommerce-businesspay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'transfer'                  => array(
				'type'        => 'checkbox',
				'title'       => esc_html__( 'Bank Transfer', 'woocommerce-businesspay' ),
				'label'       => esc_html__( 'Enable bank transfer payments with BusinessPay', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Enable bank transfer payment on checkout page', 'woocommerce-businesspay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'billet'                    => array(
				'type'        => 'checkbox',
				'title'       => esc_html__( 'Billet', 'woocommerce-businesspay' ),
				'label'       => esc_html__( 'Enable billet payments with BusinessPay', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Enable billet payment on checkout page', 'woocommerce-businesspay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'billet_settings'           => array(
				'title'       => esc_html__( 'Billet Settings', 'woocommerce-businesspay' ),
				'type'        => 'title',
				'description' => '',
			),
			'billet_number_days'        => array(
				'title'       => esc_html__( 'Number of Days', 'woocommerce-businesspay' ),
				'type'        => 'text',
				'description' => esc_html__( 'Days of expiry of the billet after printed.', 'woocommerce-businesspay' ),
				'desc_tip'    => true,
				'placeholder' => '7',
				'default'     => '7'
			),
			'billet_instruction_1'      => array(
				'type'        => 'text',
				'title'       => esc_html__( 'Instruction Line', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Billet instruction line', 'woocommerce-businesspay' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'tools'                     => array(
				'title'       => esc_html__( 'Tools', 'woocommerce-businesspay' ),
				'type'        => 'title',
				'description' => '',
			),
			'sandbox'                   => array(
				'type'        => 'checkbox',
				'title'       => esc_html__( 'BusinessPay Sandbox', 'woocommerce-businesspay' ),
				'label'       => esc_html__( 'Enable BusinessPay sandbox mode', 'woocommerce-businesspay' ),
				'description' => esc_html__( 'Enable BusinessPay Sandbox', 'woocommerce-businesspay' ),
				'class'       => 'wc-bp-sandbox',
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'debug'                     => array(
				'type'        => 'checkbox',
				'title'       => esc_html__( 'Debug', 'woocommerce-businesspay' ),
				'label'       => esc_html__( 'Enable logging', 'woocommerce-businesspay' ),
				'description' => 'Enable WooCommerce Debug Log',
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'status'                    => array(
				'title' => esc_html__( 'Status', 'woocommerce-businesspay' ),
				'type'  => 'title',
			),
		);
	}

	public function payment_fields() {
		wp_enqueue_script( 'wc-credit-card-form' );
		wc_get_template( 'frontend-template.php', array( 'bpConfig' => $this ), 'woocommerce/businesspay/', WC_BusinessPay::get_templates_path() );
		$this->antifraud_insert_fingerprint();
	}

	public function validate_fields() {
		$error_num    = 0;
		$fields       = $_POST;
		$payment_type = $this->get_gateway_selected_tab( $fields );
		switch ( $payment_type ) {
			case 'card':
				//Number
				$number = str_replace( ' ', '', $this->get_gateway_payment_card_info_number( $fields ) );
				if ( strlen( $number ) < 14 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'Card number must be at least 14 characters.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( ! is_numeric( $number ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Card Number field must be numeric.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( ! $this->is_card_luhn( $number ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'Invalid card number.', 'woocommerce-businesspay' ), 'error' );
				}

				//Name
				$name = trim( $this->get_gateway_payment_card_info_holder_name( $fields ) );
				if ( strlen( $name ) < 6 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Name field can not be less than 6 characters.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( strlen( $name ) > 25 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Name field can not be greater than 25 characters.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( 1 === preg_match( '~[0-9]~', $name ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Name field can not have numbers.', 'woocommerce-businesspay' ), 'error' );
				}

				//Expiry
				$temp_date       = explode( '/', $this->get_gateway_payment_card_info_expiry( $fields ) );
				$bp_expiry_month = trim( $temp_date[0] );
				$bp_expire_year  = trim( $temp_date[1] );

				if ( strlen( $bp_expiry_month ) < 2 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Month of Expiration field must have 2 digits.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( ! is_numeric( $bp_expiry_month ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Month of Expiration field must be numeric.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( ! is_numeric( $bp_expire_year ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Year of Expiration field must be numeric.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( strlen( $bp_expire_year ) > 4 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Year of Expiration field must have 4 digits.', 'woocommerce-businesspay' ), 'error' );
				}

				//CVC
				$bp_cvc = $this->get_gateway_payment_card_info_cvv( $fields );
				if ( strlen( $bp_cvc ) < 3 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The CVC field must have 3 or 4 digits.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( ! is_numeric( $bp_cvc ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The CVC field must be numeric.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( strlen( $bp_cvc ) > 4 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The CVC field must have 3 or 4 digits.', 'woocommerce-businesspay' ), 'error' );
				}

				//Installments
				$bp_installments = $this->get_gateway_payment_card_installments( $fields );
				if ( $bp_installments == '0' ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'Please select a installment option.', 'woocommerce-businesspay' ), 'error' );
				}

				//Document
				$bp_doc = str_replace( array(
					'.',
					'-',
					'/'
				), '', $this->get_gateway_payment_card_document( $fields ) );
				if ( strlen( $bp_doc ) != 11 && strlen( $bp_doc ) != 14 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Document field must have 11 digits for CPF or 14 digits for CNPJ.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( ! is_numeric( $bp_doc ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Document field must be numeric.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( strlen( $bp_doc ) == 11 && ! $this->is_cpf( $bp_doc ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Document field as CPF is invalid.', 'woocommerce-businesspay' ), 'error' );
				} elseif ( strlen( $bp_doc ) == 14 && ! $this->is_cnpj( $bp_doc ) ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Document field as CNPJ is invalid.', 'woocommerce-businesspay' ), 'error' );
				}

				break;
			case 'transfer':
				$bp_transf = $this->get_gateway_payment_transfer_provider( $fields );
				if ( $bp_transf == '' ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'Please select a origin transfer bank option.', 'woocommerce-businesspay' ), 'error' );
				}

				$bp_district = $this->get_gateway_billing_neighborhood( $fields );
				if ( strlen( $bp_district ) < 3 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Neighborhood field need to be filled.', 'woocommerce-businesspay' ), 'error' );
				}
				break;
			case 'billet':
				$bp_billet = $this->get_gateway_payment_billet_provider( $fields );
				if ( $bp_billet == '' ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'Please select a origin billet bank option.', 'woocommerce-businesspay' ), 'error' );
				}

				$bp_district = $this->get_gateway_billing_neighborhood( $fields );
				if ( strlen( $bp_district ) < 3 ) {
					++ $error_num;
					wc_add_notice( esc_html__( 'The Neighborhood field need to be filled.', 'woocommerce-businesspay' ), 'error' );
				}
				break;
			default:
				break;
		}

		return $error_num == 0;
	}

	public function process_payment( $order_id ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		$fields = $_POST;
		if ( $this->validate_fields() ) {
			try {
				$order  = new WC_Order( $order_id );
				$method = $this->get_gateway_selected_method( $fields );
				if ( $method == 'debit' ) {
					$order->set_payment_method_title( esc_html__( 'BusinessPay | Debit Card', 'woocommerce-businesspay' ) );
				} elseif ( $method == 'credit' ) {
					$order->set_payment_method_title( esc_html__( 'BusinessPay | Credit Card', 'woocommerce-businesspay' ) );
				} elseif ( $method == 'transfer' ) {
					$order->set_payment_method_title( esc_html__( 'BusinessPay | Bank Transfer', 'woocommerce-businesspay' ) );
				} elseif ( $method == 'billet' ) {
					$order->set_payment_method_title( esc_html__( 'BusinessPay | Billet', 'woocommerce-businesspay' ) );
				}

				$response = $this->gateway_do_authorization( $order, $method );
				if ( $response['result'] == 'success' ) {
					WC()->cart->empty_cart();
					$result = $response;
				} else {
					$this->update_order_status( $order, 'failed', $response['messages'] );
					wc_add_notice( $response['messages'], 'error' );
				}
			} catch ( Exception $e ) {
				$this->logger( 'Process Payment Error', $e->getMessage() );
			}
		}

		return $result;
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$result = true;
		$order  = wc_get_order( $order_id );
		if ( $amount == $this->get_total_order( $order ) ) {
			if ( ! empty( $reason ) ) {
				$this->gateway_maybe_do_refund( $order, $reason );
				$this->antifraud_maybe_do_refund( $order, $reason );
			} else {
				wc_create_order_note( $order_id, __( 'Failed to refund order. The reason field must be filled.', 'woocommerce-businesspay' ) );
			}
		} else {
			wc_create_order_note( $order_id, __( 'Failed to refund order. The Amount must be identical to order total.', 'woocommerce-businesspay' ) );
		}

		return $result;
	}

	//GATEWAY CALLS

	private function gateway_do_authorization( $order, $method ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		$fields = $_POST;
		try {
			$body = '';
			if ( $method == 'debit' || $method == 'credit' ) {
				$body = array(
					'referenceId' => $this->get_gateway_order_reference_id( $order ),
					'amount'      => $this->get_gateway_order_amount( $order ),
					'description' => $this->get_gateway_order_description(),
					'postBackUrl' => $this->get_gateway_order_postback_url(),
					'customer'    => array(
						'name'     => $this->get_gateway_customer_name( $fields ),
						'document' => $this->get_gateway_customer_document( $fields ),
					),
					'payment'     => array(
						'card' => array(
							'type'           => $this->get_gateway_payment_type( $fields ),
							'capture'        => $this->get_gateway_payment_card_capture(),
							'installments'   => $this->get_gateway_payment_card_installments( $fields ),
							'interestType'   => $this->get_gateway_payment_card_interest_type(),
							'authenticate'   => $this->get_gateway_payment_card_authenticate(),
							'saveCard'       => $this->get_gateway_payment_card_save(),
							'recurrent'      => $this->get_gateway_payment_card_recurrent(),
							'softDescriptor' => $this->get_gateway_payment_card_soft_descriptor(),
							'cardInfo'       => array(
								'number'          => $this->get_gateway_payment_card_info_number( $fields ),
								'expirationMonth' => $this->get_gateway_payment_card_info_expiry_month( $fields ),
								'expirationYear'  => $this->get_gateway_payment_card_info_expiry_year( $fields ),
								'cvv'             => $this->get_gateway_payment_card_info_cvv( $fields ),
								'brand'           => $this->get_gateway_payment_card_info_brand( $fields ),
								'holderName'      => $this->get_gateway_payment_card_info_holder_name( $fields ),
							)
						)
					)
				);
			} elseif ( $method == 'transfer' ) {
				$body = array(
					'referenceId' => $this->get_gateway_order_reference_id( $order ),
					'amount'      => $this->get_gateway_order_amount( $order ),
					'description' => $this->get_gateway_order_description(),
					'postBackUrl' => $this->get_gateway_order_postback_url(),
					'customer'    => array(
						'name'     => $this->get_gateway_customer_name( $fields ),
						'document' => $this->get_gateway_customer_document( $fields ),
						'email'    => $this->get_gateway_customer_email( $fields ),
						'address'  => array(
							'address'    => $this->get_gateway_customer_address( $fields ),
							'number'     => $this->get_gateway_customer_address_number( $fields ),
							'complement' => $this->get_gateway_customer_address_complement( $fields ),
							'district'   => $this->get_gateway_customer_address_district( $fields ),
							'zipcode'    => $this->get_gateway_customer_address_zipcode( $fields ),
							'city'       => $this->get_gateway_customer_address_city( $fields ),
							'state'      => $this->get_gateway_customer_address_state( $fields )
						)
					),
					'payment'     => array(
						'electronicTransfer' => array(
							'provider' => $this->get_gateway_payment_transfer_provider( $fields )
						)
					)
				);
			} elseif ( $method == 'billet' ) {
				$body = array(
					'referenceId' => $this->get_gateway_order_reference_id( $order ),
					'amount'      => $this->get_gateway_order_amount( $order ),
					'description' => $this->get_gateway_order_description(),
					'postBackUrl' => $this->get_gateway_order_postback_url(),
					'customer'    => array(
						'name'     => $this->get_gateway_customer_name( $fields ),
						'document' => $this->get_gateway_customer_document( $fields ),
						'email'    => $this->get_gateway_customer_email( $fields ),
						'address'  => array(
							'address'    => $this->get_gateway_customer_address( $fields ),
							'number'     => $this->get_gateway_customer_address_number( $fields ),
							'complement' => $this->get_gateway_customer_address_complement( $fields ),
							'district'   => $this->get_gateway_customer_address_district( $fields ),
							'zipcode'    => $this->get_gateway_customer_address_zipcode( $fields ),
							'city'       => $this->get_gateway_customer_address_city( $fields ),
							'state'      => $this->get_gateway_customer_address_state( $fields )
						)
					),
					'payment'     => array(
						'bankSlip' => array(
							'expirationDate' => $this->get_gateway_payment_billet_expiration_date(),
							'instructions'   => $this->get_gateway_payment_billet_instruction(),
							'guarantor'      => $this->get_gateway_payment_billet_guarantor(),
							'provider'       => $this->get_gateway_payment_billet_provider( $fields )
						)
					)
				);
			}

			$response = $this->gateway_api_request( 'POST', 'transactions', $body );

			if ( ! isset( $response->result ) ) {
				$this->gateway_insert_order_metadata( $order, $response );
				$res_status = $this->gateway_insert_order_status( $order, $response );

				if ( $res_status['result'] == 'success' ) {
					$this->antifraud_maybe_do_analysis( $order, $response );
				}
				$result = $res_status;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Authorization Error', $e->getMessage() );
		}

		return $result;
	}

	private function gateway_do_capture( $order ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		try {
			$oid             = $this->get_order_id( $order );
			$amount_captured = get_post_meta( $oid, '_businesspay-card-captured-amount', true );
			if ( empty( $amount_captured ) ) {
				$transaction_id = get_post_meta( $oid, '_businesspay-transaction-id', true );
				$body           = array( 'transactionId' => $transaction_id );
				$endpoint       = 'transactions/' . $transaction_id . '/capture';
				$response       = $this->gateway_api_request( 'PUT', $endpoint, $body );
				if ( ! isset( $response->result ) ) {
					$this->gateway_insert_order_metadata( $order, $response );
					$result = $this->gateway_insert_order_status( $order, $response );
				}
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway API Capture Error', $e->getMessage() );
		}

		return $result;
	}

	private function gateway_api_request( $method, $endpoint, $body ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		try {
			if ( ! empty( $body ) ) {
				$payload = array(
					'method'    => $method,
					'sslverify' => true,
					'timeout'   => 60,
					'headers'   => array(
						'Content-Type'      => 'application/json',
						'authenticationApi' => $this->authentication_api,
						'authenticationKey' => $this->authentication_key
					),
					'body'      => json_encode( $body )
				);

				$url = ( $this->sandbox == 'yes' ) ? $this->gateway_demo_url . '/' . $this->gateway_demo_version . '/' . $endpoint : $this->gateway_live_url . '/' . $this->gateway_live_version . '/' . $endpoint;

				$this->logger( 'API Gateway Request | BODY', $body );
				$response = wp_safe_remote_post( $url, $payload );
				if ( ! is_wp_error( $response ) ) {
					$response_json = json_decode( wp_remote_retrieve_body( $response ) );
					$this->logger( 'API Gateway Response | JSON', $response_json );
					if ( ! empty( $response_json ) ) {
						$result = $response_json;
					} else {
						wc_add_notice( esc_html__( 'The credentials for this virtual store are not valid on the BusinessPay server.', 'woocommerce-businesspay' ), 'error' );
					}
				} else {
					wc_add_notice( esc_html__( 'Could not receive a valid server response. Please try again.', 'woocommerce-businesspay' ), 'error' );
				}
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Api Request Error', $e->getMessage() );
		}

		return $result;
	}

	private function gateway_insert_order_metadata( $order, $response ) {
		try {
			if ( ! strlen( $response->error->message ) > 0 ) {
				$order_id = $this->get_order_id( $order );
				$this->gateway_remove_order_metadata( $order_id );

				if ( isset( $response->payment->card ) ) {
					if ( $response->payment->card->type == '1' ) {
						update_post_meta( $order_id, '_businesspay-transaction-type', 'credit-card' );
					} elseif ( $response->payment->card->type == '2' ) {
						update_post_meta( $order_id, '_businesspay-transaction-type', 'debit-card' );
					}
					update_post_meta( $order_id, '_businesspay-transaction-id', $response->transactionId );
					update_post_meta( $order_id, '_businesspay-transaction-date', $response->dtTransaction );
					update_post_meta( $order_id, '_businesspay-transaction-status', $response->status );
					update_post_meta( $order_id, '_businesspay-transaction-status-message', $this->gateway_woocommerce_message( $order, $response ) );
					update_post_meta( $order_id, '_businesspay-card-type', $response->payment->card->type );
					update_post_meta( $order_id, '_businesspay-card-brand', $response->payment->card->cardInfo->brand );
					update_post_meta( $order_id, '_businesspay-card-interest-type', $response->payment->card->interestType );
					update_post_meta( $order_id, '_businesspay-card-installments', $response->payment->card->installments );
					update_post_meta( $order_id, '_businesspay-card-capture', $response->payment->card->capture );
					update_post_meta( $order_id, '_businesspay-card-captured-amount', $response->payment->card->capturedAmount );
					update_post_meta( $order_id, '_businesspay-card-pre-authorization', $response->payment->card->preAuthorization );
					update_post_meta( $order_id, '_businesspay-card-authenticate', $response->payment->card->authenticate );
					update_post_meta( $order_id, '_businesspay-card-recurrent', $response->payment->card->recurrent );
					update_post_meta( $order_id, '_businesspay-card-provider', $response->payment->card->provider );
					update_post_meta( $order_id, '_businesspay-card-provider-version', $response->payment->card->providerVersion );
					update_post_meta( $order_id, '_businesspay-card-authentication-eci', $response->payment->card->authenticationECI );
					update_post_meta( $order_id, '_businesspay-card-authorization-code', $response->payment->card->codAuthorization );
					update_post_meta( $order_id, '_businesspay-card-provider-reference', $response->payment->card->providerReference );
					update_post_meta( $order_id, '_businesspay-card-provider-code', $response->payment->card->providerCode );
					update_post_meta( $order_id, '_businesspay-card-provider-message', $response->payment->card->providerMessage );
					update_post_meta( $order_id, '_businesspay-card-cardinfo-number', $response->payment->card->cardInfo->number );
					update_post_meta( $order_id, '_businesspay-card-cardinfo-expiration-month', $response->payment->card->cardInfo->expirationMonth );
					update_post_meta( $order_id, '_businesspay-card-cardinfo-expiration-year', $response->payment->card->cardInfo->expirationYear );
					update_post_meta( $order_id, '_businesspay-customer-name', $response->customer->name );
					update_post_meta( $order_id, '_businesspay-customer-document', $response->customer->document );
				} elseif ( isset( $response->payment->electronicTransfer ) ) {
					update_post_meta( $order_id, '_businesspay-transaction-type', 'bank-transfer' );
					update_post_meta( $order_id, '_businesspay-transaction-id', $response->transactionId );
					update_post_meta( $order_id, '_businesspay-transaction-date', $response->dtTransaction );
					update_post_meta( $order_id, '_businesspay-transaction-status', $response->status );
					update_post_meta( $order_id, '_businesspay-transaction-status-message', $this->gateway_woocommerce_message( $order, $response ) );
					update_post_meta( $order_id, '_businesspay-customer-name', $response->customer->name );
					update_post_meta( $order_id, '_businesspay-customer-document', $response->customer->document );
					update_post_meta( $order_id, '_businesspay-electronic-transfer-provider', $response->payment->electronicTransfer->provider );
					update_post_meta( $order_id, '_businesspay-electronic-transfer-provider-reference', $response->payment->electronicTransfer->providerReference );
					update_post_meta( $order_id, '_businesspay-electronic-transfer-payment-date', $response->payment->electronicTransfer->paymentDate );
					update_post_meta( $order_id, '_businesspay-electronic-transfer-payment-amount', $response->payment->electronicTransfer->paymentAmount );
				} elseif ( isset( $response->payment->bankSlip ) ) {
					update_post_meta( $order_id, '_businesspay-transaction-type', 'billet' );
					update_post_meta( $order_id, '_businesspay-transaction-id', $response->transactionId );
					update_post_meta( $order_id, '_businesspay-transaction-date', $response->dtTransaction );
					update_post_meta( $order_id, '_businesspay-transaction-status', $response->status );
					update_post_meta( $order_id, '_businesspay-transaction-status-message', $this->gateway_woocommerce_message( $order, $response ) );
					update_post_meta( $order_id, '_businesspay-customer-name', $response->customer->name );
					update_post_meta( $order_id, '_businesspay-customer-document', $response->customer->document );
					update_post_meta( $order_id, '_businesspay-bankslip-provider-reference', $response->payment->bankSlip->providerReference );
					update_post_meta( $order_id, '_businesspay-bankslip-provider-code', $response->payment->bankSlip->providerCode );
					update_post_meta( $order_id, '_businesspay-bankslip-provider-message', $response->payment->bankSlip->providerMessage );
					update_post_meta( $order_id, '_businesspay-bankslip-emission-date', $response->payment->bankSlip->emissionDate );
					update_post_meta( $order_id, '_businesspay-bankslip-expiration-date', $response->payment->bankSlip->expirationDate );
					update_post_meta( $order_id, '_businesspay-bankslip-instructions', $response->payment->bankSlip->instructions );
					update_post_meta( $order_id, '_businesspay-bankslip-guarantor', $response->payment->bankSlip->guarantor );
					update_post_meta( $order_id, '_businesspay-bankslip-provider', $response->payment->bankSlip->provider );
					update_post_meta( $order_id, '_businesspay-bankslip-payment-date', $response->payment->bankSlip->paymentDate );
					update_post_meta( $order_id, '_businesspay-bankslip-payment-amount', $response->payment->bankSlip->paymentAmount );
				}
				$this->auth_transaction( $order );
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Insert Order Metadata Error', $e->getMessage() );
		}
	}

	private function gateway_insert_order_status( $order, $response ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		try {
			if ( ! isset( $response->error ) ) {
				$res_status  = $this->gateway_woocommerce_status( $order, $response );
				$res_result  = $this->gateway_woocommerce_result( $response );
				$res_message = $this->gateway_woocommerce_message( $order, $response );

				if ( $res_result == 'success' ) {
					$res_note = esc_html__( 'BusinessPay Status: ', 'woocommerce-businesspay' ) . $res_message;
					$order->set_transaction_id( $response->transactionId );
					$this->update_order_status( $order, $res_status, $res_note );

					$result = array(
						'result'   => 'success',
						'redirect' => $this->get_response_url( $order, $response ),
					);
				} else {
					$result = array(
						'result'   => $res_result,
						'messages' => $res_message
					);
					$this->update_order_status( $order, 'failed', $res_message );
				}
			} else {
				$this->update_order_status( $order, 'failed', $response->error->message );
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Insert Order Status Error', $e->getMessage() );
		}

		return $result;
	}

	private function gateway_remove_order_metadata( $order_id ) {
		$tid = get_post_meta( $order_id, '_businesspay-transaction-type', true );
		if ( strlen( $tid ) > 0 ) {
			delete_post_meta( $order_id, '_businesspay-transaction-id' );
			delete_post_meta( $order_id, '_businesspay-transaction-type' );
			delete_post_meta( $order_id, '_businesspay-transaction-date' );
			delete_post_meta( $order_id, '_businesspay-transaction-status' );
			delete_post_meta( $order_id, '_businesspay-transaction-status-message' );
			delete_post_meta( $order_id, '_businesspay-card-type' );
			delete_post_meta( $order_id, '_businesspay-card-brand' );
			delete_post_meta( $order_id, '_businesspay-card-interest-type' );
			delete_post_meta( $order_id, '_businesspay-card-installments' );
			delete_post_meta( $order_id, '_businesspay-card-capture' );
			delete_post_meta( $order_id, '_businesspay-card-captured-amount' );
			delete_post_meta( $order_id, '_businesspay-card-pre-authorization' );
			delete_post_meta( $order_id, '_businesspay-card-authenticate' );
			delete_post_meta( $order_id, '_businesspay-card-recurrent' );
			delete_post_meta( $order_id, '_businesspay-card-provider' );
			delete_post_meta( $order_id, '_businesspay-card-provider-version' );
			delete_post_meta( $order_id, '_businesspay-card-authentication-eci' );
			delete_post_meta( $order_id, '_businesspay-card-authorization-code' );
			delete_post_meta( $order_id, '_businesspay-card-provider-reference' );
			delete_post_meta( $order_id, '_businesspay-card-provider-code' );
			delete_post_meta( $order_id, '_businesspay-card-provider-message' );
			delete_post_meta( $order_id, '_businesspay-card-cardinfo-number' );
			delete_post_meta( $order_id, '_businesspay-card-cardinfo-expiration-month' );
			delete_post_meta( $order_id, '_businesspay-card-cardinfo-expiration-year' );
			delete_post_meta( $order_id, '_businesspay-customer-name' );
			delete_post_meta( $order_id, '_businesspay-customer-document' );
			delete_post_meta( $order_id, '_businesspay-electronic-transfer-provider' );
			delete_post_meta( $order_id, '_businesspay-electronic-transfer-provider-reference' );
			delete_post_meta( $order_id, '_businesspay-electronic-transfer-payment-date' );
			delete_post_meta( $order_id, '_businesspay-electronic-transfer-payment-amount' );
			delete_post_meta( $order_id, '_businesspay-bankslip-provider-reference' );
			delete_post_meta( $order_id, '_businesspay-bankslip-provider-code' );
			delete_post_meta( $order_id, '_businesspay-bankslip-provider-message' );
			delete_post_meta( $order_id, '_businesspay-bankslip-emission-date' );
			delete_post_meta( $order_id, '_businesspay-bankslip-expiration-date' );
			delete_post_meta( $order_id, '_businesspay-bankslip-instructions' );
			delete_post_meta( $order_id, '_businesspay-bankslip-guarantor' );
			delete_post_meta( $order_id, '_businesspay-bankslip-provider' );
			delete_post_meta( $order_id, '_businesspay-bankslip-payment-date' );
			delete_post_meta( $order_id, '_businesspay-bankslip-payment-amount' );
		}
	}

	//GATEWAY STATUS

	private function gateway_woocommerce_status( $order, $response ) {
		try {
			switch ( $response->status ) {
				case 0: //TRANSACAO INICIADA
					$result = 'on-hold';
					break;
				case 1: //AGUARDANDO PAGAMENTO
					$result = 'on-hold';
					break;
				case 2: //EFETIVADA
					$result = 'failed';
					break;
				case 3: //EM ANALISE
					$result = 'on-hold';
					break;
				case 4: //EXPIRADA
					$result = 'cancelled';
					break;
				case 5: //AUTORIZADA
					$result = 'on-hold';
					break;
				case 6: //CONFIRMADA
					$result = 'processing';
					break;
				case 7: //NEGADA
					$result = 'failed';
					break;
				case 8: //CANCELAMENTO EM ANDAMENTO
					$result = 'on-hold';
					break;
				case 9: //CANCELADA
					$result = 'refunded';
					break;
				case 10: //PENDENTE DE CONFIRMAÇÃO
					$result = 'on-hold';
					break;
				case 11: //FALHA NA COMUNICAÇÃO COM FORNECEDOR
					$result = 'failed';
					break;
				default:
					$result = 'failed';
					break;
			}

			if ( $result == 'processing' && $this->get_gateway_payment_captured( $response ) ) {
				//Verify payment amount
				$paid_wrong = false;
				$paid_total = $this->get_gateway_payment_amount( $response );
				if ( ! empty( $paid_total ) ) {
					$order_total = str_replace( array( ',', '.' ), '', $this->get_total_order( $order ) );
					$balance     = $order_total - $paid_total;
					if ( $balance > 0 ) {
						$paid_wrong = true;
					}
				} else {
					$paid_wrong = true;
				}

				if ( $paid_wrong ) {
					$result = 'on-hold';
				}
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Woocommerce Status Error', $e->getMessage() );
		}

		return $result;
	}

	private function gateway_woocommerce_result( $response ) {
		$result = 'failure';
		try {
			switch ( $response->status ) {
				case 0: //TRANSACAO INICIADA
					$result = 'success';
					break;
				case 1: //AGUARDANDO PAGAMENTO
					$result = 'success';
					break;
				case 2: //EFETIVADA
					$result = 'failure';
					break;
				case 3: //EM ANALISE
					$result = 'success';
					break;
				case 4: //EXPIRADA
					$result = 'failure';
					break;
				case 5: //AUTORIZADA
					$result = 'success';
					break;
				case 6: //CONFIRMADA
					$result = 'success';
					break;
				case 7: //NEGADA
					$result = 'failure';
					break;
				case 8: //CANCELAMENTO EM ANDAMENTO
					$result = 'failure';
					break;
				case 9: //CANCELADA
					$result = 'success';
					break;
				case 10: //PENDENTE DE CONFIRMAÇÃO
					$result = 'success';
					break;
				case 11: //FALHA NA COMUNICAÇÃO COM FORNECEDOR
					$result = 'failure';
					break;
				default:
					$result = 'failure';
					break;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Woocommerce Result Error', $e->getMessage() );
		}

		return $result;
	}

	private function gateway_woocommerce_message( $order, $response ) {
		$result = 'failure';
		try {
			switch ( $response->status ) {
				case 0: //TRANSACAO INICIADA
					$result = esc_html__( 'Transaction started but not confirmed.', 'woocommerce-businesspay' );
					break;
				case 1: //AGUARDANDO PAGAMENTO
					$result = esc_html__( 'Awating payment.', 'woocommerce-businesspay' );
					break;
				case 2: //EFETIVADA
					$result = esc_html__( 'Transaction effectivated.', 'woocommerce-businesspay' );
					break;
				case 3: //EM ANALISE
					$result = esc_html__( 'Transaction under analysis.', 'woocommerce-businesspay' );
					break;
				case 4: //EXPIRADA
					$result = esc_html__( 'Transaction expired.', 'woocommerce-businesspay' );
					break;
				case 5: //AUTORIZADA
					$result = esc_html__( 'Transaction authorized.', 'woocommerce-businesspay' );
					break;
				case 6: //CONFIRMADA
					$result = esc_html__( 'Transaction confirmed.', 'woocommerce-businesspay' );
					break;
				case 7: //NEGADA
					$result = esc_html__( 'Transaction denied.', 'woocommerce-businesspay' );
					break;
				case 8: //CANCELAMENTO EM ANDAMENTO
					$result = esc_html__( 'Cancellation in progress.', 'woocommerce-businesspay' );
					break;
				case 9: //CANCELADA
					$result = esc_html__( 'Transaction Canceled.', 'woocommerce-businesspay' );
					break;
				case 10: //PENDENTE DE CONFIRMAÇÃO
					$result = esc_html__( 'Transaction pending of confirmation.', 'woocommerce-businesspay' );
					break;
				case 11: //FALHA NA COMUNICAÇÃO COM FORNECEDOR
					$result = esc_html__( 'Failure to communicate with the provider.', 'woocommerce-businesspay' );
					break;
				default:
					$result = esc_html__( 'No valid server response.', 'woocommerce-businesspay' );
					break;
			}

			if ( $response->status == 6 && $this->get_gateway_payment_captured( $response ) ) {
				//Verify payment amount
				$paid_wrong = false;
				$paid_total = $this->get_gateway_payment_amount( $response );
				if ( ! empty( $paid_total ) ) {
					$order_total = str_replace( array( ',', '.' ), '', $this->get_total_order( $order ) );
					$balance     = $order_total - $paid_total;
					if ( $balance > 0 ) {
						$paid_wrong = true;
					}
				} else {
					$paid_wrong = true;
				}

				if ( $paid_wrong ) {
					$result = esc_html__( 'payment made less than the value of the order.', 'woocommerce-businesspay' );
				}
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Woocommerce Message Error', $e->getMessage() );
		}

		return $result;
	}

	//GATEWAY REFUNDS

	private function gateway_maybe_do_refund( $order, $reason ) {
		$status           = get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-status', true );
		$transaction_id   = get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-id', true );
		$transaction_type = get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-type', true );
		$transaction_card = $transaction_type == 'credit-card' || $transaction_type == 'debit-card';

		if ( ( $status == 5 || $status == 6 ) && ! empty( $transaction_id ) && $transaction_card ) {
			$this->gateway_do_refund( $order, $reason );
		} else {
			if ( $transaction_type == 'billet' ) {
				wc_create_order_note( $this->get_order_id( $order ), __( 'Billet transactions cannot be refunded.', 'woocommerce-businesspary' ) );
			} elseif ( $transaction_type == 'bank-transfer' ) {
				wc_create_order_note( $this->get_order_id( $order ), __( 'Bank transfer transactions cannot be refunded.', 'woocommerce-businesspary' ) );
			}
		}
	}

	private function gateway_do_refund( $order ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		try {
			$order_id       = $this->get_order_id( $order );
			$transaction_id = get_post_meta( $order_id, '_businesspay-transaction-id', true );
			$body           = array( 'transactionId' => $transaction_id );
			$endpoint       = 'transactions/' . $transaction_id . '/void';
			$response       = $this->gateway_api_request( 'PUT', $endpoint, $body );
			$this->logger( 'Gateway API Do Refund | Response', $response );
			if ( ! isset( $response->result ) ) {
				$this->gateway_insert_order_metadata( $order, $response );
				$result = $this->gateway_insert_order_status( $order, $response );
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway API Do Refund Error', $e->getMessage() );
		}

		return $result;
	}


	//ANTIFRAUD CALLS

	private function antifraud_maybe_renew_token() {
		$result = false;
		try {
			$today      = current_time( 'mysql' );
			$token      = get_option( 'businesspay_antifraud_auth_token' );
			$expiration = get_option( 'businesspay_antifraud_auth_expiration' );
			$this->logger( 'Antifraud Token | today', $today );
			$this->logger( 'Antifraud Token | token', $token );
			$this->logger( 'Antifraud Token | expiration', $expiration );

			$renew = ( $token == '' ) || ( $expiration == '' ) || ( strtotime( $expiration ) < strtotime( $today ) );

			if ( $renew ) {
				$retry_num = 0;
				while ( $retry_num < 3 ) {
					$retry_num ++;
					$body = array(
						'Name'     => ( $this->debug == 'yes' ) ? $this->antifraud_login_demo : $this->antifraud_login_live,
						'Password' => ( $this->debug == 'yes' ) ? $this->antifraud_password_demo : $this->antifraud_password_live
					);

					$payload = array(
						'method'    => 'POST',
						'sslverify' => true,
						'timeout'   => 60,
						'headers'   => array(
							'cache-control' => 'no-cache',
							'Content-Type'  => 'application/json',
						),
						'body'      => json_encode( $body )
					);

					$url = ( $this->sandbox == 'yes' ) ? $this->antifraud_demo_url . '/api/' . $this->antifraud_demo_version . '/authenticate' : $this->antifraud_live_url . '/' . $this->antifraud_live_version . '/authenticate';

					$this->logger( 'Antifraud Token | $url', $url );
					$this->logger( 'Antifraud Token | PAYLOAD', $payload );
					$response = wp_safe_remote_post( $url, $payload );

					$this->logger( 'Antifraud Token | Response', $response );

					if ( ! is_wp_error( $response ) ) {
						$response = json_decode( wp_remote_retrieve_body( $response ) );
						if ( ! empty( $response ) ) {
							$this->set_option_antifraud_auth_token( $response->Token );
							$this->set_option_antifraud_auth_expiration( $response->ExpirationDate );
							$retry_num = 3;
							$result    = true;
						}
					}
				}
			} else {
				$result = true;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Antifraud Renew Token Error', $e->getMessage() );
		}

		return $result;
	}

	private function antifraud_maybe_do_analysis( $order, $gateway_response ) {
		$status           = $gateway_response->status;
		$oid              = $this->get_order_id( $order );
		$transaction_id   = get_post_meta( $oid, '_businesspay-antifraud-transaction-id', true );
		$transaction_type = get_post_meta( $oid, '_businesspay-transaction-type', true );
		$transaction_card = $transaction_type == 'credit-card' || $transaction_type == 'debit-card';
		$amount_captured  = get_post_meta( $oid, '_businesspay-card-captured-amount', true );

		if ( ( $status == 5 || $status == 6 ) && empty( $transaction_id ) && $transaction_card && empty( $amount_captured ) && $this->enable_antifraud ) {
			$this->antifraud_do_analysis( $order );
		}
	}

	private function antifraud_do_analysis( $order ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		if ( $this->antifraud_maybe_renew_token() ) {
			$retry_num = 0;
			while ( $retry_num < 3 ) {
				$retry_num ++;
				try {
					$af_items_order = $order->get_items();
					$arr_items      = array();

					foreach ( $af_items_order as $af_item_order ) {
						$arr_item = array(
							'code'         => $this->get_antifraud_item_id( $af_item_order ),
							'name'         => $this->get_antifraud_item_name( $af_item_order ),
							'value'        => $this->get_antifraud_item_value( $af_item_order ),
							'amount'       => $this->get_antifraud_item_amount( $af_item_order ),
							'categoryID'   => $this->get_antifraud_item_category_id( $af_item_order ),
							'categoryName' => $this->get_antifraud_item_category_name( $af_item_order ),
						);
						array_push( $arr_items, $arr_item );
					}

					$arr_order = array(
						'code'                 => $this->get_antifraud_order_id( $order ),
						'sessionID'            => $this->get_antifraud_order_fingerprint(),
						'date'                 => $this->get_antifraud_order_date( $order ),
						'email'                => $this->get_antifraud_order_email( $order ),
						'itemValue'            => $this->get_antifraud_order_total_items( $order ),
						'totalValue'           => $this->get_antifraud_order_total_order( $order ),
						'numberOfInstallments' => $this->get_antifraud_order_qtd_installments( $order ),
						'ip'                   => $this->get_antifraud_order_ip( $order ),
						'observation'          => $this->get_antifraud_order_obs( $order ),
						'origin'               => $this->get_antifraud_order_origin(),
						'channelID'            => $this->get_antifraud_order_channel_id(),
						'product'              => $this->get_antifraud_order_product_id(),
						'billing'              => array(
							'clientID'        => $this->get_antifraud_billing_client_id( $order ),
							'type'            => $this->get_antifraud_billing_person_type( $order ),
							'primaryDocument' => $this->get_antifraud_billing_document( $order ),
							'name'            => $this->get_antifraud_billing_name( $order ),
							'email'           => $this->get_antifraud_billing_email( $order ),
							'address'         => array(
								'street'                => $this->get_antifraud_billing_street( $order ),
								'number'                => $this->get_antifraud_billing_street_number( $order ),
								'additionalInformation' => $this->get_antifraud_billing_complement( $order ),
								'county'                => $this->get_antifraud_billing_county( $order ),
								'city'                  => $this->get_antifraud_billing_city( $order ),
								'state'                 => $this->get_antifraud_billing_state( $order ),
								'zipcode'               => $this->get_antifraud_billing_zipcode( $order ),
								'country'               => $this->get_antifraud_billing_country( $order ),
							),
							'phones'          => array(
								array(
									'type'   => $this->get_antifraud_billing_phone_type( $order ),
									'ddi'    => $this->get_antifraud_billing_phone_ddi(),
									'ddd'    => $this->get_antifraud_billing_phone_ddd( $order ),
									'number' => $this->get_antifraud_billing_phone_number( $order ),
								),
							),
						),
						'shipping'             => array(
							'clientID'        => $this->get_antifraud_shipping_client_id( $order ),
							'type'            => $this->get_antifraud_shipping_person_type( $order ),
							'primaryDocument' => $this->get_antifraud_shipping_document( $order ),
							'name'            => $this->get_antifraud_shipping_name( $order ),
							'email'           => $this->get_antifraud_shipping_email( $order ),
							'address'         => array(
								'street'                => $this->get_antifraud_shipping_street( $order ),
								'number'                => $this->get_antifraud_shipping_street_number( $order ),
								'additionalInformation' => $this->get_antifraud_shipping_complement( $order ),
								'county'                => $this->get_antifraud_shipping_county( $order ),
								'city'                  => $this->get_antifraud_shipping_city( $order ),
								'state'                 => $this->get_antifraud_shipping_state( $order ),
								'zipcode'               => $this->get_antifraud_shipping_zipcode( $order ),
								'country'               => $this->get_antifraud_shipping_country( $order ),
							),
							'phones'          => array(
								array(
									'type'   => $this->get_antifraud_shipping_phone_type( $order ),
									'ddi'    => $this->get_antifraud_shipping_phone_ddi(),
									'ddd'    => $this->get_antifraud_shipping_phone_ddd( $order ),
									'number' => $this->get_antifraud_shipping_phone_number( $order ),
								),
							),
							'price'           => $this->get_antifraud_shipping_delivery_price( $order ),
						),
						'payments'             => array(
							array(
								'date'         => $this->get_antifraud_payment_date( $order ),
								'value'        => $this->get_antifraud_payment_value( $order ),
								'type'         => $this->get_antifraud_payment_type( $order ),
								'installments' => $this->get_antifraud_payment_qtd_installments( $order ),
								'card'         => array(
									'bin'          => $this->get_antifraud_payment_card_bin( $order ),
									'end'          => $this->get_antifraud_payment_card_end( $order ),
									'type'         => $this->get_antifraud_payment_card_type( $order ),
									'validityDate' => $this->get_antifraud_payment_card_expiration( $order ),
									'ownerName'    => $this->get_antifraud_payment_card_holder_name( $order ),
									'document'     => $this->get_antifraud_payment_card_legal_document( $order ),
								),
							),
						),
						'items'                => $arr_items
					);

					$response = $this->antifraud_api_request( 'POST', 'orders', $arr_order );
					if ( ! $response->result == 'failure' ) {
						$this->antifraud_insert_order_metadata( $order, $response );
						$response  = $this->antifraud_insert_order_status( $order, $response );
						$retry_num = 3;
						$result    = $response;
					}
				} catch ( Exception $e ) {
					$this->logger( 'Antifraud Authorization Card Error', $e->getMessage() );
				}
			}
		} else {
			$this->logger( 'Antifraud Do Analysis', 'Antifraud Renew Token Error' );
		}

		return $result;
	}

	private function antifraud_api_request( $method, $endpoint, $body ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		try {
			$payload = array(
				'method'    => $method,
				'sslverify' => true,
				'timeout'   => 60,
				'headers'   => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->antifraud_auth_token,
				),
				'body'      => json_encode( $body )
			);

			$url = ( $this->sandbox == 'yes' ) ? $this->antifraud_demo_url . '/api/' . $this->antifraud_demo_version . '/' . $endpoint : $this->antifraud_live_url . '/' . $this->antifraud_live_version . '/' . $endpoint;

			$this->logger( 'API Antifraud Request | URL', $method . ' - ' . $url );
			$this->logger( 'API Antifraud Request | BODY', $body );
			$response = wp_safe_remote_post( $url, $payload );

			if ( ! is_wp_error( $response ) ) {
				$response_json = json_decode( wp_remote_retrieve_body( $response ) );
				$this->logger( 'API Antifraud Response | JSON', $response_json );
				if ( ! empty( $response_json ) ) {
					$result = $response_json;
				} else {
					wc_add_notice( esc_html__( 'The credentials for this virtual store are not valid on the BusinessPay server. Please try again.', 'woocommerce-businesspay' ), 'error' );
				}
			} else {
				wc_add_notice( esc_html__( 'Could not receive a valid server response. Please try again.', 'woocommerce-businesspay' ), 'error' );
			}
		} catch ( Exception $e ) {
			$this->logger( 'Antifraud API Request Error', $e->getMessage() );
		}

		return $result;
	}

	private function antifraud_insert_order_metadata( $order, $response ) {
		try {
			if ( ! isset( $response->Message ) ) {
				$order_id = $this->get_order_id( $order );
				$this->antifraud_remove_order_metadata( $order_id );

				update_post_meta( $order_id, '_businesspay-antifraud-transaction-date', current_time( 'mysql' ) );

				if ( isset( $response->packageID ) ) {
					update_post_meta( $order_id, '_businesspay-antifraud-transaction-id', $response->packageID );
				}

				if ( isset( $response->orders[0]->status ) ) {
					update_post_meta( $order_id, '_businesspay-antifraud-status-code', $response->orders[0]->status );
					update_post_meta( $order_id, '_businesspay-antifraud-message', $this->antifraud_woocommerce_message( $response ) );
				}

				if ( isset( $response->orders[0]->score ) ) {
					update_post_meta( $order_id, '_businesspay-antifraud-score', $response->orders[0]->score );
				}
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Insert Order Metadata Error', $e->getMessage() );
		}
	}

	private function antifraud_insert_order_status( $order, $response ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		try {
			$res_status  = $this->antifraud_woocommerce_status( $response );
			$res_result  = $this->antifraud_woocommerce_result( $response );
			$res_message = $this->antifraud_woocommerce_message( $response );

			if ( $res_result == 'success' ) {
				$res_note = esc_html__( 'Antifraud Status: ', 'woocommerce-businesspay' ) . $res_message;

				if ( ! empty( $res_status ) ) {
					$this->update_order_status( $order, $res_status, $res_note );
				} else {
					wc_create_order_note( $this->get_order_id( $order ), $res_note );
				}


				$analysis = $response->orders[0]->status;
				if ( in_array( $analysis, array( 'APA', 'APM', 'APP' ) ) ) {
					$this->gateway_do_capture( $order );
				}
			} else {
				$result = array(
					'result'   => $res_result,
					'messages' => $res_message
				);
				$this->update_order_status( $order, 'failed', $res_message );
			}
		} catch ( Exception $e ) {
			$this->logger( 'Antifraud Insert Order Status Error', $e->getMessage() );
		}

		return $result;
	}

	private function antifraud_remove_order_metadata( $order_id ) {
		delete_post_meta( $order_id, '_businesspay-antifraud-transaction-date' );
		delete_post_meta( $order_id, '_businesspay-antifraud-transaction-id' );
		delete_post_meta( $order_id, '_businesspay-antifraud-status-code' );
		delete_post_meta( $order_id, '_businesspay-antifraud-score' );
		delete_post_meta( $order_id, '_businesspay-antifraud-message' );
	}

	//ANTIFRAUD STATUS

	private function antifraud_woocommerce_status( $response ) {
		$result = 'failed';
		try {
			switch ( $response->orders[0]->status ) {
				case 'APA': //APROVAÇÃO AUTOMÁTICA
					$result = 'on-hold';
					break;
				case 'AMA': //ANÁLISE MANUAL
					$result = 'on-hold';
					break;
				case 'APM': //APROVAÇÃO MANUAL
					$result = 'on-hold';
					break;
				case 'APP': //APROVAÇÃO POR POLÍTICA
					$result = 'on-hold';
					break;
				case 'CAN': //CANCELADO PELO CLIENTE
					$result = '';
					break;
				case 'FRD': //FRAUDE CONFIRMADA
					$result = 'failed';
					break;
				case 'NVO': //NOVO
					$result = 'on-hold';
					break;
				case 'RPA': //REPROVAÇÃO AUTOMÁTICA
					$result = 'failed';
					break;
				case 'RPM': //REPROVAÇÃO SEM SUSPEITA
					$result = 'failed';
					break;
				case 'RPP': //REPROVAÇÃO POR POLÍTICA
					$result = 'failed';
					break;
				case 'SUS': //SUSPENSÃO MANUAL
					$result = 'failed';
					break;
				default:
					$result = 'failed';
					break;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Antifraud Woocommerce Status Error', $e->getMessage() );
		}

		return $result;
	}

	private function antifraud_woocommerce_result( $response ) {
		$result = 'failure';
		try {
			switch ( $response->orders[0]->status ) {
				case 'APA': //APROVAÇÃO AUTOMÁTICA
					$result = 'success';
					break;
				case 'AMA': //ANÁLISE MANUAL
					$result = 'success';
					break;
				case 'APM': //APROVAÇÃO MANUAL
					$result = 'success';
					break;
				case 'APP': //APROVAÇÃO POR POLÍTICA
					$result = 'success';
					break;
				case 'CAN': //CANCELADO PELO CLIENTE
					$result = 'success';
					break;
				case 'FRD': //FRAUDE CONFIRMADA
					$result = 'success';
					break;
				case 'NVO': //NOVO
					$result = 'success';
					break;
				case 'RPA': //REPROVAÇÃO AUTOMÁTICA
					$result = 'success';
					break;
				case 'RPM': //REPROVAÇÃO SEM SUSPEITA
					$result = 'success';
					break;
				case 'RPP': //REPROVAÇÃO POR POLÍTICA
					$result = 'success';
					break;
				case 'SUS': //SUSPENSÃO MANUAL
					$result = 'success';
					break;
				default:
					$result = 'failure';
					break;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Woocommerce Result Error', $e->getMessage() );
		}

		return $result;
	}

	private function antifraud_woocommerce_message( $response ) {
		$result = 'failure';
		try {
			switch ( $response->orders[0]->status ) {
				case 'APA': //APROVAÇÃO AUTOMÁTICA
					$result = esc_html__( 'Order was automatically approved according to parameters defined in the automatic approval rule.', 'woocommerce-businesspay' );
					break;
				case 'AMA': //ANÁLISE MANUAL
					$result = esc_html__( 'Order is queued for analysis.', 'woocommerce-businesspay' );
					break;
				case 'APM': //APROVAÇÃO MANUAL
					$result = esc_html__( 'Order manually approved by decision-making of an analyst.', 'woocommerce-businesspay' );
					break;
				case 'APP': //APROVAÇÃO POR POLÍTICA
					$result = esc_html__( 'Order approved automatically by client-established policy or antifraud software rule.', 'woocommerce-businesspay' );
					break;
				case 'CAN': //CANCELADO PELO CLIENTE
					$result = esc_html__( 'Canceled by customer or duplicated order.', 'woocommerce-businesspay' );
					break;
				case 'FRD': //FRAUDE CONFIRMADA
					$result = esc_html__( 'Order imputed as fraud confirmed by contact with the card administrator and / or contact with the card holder or CPF of the registry that is unaware of the purchase.', 'woocommerce-businesspay' );
					break;
				case 'NVO': //NOVO
					$result = esc_html__( 'Order imported and not classified Score by the analyzer yet.', 'woocommerce-businesspay' );
					break;
				case 'RPA': //REPROVAÇÃO AUTOMÁTICA
					$result = esc_html__( 'Order Automatically Disapproved by some type of business rule that needs to be applied.', 'woocommerce-businesspay' );
					break;
				case 'RPM': //REPROVAÇÃO SEM SUSPEITA
					$result = esc_html__( 'Order disapproved without suspicion by lack of contact with the client within the agreed period and / or social security number of restrictive policies (Irregular, SUS or Canceled).', 'woocommerce-businesspay' );
					break;
				case 'RPP': //REPROVAÇÃO POR POLÍTICA
					$result = esc_html__( 'Order disapproved automatically by policy established by customer or antifraud software.', 'woocommerce-businesspay' );
					break;
				case 'SUS': //SUSPENSÃO MANUAL
					$result = esc_html__( 'Order automatically approved by policy established by the customer or antifraud software.', 'woocommerce-businesspay' );
					break;
				default:
					$result = 'failure';
					break;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway Woocommerce Message Error', $e->getMessage() );
		}

		return $result;
	}

	//ANTIFRAUD REFUNDS

	private function antifraud_maybe_do_refund( $order, $reason ) {
		$status           = get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-status', true );
		$transaction_id   = get_post_meta( $this->get_order_id( $order ), '_businesspay-antifraud-transaction-id', true );
		$transaction_type = get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-type', true );
		$transaction_card = $transaction_type == 'credit-card' || $transaction_type == 'debit-card';

		if ( $status == 9 && ! empty( $transaction_id ) && $transaction_card && $this->enable_antifraud ) {
			$this->antifraud_do_refund( $order, $reason );
		}
	}

	private function antifraud_do_refund( $order, $reason ) {
		$result    = array( 'result' => 'failure', 'messages' => '' );
		$retry_num = 0;
		while ( $retry_num < 3 ) {
			$retry_num ++;
			try {

				$orders = array( $this->get_order_id( $order ) );

				$body = array(
					'message' => $reason,
					'orders'  => $orders
				);

				$response = $this->antifraud_api_request( 'POST', 'chargeback', $body );
				if ( ! $response->result == 'failure' ) {
					$this->antifraud_insert_order_metadata( $order, $response );
					$response  = $this->antifraud_insert_order_status( $order, $response );
					$retry_num = 3;
					$result    = $response;
				}
			} catch ( Exception $e ) {
				$this->logger( 'Antifraud Refund Error', $e->getMessage() );
			}
		}

		return $result;
	}


	//WEBHOOK CALLBACK

	public function webhook_callback() {
		$contents = file_get_contents( "php://input" );
		$json     = json_decode( $contents );
		$this->logger( 'Webhook Callback | Body', $json );

		if ( ! is_wp_error( $json ) ) {
			header( 'HTTP/1.1 200 OK' );

			if ( isset( $json->transactionId ) && isset( $json->referenceId ) ) {
				$this->consult_status_gateway( $json->referenceId, $json->transactionId );
			} elseif ( isset( $json->code ) && isset( $json->date ) && isset( $json->type ) ) {
				$this->consult_status_antifraud( $json->code, $json->type );
			} else {
				$this->logger( 'WebHook Callback Error', 'Fields not detected. Suspicious call.' );
			}
		}
	}

	private function consult_status_gateway( $order_id, $transactionId ) {
		$result = false;
		try {
			$order = wc_get_order( $order_id );
			if ( $this->get_order_id( $order ) == $order_id ) {
				$body     = array( 'transactionId' => $transactionId );
				$endpoint = 'transactions/' . $transactionId;
				$response = $this->gateway_api_request( 'GET', $endpoint, $body );
				if ( ! isset( $response->result ) ) {
					$this->gateway_insert_order_metadata( $order, $response );
					$res_status = $this->gateway_insert_order_status( $order, $response );

					if ( $res_status['result'] == 'success' ) {
						$this->antifraud_maybe_do_analysis( $order, $response );
					}
					$result = $res_status;
				}
			}
		} catch ( Exception $e ) {
			$this->logger( 'Gateway API Consult Status Error', $e->getMessage() );
		}

		return $result;
	}

	private function consult_status_antifraud( $order_id, $type ) {
		$result = false;
		try {
			$order = wc_get_order( $order_id );
			if ( ! is_wp_error( $order ) ) {
				if ( $type == 'status' ) {
					$body     = '';
					$endpoint = 'orders/' . $order_id . '/status';
					$response = $this->antifraud_api_request( 'GET', $endpoint, $body );
					if ( ! isset( $response->result ) ) {
						$this->antifraud_insert_order_metadata( $order, $response );
						$result = $this->antifraud_insert_order_status( $order, $response );
					}
				} else {
					$this->logger( 'Antifraud Webhook consult status error', 'Incorrect type value' );
				}
			}
		} catch ( Exception $e ) {
			$this->logger( 'Antifraud API Consult Status Error', $e->getMessage() );
		}

		return $result;
	}


	//AUTH TRANSACTIONS

	private function auth_transaction( $order ) {
		$result = false;
		try {
			$oid = $this->get_order_id( $order );

			// Connectivity
			$status = get_post_meta( $oid, '_businesspay-auth-connectivity', true );
			if ( empty( $status ) || $status == 'no' ) {
				$transaction_status = get_post_meta( $oid, '_businesspay-transaction-status', true );
				if ( $transaction_status == '5' || $transaction_status == '6' ) {
					$this->auth_transaction_connectivity( $order );
				}
			}

			// Antifraud
			if ( $this->enable_antifraud ) {
				$status = get_post_meta( $oid, '_businesspay-auth-antifraud', true );
				if ( empty( $status ) || $status == 'no' ) {
					$transaction_status = get_post_meta( $oid, '_businesspay-antifraud-transaction-id', true );
					if ( ! empty( $transaction_status ) ) {
						$this->auth_transaction_antifraud( $order );
					}
				}
			}

			$type = get_post_meta( $oid, '_businesspay-transaction-type', true );
			if ( $type == 'billet' ) {
				$status = get_post_meta( $oid, '_businesspay-auth-billet', true );
				if ( empty( $status ) || $status == 'no' ) {
					$transaction_status = get_post_meta( $oid, '_businesspay-transaction-status', true );
					if ( $transaction_status == '5' || $transaction_status == '6' ) {
						$this->auth_transaction_billet( $order );
					}
				}
			} elseif ( $type == 'credit-card' ) {
				$status = get_post_meta( $oid, '_businesspay-auth-credit', true );
				if ( empty( $status ) || $status == 'no' ) {
					$transaction_status = get_post_meta( $oid, '_businesspay-transaction-status', true );
					if ( $transaction_status == '5' || $transaction_status == '6' ) {
						$this->auth_transaction_credit( $order );
					}
				}
			} elseif ( $type == 'debit-card' ) {
				$status = get_post_meta( $oid, '_businesspay-auth-debit', true );
				if ( empty( $status ) || $status == 'no' ) {
					$transaction_status = get_post_meta( $oid, '_businesspay-transaction-status', true );
					if ( $transaction_status == '5' || $transaction_status == '6' ) {
						$this->auth_transaction_debit( $order );
					}
				}
			} elseif ( $type == 'bank-transfer' ) {
				$status = get_post_meta( $oid, '_businesspay-auth-transfer', true );
				if ( empty( $status ) || $status == 'no' ) {
					$transaction_status = get_post_meta( $oid, '_businesspay-transaction-status', true );
					if ( $transaction_status == '5' || $transaction_status == '6' ) {
						$this->auth_transaction_transfer( $order );
					}
				}
			}


		} catch ( Exception $e ) {
			$this->logger( 'Auth Connectivity Error', $e->getMessage() );
		}

		return $result;
	}

	private function auth_transaction_connectivity( $order ) {
		$result = false;
		try {
			$body     = array(
				'clientId'          => $this->get_auth_client_id(),
				'installId'         => $this->get_auth_install_id(),
				'orderId'           => $this->get_auth_order_id( $order ),
				'transactionId'     => $this->get_auth_transaction_id( $order ),
				'transactionDate'   => $this->get_auth_transaction_date( $order ),
				'transactionValue'  => $this->get_auth_transaction_value( $order ),
				'transactionStatus' => $this->get_auth_transaction_status( $order ),
				'provider'          => $this->get_auth_provider( $order ),
				'modeApi'           => $this->get_auth_mode_api(),
			);
			$response = $this->auth_api_request( 'POST', 'connectivity', $body );
			if ( ! isset( $response->result ) ) {
				$response_body = json_decode( $response );
				$status        = ( ! is_wp_error( $response_body ) ) ? 'yes' : 'no';
				update_post_meta( $this->get_order_id( $order ), '_businesspay-auth-connectivity', $status );
				$result = true;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Auth Connectivity Error', $e->getMessage() );
		}

		return $result;
	}

	private function auth_transaction_antifraud( $order ) {
		$result = false;
		try {
			$body     = array(
				'clientId'           => $this->get_auth_client_id(),
				'installId'          => $this->get_auth_install_id(),
				'orderId'            => $this->get_auth_order_id( $order ),
				'transactionId'      => $this->get_auth_transaction_id( $order ),
				'transactionDate'    => $this->get_auth_transaction_date( $order ),
				'transactionValue'   => $this->get_auth_transaction_value( $order ),
				'transactionStatus'  => $this->get_auth_transaction_status( $order ),
				'provider'           => 'CLEARSALE',
				'antifraudProductId' => $this->get_auth_antifraud_product_id(),
				'modeApi'            => $this->get_auth_mode_api(),
			);
			$response = $this->auth_api_request( 'POST', 'antifraud', $body );
			if ( ! isset( $response->result ) ) {
				$response_body = json_decode( $response );
				$status        = ( ! is_wp_error( $response_body ) ) ? 'yes' : 'no';
				update_post_meta( $this->get_order_id( $order ), '_businesspay-auth-antifraud', $status );
				$result = true;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Auth Antifraud Error', $e->getMessage() );
		}

		return $result;
	}

	private function auth_transaction_billet( $order ) {
		$result = false;
		try {
			$body     = array(
				'clientId'          => $this->get_auth_client_id(),
				'installId'         => $this->get_auth_install_id(),
				'orderId'           => $this->get_auth_order_id( $order ),
				'transactionId'     => $this->get_auth_transaction_id( $order ),
				'transactionDate'   => $this->get_auth_transaction_date( $order ),
				'transactionValue'  => $this->get_auth_transaction_value( $order ),
				'transactionStatus' => $this->get_auth_transaction_status( $order ),
				'provider'          => $this->get_auth_provider( $order ),
				'modeApi'           => $this->get_auth_mode_api(),
			);
			$response = $this->auth_api_request( 'POST', 'billet', $body );
			if ( ! isset( $response->result ) ) {
				$response_body = json_decode( $response );
				$status        = ( ! is_wp_error( $response_body ) ) ? 'yes' : 'no';
				update_post_meta( $this->get_order_id( $order ), '_businesspay-auth-billet', $status );
				$result = true;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Auth Billet Error', $e->getMessage() );
		}

		return $result;
	}

	private function auth_transaction_credit( $order ) {
		$result = false;
		try {
			$body     = array(
				'clientId'          => $this->get_auth_client_id(),
				'installId'         => $this->get_auth_install_id(),
				'orderId'           => $this->get_auth_order_id( $order ),
				'transactionId'     => $this->get_auth_transaction_id( $order ),
				'transactionDate'   => $this->get_auth_transaction_date( $order ),
				'transactionValue'  => $this->get_auth_transaction_value( $order ),
				'transactionStatus' => $this->get_auth_transaction_status( $order ),
				'cardInstallments'  => $this->get_auth_card_installments( $order ),
				'cardBrand'         => $this->get_auth_card_brand( $order ),
				'provider'          => $this->get_auth_provider( $order ),
				'modeApi'           => $this->get_auth_mode_api(),
			);
			$response = $this->auth_api_request( 'POST', 'credit', $body );
			if ( ! isset( $response->result ) ) {
				$response_body = json_decode( $response );
				$status        = ( ! is_wp_error( $response_body ) ) ? 'yes' : 'no';
				update_post_meta( $this->get_order_id( $order ), '_businesspay-auth-credit', $status );
				$result = true;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Auth Credit Error', $e->getMessage() );
		}

		return $result;
	}

	private function auth_transaction_debit( $order ) {
		$result = false;
		try {
			$body     = array(
				'clientId'          => $this->get_auth_client_id(),
				'installId'         => $this->get_auth_install_id(),
				'orderId'           => $this->get_auth_order_id( $order ),
				'transactionId'     => $this->get_auth_transaction_id( $order ),
				'transactionDate'   => $this->get_auth_transaction_date( $order ),
				'transactionValue'  => $this->get_auth_transaction_value( $order ),
				'transactionStatus' => $this->get_auth_transaction_status( $order ),
				'cardBrand'         => $this->get_auth_card_brand( $order ),
				'provider'          => $this->get_auth_provider( $order ),
				'modeApi'           => $this->get_auth_mode_api(),
			);
			$response = $this->auth_api_request( 'POST', 'debit', $body );
			if ( ! isset( $response->result ) ) {
				$response_body = json_decode( $response );
				$status        = ( ! is_wp_error( $response_body ) ) ? 'yes' : 'no';
				update_post_meta( $this->get_order_id( $order ), '_businesspay-auth-debit', $status );
				$result = true;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Auth Debit Error', $e->getMessage() );
		}

		return $result;
	}

	private function auth_transaction_transfer( $order ) {
		$result = false;
		try {
			$body     = array(
				'clientId'          => $this->get_auth_client_id(),
				'installId'         => $this->get_auth_install_id(),
				'orderId'           => $this->get_auth_order_id( $order ),
				'transactionId'     => $this->get_auth_transaction_id( $order ),
				'transactionDate'   => $this->get_auth_transaction_date( $order ),
				'transactionValue'  => $this->get_auth_transaction_value( $order ),
				'transactionStatus' => $this->get_auth_transaction_status( $order ),
				'provider'          => $this->get_auth_provider( $order ),
				'modeApi'           => $this->get_auth_mode_api(),
			);
			$response = $this->auth_api_request( 'POST', 'debit', $body );
			if ( ! isset( $response->result ) ) {
				$response_body = json_decode( $response );
				$status        = ( ! is_wp_error( $response_body ) ) ? 'yes' : 'no';
				update_post_meta( $this->get_order_id( $order ), '_businesspay-auth-transfer', $status );
				$result = true;
			}
		} catch ( Exception $e ) {
			$this->logger( 'Auth Debit Error', $e->getMessage() );
		}

		return $result;
	}

	private function auth_api_request( $method, $endpoint, $body ) {
		$result = array( 'result' => 'failure', 'messages' => '' );
		try {
			$payload = array(
				'method'    => $method,
				'sslverify' => true,
				'timeout'   => 60,
				'headers'   => array(
					'Content-Type'      => 'application/json',
					'authenticationApi' => $this->authentication_api,
					'authenticationKey' => $this->authentication_key
				),
				'body'      => json_encode( $body )
			);

			$url = $this->auth_schema . '://' . $this->auth_gateway . '.' . $this->authority . '.com.br';
			$url .= '/' . $this->auth_api . '/' . $this->auth_scope . '/' . $this->auth_version . '/' . $endpoint;

			$this->logger( 'API Auth Request | URL', $url );
			$this->logger( 'API Auth Request | BODY', $body );
			$response = wp_safe_remote_post( $url, $payload );

			if ( ! is_wp_error( $response ) ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ) );
				$this->logger( 'API Auth Response | JSON', $response_body );
				if ( ! empty( $response_body ) ) {
					$result = $response_body;
				} else {
					wc_add_notice( esc_html__( 'The credentials for this virtual store are not valid on the BusinessPay server. Please try again.', 'woocommerce-businesspay' ), 'error' );
				}
			} else {
				$this->logger( 'Auth API Request Error', 'is_wp_error' );
			}
		} catch ( Exception $e ) {
			$this->logger( 'Auth API Request Error', $e->getMessage() );
		}

		return $result;
	}


	//ADMIN METABOXES

	public function businesspay_admin_panel_gateway() {
		wc_get_template( 'businesspay-panel-gateway.php', array( 'bpConfig' => $this ), 'woocommerce/businesspay/', WC_BusinessPay::get_templates_path() );
	}

	public function businesspay_admin_panel_antifraud() {
		wc_get_template( 'businesspay-panel-antifraud.php', array( 'bpConfig' => $this ), 'woocommerce/businesspay/', WC_BusinessPay::get_templates_path() );
	}


	//GATEWAY GETTERS - Order

	private function get_gateway_selected_method( $fields ) {
		$payment_type = $this->get_gateway_selected_tab( $fields );
		$method       = $payment_type;
		if ( $payment_type == 'card' ) {
			$installments = $this->get_gateway_payment_card_installments( $fields );
			if ( $installments == 'debit' ) {
				$method = $installments;
			} else {
				$method = 'credit';
			}
		}

		return $method;
	}

	private function get_gateway_selected_tab( $fields ) {
		return sanitize_text_field( $fields['businesspay_selected_tab'] );
	}

	private function get_gateway_order_reference_id( $order ) {
		return $this->get_order_id( $order );
	}

	private function get_gateway_order_amount( $order ) {
		return str_replace( '.', '', $order->order_total );
	}

	private function get_gateway_order_description() {
		return esc_html__( 'Woocommerce Order', 'woocommerce-businesspay' );
	}

	private function get_gateway_order_postback_url() {
		return $this->get_postback_url();
	}

	//GATEWAY GETTERS - Payment

	private function get_gateway_payment_type( $fields ) {
		$installments = sanitize_text_field( $fields['businesspay_installments'] );
		if ( $installments == 'debit' ) {
			$type = '2';
		} else {
			$type = '1';
		}

		return $type;
	}

	private function get_gateway_payment_card_capture() {
		return $this->enable_antifraud ? 'false' : 'true';
	}

	private function get_gateway_payment_card_save() {
		//Save card to create token
		return 'false';
	}

	private function get_gateway_payment_card_interest_type() {
		//3: Parcelado loja. 4: Parcelado Administrador
		return 4;
	}

	private function get_gateway_payment_card_authenticate() {
		//1: Somente transações autenticadas. 2: Autenticadas e não autenticadas. 3: Autorizar sem autenticação.
		return ( $this->sandbox ) ? 3 : 1;
	}

	private function get_gateway_payment_card_recurrent() {
		return 'false';
	}

	private function get_gateway_payment_card_soft_descriptor() {
		return $this->name_in_invoice;
	}

	private function get_gateway_payment_card_info_number( $fields ) {
		return str_replace( ' ', '', sanitize_text_field( $fields['businesspay_card_number'] ) );
	}

	private function get_gateway_payment_card_info_brand( $fields ) {
		$brand = strtolower( str_replace( array( ' ' ), '', sanitize_text_field( $fields['businesspay_card_brand'] ) ) );

		switch ( $brand ) {
			case 'amex':
				$result = 'Amex';
				break;
			case 'aura':
				$result = 'Aura';
				break;
			case 'dinersclub':
				$result = 'Diners';
				break;
			case 'discover':
				$result = 'Discover';
				break;
			case 'elo':
				$result = 'Elo';
				break;
			case 'hipercard':
				$result = 'Hipercard';
				break;
			case 'hiper':
				$result = 'Hiper';
				break;
			case 'jcb':
				$result = 'JCB';
				break;
			case 'mastercard':
				$result = 'Mastercard';
				break;
			case 'visa':
				$result = 'Visa';
				break;
			default:
				$result = '';
				break;
		}

		return $result;
	}

	private function get_gateway_payment_card_info_holder_name( $fields ) {
		return sanitize_text_field( $fields['businesspay_holder_name'] );
	}

	private function get_gateway_payment_card_info_expiry( $fields ) {
		return sanitize_text_field( $fields['businesspay_expiry'] );
	}

	private function get_gateway_payment_card_info_expiry_month( $fields ) {
		$temp_date = explode( '/', $this->get_gateway_payment_card_info_expiry( $fields ) );

		return trim( $temp_date[0] );
	}

	private function get_gateway_payment_card_info_expiry_year( $fields ) {
		$temp_date = explode( '/', $this->get_gateway_payment_card_info_expiry( $fields ) );

		return trim( $temp_date[1] );
	}

	private function get_gateway_payment_card_info_cvv( $fields ) {
		return sanitize_text_field( $fields['businesspay_cvv'] );
	}

	private function get_gateway_payment_card_installments( $fields ) {
		$installments = sanitize_text_field( $fields['businesspay_installments'] );
		if ( $installments == 'debit' ) {
			$installments = '1';
		}

		return $installments;
	}

	private function get_gateway_payment_card_document( $fields ) {
		return str_replace( array( '.', '/', '-', ' ' ), '', sanitize_text_field( $fields['businesspay_doc'] ) );
	}

	private function get_gateway_payment_billet_expiration_date() {
		$interval = $this->billet_number_days;
		if ( empty( $interval ) ) {
			$interval = $this->default_billet_expiration_days;
		}
		try {
			$expiration    = new DateTime();
			$interval_spec = 'P' . $interval . 'D';
			$expiration->add( new DateInterval( $interval_spec ) );
			$result = $expiration->format( 'Y-m-d' );
		} catch ( Exception $e ) {
			$result = false;
			$this->logger( 'Get Billet Expiration Error', $e->getMessage() );
		}

		return $result;
	}

	private function get_gateway_payment_billet_instruction() {
		return $this->billet_instruction_1;
	}

	private function get_gateway_payment_billet_guarantor() {
		return 'Comprador';
	}

	private function get_gateway_payment_amount( $response ) {
		$payment_amount = '0';
		if ( isset( $response->payment->card ) ) {
			$payment_amount = $response->payment->card->capturedAmount;
		} elseif ( isset( $response->payment->bankSlip ) ) {
			$payment_amount = $response->payment->bankSlip->paymentAmount;
		} elseif ( $response->payment->electronicTransfer ) {
			$payment_amount = $response->payment->electronicTransfer->paymentAmount;
		}

		return $payment_amount;
	}

	private function get_gateway_payment_captured( $response ) {
		$captured = false;
		$status   = $response->status;
		if ( isset( $response->payment->card ) ) {
			$captured = $response->payment->card->capture;
		} elseif ( isset( $response->payment->bankSlip ) ) {
			$captured = ( $response->payment->bankSlip->paymentAmount >= $response->amount );
		} elseif ( $response->payment->electronicTransfer ) {
			$captured = ( $status == '5' || $status == '6' ) ? true : false;
		}

		return $captured;
	}

	private function get_gateway_payment_transfer_provider( $fields ) {
		return sanitize_text_field( $fields['businesspay_transfer_bank'] );
	}

	private function get_gateway_payment_billet_provider( $fields ) {
		return sanitize_text_field( $fields['businesspay_billet_bank'] );
	}

	//GATEWAY GETTERS - Customer

	private function get_gateway_customer_name( $fields ) {
		$payment_type = $this->get_gateway_selected_tab( $fields );
		$name         = '';
		switch ( $payment_type ) {
			case 'card':
				$name = $this->get_gateway_payment_card_info_holder_name( $fields );
				break;
			case 'transfer':
				$name = sanitize_text_field( $fields['billing_first_name'] . ' ' . $fields['billing_last_name'] );
				break;
			case 'billet':
				$name = sanitize_text_field( $fields['billing_first_name'] . ' ' . $fields['billing_last_name'] );
				break;
		}

		return $name;
	}

	private function get_gateway_customer_document( $fields ) {
		$payment_type = $this->get_gateway_selected_tab( $fields );
		$doc          = '';
		switch ( $payment_type ) {
			case 'card':
				$doc = $this->get_gateway_payment_card_document( $fields );
				break;
			case 'transfer':
				$doc = sanitize_text_field( $fields['billing_cpf'] );
				break;
			case 'billet':
				$doc = sanitize_text_field( $fields['billing_cpf'] );
				break;
		}

		return $doc;
	}

	private function get_gateway_customer_email( $fields ) {
		return sanitize_email( $fields['billing_email'] );
	}

	private function get_gateway_customer_address( $fields ) {
		return sanitize_text_field( $fields['billing_address_1'] );
	}

	private function get_gateway_customer_address_number( $fields ) {
		return sanitize_text_field( $fields['billing_number'] );
	}

	private function get_gateway_customer_address_complement( $fields ) {
		return sanitize_text_field( $fields['billing_address_2'] );
	}

	private function get_gateway_customer_address_district( $fields ) {
		return sanitize_text_field( $fields['billing_neighborhood'] );
	}

	private function get_gateway_customer_address_zipcode( $fields ) {
		return str_replace( '-', '', sanitize_text_field( $fields['billing_postcode'] ) );
	}

	private function get_gateway_customer_address_city( $fields ) {
		return sanitize_text_field( $fields['billing_city'] );
	}

	private function get_gateway_customer_address_state( $fields ) {
		return sanitize_text_field( $fields['billing_state'] );
	}

	//GATEWAY GETTERS - Billing

	private function get_gateway_billing_neighborhood( $fields ) {
		return sanitize_text_field( $fields['billing_neighborhood'] );
	}


	//ANTIFRAUD GETTERS - Order

	private function get_antifraud_order_id( $order ) {
		return (string) $this->get_order_id( $order );
	}

	private function get_antifraud_order_fingerprint() {
		return $this->get_session_id();
	}

	private function get_antifraud_order_date( $order ) {
		return sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-date', true ) );
	}

	private function get_antifraud_order_email( $order ) {
		return sanitize_email( $order->billing_email );
	}

	private function get_antifraud_order_total_items( $order ) {
		return $order->get_subtotal();
	}

	private function get_antifraud_order_total_order( $order ) {
		return (float) $order->get_total();
	}

	private function get_antifraud_order_qtd_installments( $order ) {
		return (integer) sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-card-installments', true ) );
	}

	private function get_antifraud_order_ip( $order ) {
		return $order->get_customer_ip_address();
	}

	private function get_antifraud_order_obs( $order ) {
		return sanitize_text_field( $order->get_customer_note() );
	}

	private function get_antifraud_order_origin() {
		return sanitize_text_field( $this->get_blog_domain() );
	}

	private function get_antifraud_order_channel_id() {
		return sanitize_text_field( $this->get_blog_name() );
	}

	private function get_antifraud_order_product_id() {
		return (integer) $this->antifraud_product_id;
	}

	private function get_antifraud_order_product_name() {
		switch ( $this->antifraud_product_id ) {
			case '1':
				$result = 'Realtime Decision';
				break;
			case '2':
				$result = 'Total ClearSale';
				break;
			case '3':
				$result = 'Total Garantido';
				break;
			case '4':
				$result = 'Application';
				break;
			default:
				$result = '';
				break;
		}

		return $result;
	}

	//ANTIFRAUD GETTERS - Billing

	private function get_antifraud_billing_client_id( $order ) {
		return sanitize_text_field( $order->get_user_id() );
	}

	private function get_antifraud_billing_person_type( $order ) {
		return (integer) sanitize_text_field( $order->get_meta( '_billing_persontype' ) );
	}

	private function get_antifraud_billing_document( $order ) {
		$rg   = sanitize_text_field( $order->get_meta( '_billing_rg' ) );
		$cpf  = sanitize_text_field( $order->get_meta( '_billing_cpf' ) );
		$cnpj = sanitize_text_field( $order->get_meta( '_billing_cnpj' ) );
		if ( ! empty( $cpf ) ) {
			$pdocument = $cpf;
		} elseif ( ! empty( $cnpj ) ) {
			$pdocument = $cnpj;
		} else {
			$pdocument = $rg;
		}

		return $pdocument;
	}

	private function get_antifraud_billing_name( $order ) {
		return sanitize_text_field( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	}

	private function get_antifraud_billing_email( $order ) {
		return sanitize_email( $order->get_billing_email() );
	}

	private function get_antifraud_billing_street( $order ) {
		return sanitize_text_field( $order->get_billing_address_1() );
	}

	private function get_antifraud_billing_complement( $order ) {
		return sanitize_text_field( $order->get_billing_address_2() );
	}

	private function get_antifraud_billing_street_number( $order ) {
		return sanitize_text_field( $order->get_meta( '_billing_number' ) );
	}

	private function get_antifraud_billing_county( $order ) {
		return sanitize_text_field( $order->get_meta( '_billing_neighborhood' ) );
	}

	private function get_antifraud_billing_city( $order ) {
		return sanitize_text_field( $order->get_billing_city() );
	}

	private function get_antifraud_billing_state( $order ) {
		return sanitize_text_field( $order->get_billing_state() );
	}

	private function get_antifraud_billing_country( $order ) {
		return sanitize_text_field( $order->get_billing_country() );
	}

	private function get_antifraud_billing_zipcode( $order ) {
		return sanitize_text_field( $order->get_billing_postcode() );
	}

	private function get_antifraud_billing_phone_type( $order ) {
		$phone = str_replace( array( '(', ')', ' ', '-' ), '', sanitize_text_field( $order->get_billing_phone() ) );
		if ( strlen( $phone ) >= 11 ) {
			$result = 6;
		} //Celular
		elseif ( $this->get_antifraud_billing_person_type( $order ) == 1 ) { //Pessoa física
			$result = 1; //Residencial
		} else { //Pessoa jurídica
			$result = 2; //Comercial
		}

		return $result;
	}

	private function get_antifraud_billing_phone_ddi() {
		return 55;
	}

	private function get_antifraud_billing_phone_ddd( $order ) {
		$phone = sanitize_text_field( $order->get_billing_phone() );
		$ddd   = substr( $phone, 0, strpos( $phone, ')', 1 ) );
		$ddd   = str_replace( array( '(', ')' ), '', $ddd );

		return (integer) $ddd;
	}

	private function get_antifraud_billing_phone_number( $order ) {
		$phone = sanitize_text_field( $order->get_billing_phone() );
		$phone = substr( $phone, strpos( $phone, ')', 1 ) );
		$phone = str_replace( array( '(', ')', ' ', '-' ), '', $phone );

		return (integer) $phone;
	}

	//ANTIFRAUD GETTERS - Shipping

	private function get_antifraud_shipping_client_id( $order ) {
		return sanitize_text_field( $order->get_user_id() );
	}

	private function get_antifraud_shipping_person_type( $order ) {
		return (integer) sanitize_text_field( $order->get_meta( '_billing_persontype' ) );
	}

	private function get_antifraud_shipping_document( $order ) {
		$rg   = sanitize_text_field( $order->get_meta( '_billing_rg' ) );
		$cpf  = sanitize_text_field( $order->get_meta( '_billing_cpf' ) );
		$cnpj = sanitize_text_field( $order->get_meta( '_billing_cnpj' ) );
		if ( ! empty( $cpf ) ) {
			$pdocument = $cpf;
		} elseif ( ! empty( $cnpj ) ) {
			$pdocument = $cnpj;
		} else {
			$pdocument = $rg;
		}

		return $pdocument;
	}

	private function get_antifraud_shipping_name( $order ) {
		$name = trim( sanitize_text_field( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() ) );
		if ( empty( $name ) ) {
			$name = trim( sanitize_text_field( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) );
		}

		return $name;
	}

	private function get_antifraud_shipping_email( $order ) {
		return sanitize_email( $order->get_billing_email() );
	}

	private function get_antifraud_shipping_street( $order ) {
		$zip = trim( sanitize_text_field( $order->get_shipping_postcode() ) );
		if ( empty( $zip ) ) {
			$street = trim( sanitize_text_field( $order->get_billing_address_1() ) );
		} else {
			$street = trim( sanitize_text_field( $order->get_shipping_address_1() ) );
		}

		return $street;
	}

	private function get_antifraud_shipping_complement( $order ) {
		$zip = trim( sanitize_text_field( $order->get_shipping_postcode() ) );
		if ( empty( $zip ) ) {
			$comp = sanitize_text_field( $order->get_billing_address_2() );
		} else {
			$comp = sanitize_text_field( $order->get_shipping_address_2() );
		}

		return $comp;
	}

	private function get_antifraud_shipping_street_number( $order ) {
		$zip = trim( sanitize_text_field( $order->get_shipping_postcode() ) );
		if ( empty( $zip ) ) {
			$number = trim( sanitize_text_field( $order->get_meta( '_billing_number' ) ) );
		} else {
			$number = trim( sanitize_text_field( $order->get_meta( '_shipping_number' ) ) );
		}

		return $number;
	}

	private function get_antifraud_shipping_county( $order ) {
		$zip = trim( sanitize_text_field( $order->get_shipping_postcode() ) );
		if ( empty( $zip ) ) {
			$county = trim( sanitize_text_field( $order->get_meta( '_billing_neighborhood' ) ) );
		} else {
			$county = trim( sanitize_text_field( $order->get_meta( '_shipping_neighborhood' ) ) );
		}

		return $county;
	}

	private function get_antifraud_shipping_city( $order ) {
		$zip = trim( sanitize_text_field( $order->get_shipping_postcode() ) );
		if ( empty( $zip ) ) {
			$city = trim( sanitize_text_field( $order->get_billing_city() ) );
		} else {
			$city = trim( sanitize_text_field( $order->get_shipping_city() ) );
		}

		return $city;
	}

	private function get_antifraud_shipping_state( $order ) {
		$zip = trim( sanitize_text_field( $order->get_shipping_postcode() ) );
		if ( empty( $zip ) ) {
			$state = trim( sanitize_text_field( $order->get_billing_state() ) );
		} else {
			$state = trim( sanitize_text_field( $order->get_shipping_state() ) );
		}

		return $state;
	}

	private function get_antifraud_shipping_country( $order ) {
		$zip = sanitize_text_field( $order->get_shipping_postcode() );
		if ( empty( $zip ) ) {
			$country = trim( sanitize_text_field( $order->get_billing_country() ) );
		} else {
			$country = trim( sanitize_text_field( $order->get_shipping_country() ) );
		}

		return $country;
	}

	private function get_antifraud_shipping_zipcode( $order ) {
		$zip = trim( sanitize_text_field( $order->get_shipping_postcode() ) );
		if ( empty( $zip ) ) {
			$zip = trim( sanitize_text_field( $order->get_billing_postcode() ) );
		}

		return $zip;
	}

	private function get_antifraud_shipping_phone_type( $order ) {
		$phone = str_replace( array( '(', ')', ' ', '-' ), '', sanitize_text_field( $order->get_billing_phone() ) );
		if ( strlen( $phone ) >= 11 ) {
			$result = 6;
		} //Celular
		elseif ( $this->get_antifraud_billing_person_type( $order ) == 1 ) { //Pessoa física
			$result = 1; //Residencial
		} else { //Pessoa jurídica
			$result = 2; //Comercial
		}

		return $result;
	}

	private function get_antifraud_shipping_phone_ddi() {
		return 55;
	}

	private function get_antifraud_shipping_phone_ddd( $order ) {
		$phone = sanitize_text_field( $order->get_billing_phone() );
		$ddd   = substr( $phone, 0, strpos( $phone, ')', 1 ) );
		$ddd   = str_replace( array( '(', ')' ), '', $ddd );

		return (integer) $ddd;
	}

	private function get_antifraud_shipping_phone_number( $order ) {
		$phone = sanitize_text_field( $order->get_billing_phone() );
		$phone = substr( $phone, strpos( $phone, ')', 1 ) );
		$phone = str_replace( array( '(', ')', ' ', '-' ), '', $phone );

		return (integer) $phone;
	}

	private function get_antifraud_shipping_delivery_price( $order ) {
		return (float) $order->get_total_shipping();
	}

	//ANTIFRAUD GETTERS - Payment

	private function get_antifraud_payment_date( $order ) {
		return sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-date', true ) );
	}

	private function get_antifraud_payment_value( $order ) {
		return (float) $order->get_total();
	}

	private function get_antifraud_payment_type( $order ) {
		$card_type = sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-card-type', true ) );
		if ( $card_type == '2' ) {
			$card_type = '3'; //débito é 3 na clearsale
		}

		return (integer) $card_type;
	}

	private function get_antifraud_payment_qtd_installments( $order ) {
		return (integer) sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-card-installments', true ) );
	}

	private function get_antifraud_payment_card_bin( $order ) {
		$number = sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-card-cardinfo-number', true ) );

		return substr( $number, 0, strpos( $number, '*' ) );
	}

	private function get_antifraud_payment_card_end( $order ) {
		$number = sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-card-cardinfo-number', true ) );
		$number = str_replace( '******', '*', $number );

		return str_replace( '*', '', substr( $number, strpos( $number, '*', 1 ) ) );
	}

	private function get_antifraud_payment_card_type( $order ) {
		$card_type = strtoupper( sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-card-brand', true ) ) );
		switch ( $card_type ) {
			case 'DINERS CLUB'      :
				$card_num = 1;
				break;
			case 'DINERSCLUB'       :
				$card_num = 1;
				break;
			case 'DINERS'           :
				$card_num = 1;
				break;
			case 'MASTER CARD'      :
				$card_num = 2;
				break;
			case 'MASTERCARD'       :
				$card_num = 2;
				break;
			case 'MASTER'           :
				$card_num = 2;
				break;
			case 'VISA'             :
				$card_num = 3;
				break;
			case 'AMERICAN EXPRESS' :
				$card_num = 5;
				break;
			case 'AMERICANEXPRESS'  :
				$card_num = 5;
				break;
			case 'AMEX'             :
				$card_num = 5;
				break;
			case 'HIPER CARD'       :
				$card_num = 6;
				break;
			case 'HIPERCARD'        :
				$card_num = 6;
				break;
			case 'HIPER'            :
				$card_num = 6;
				break;
			case 'AURA'             :
				$card_num = 7;
				break;
			default                 :
				$card_num = 4;
				break; //outros
		}

		return $card_num;
	}

	private function get_antifraud_payment_card_expiration( $order ) {
		$month = sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-card-cardinfo-expiration-month', true ) );
		$year  = sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-card-cardinfo-expiration-year', true ) );

		return $month . '/' . $year;
	}

	private function get_antifraud_payment_card_holder_name( $order ) {
		return sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-customer-name', true ) );
	}

	private function get_antifraud_payment_card_legal_document( $order ) {
		return sanitize_text_field( get_post_meta( $this->get_order_id( $order ), '_businesspay-customer-document', true ) );
	}

	//ANTIFRAUD GETTERS - Item

	private function get_antifraud_item_id( $order_item ) {
		return sanitize_text_field( $order_item['product_id'] );
	}

	private function get_antifraud_item_name( $order_item ) {
		return sanitize_text_field( $order_item['name'] );
	}

	private function get_antifraud_item_value( $order_item ) {
		return (float) sanitize_text_field( $order_item->get_subtotal() );
	}

	private function get_antifraud_item_amount( $order_item ) {
		return (integer) sanitize_text_field( $order_item['qty'] );
	}

	private function get_antifraud_item_category_id( $order_item ) {
		$cats = get_the_terms( $this->get_antifraud_item_id( $order_item ), 'product_cat' );

		return $cats[0]->term_id;
	}

	private function get_antifraud_item_category_name( $order_item ) {
		$cats = get_the_terms( $this->get_antifraud_item_id( $order_item ), 'product_cat' );

		return $cats[0]->name;
	}


	// AUTH GETTERS

	private function get_auth_client_id() {
		return $this->auth_client_id;
	}

	private function get_auth_install_id() {
		return $this->auth_install_id;
	}

	private function get_auth_order_id( $order ) {
		return $this->get_order_id( $order );
	}

	private function get_auth_transaction_id( $order ) {
		return get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-id', true );
	}

	private function get_auth_transaction_date( $order ) {
		return str_replace( 'T', ' ', get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-date', true ) );
	}

	private function get_auth_transaction_value( $order ) {
		return $order->order_total;
	}

	private function get_auth_transaction_status( $order ) {
		return get_post_meta( $this->get_order_id( $order ), '_businesspay-transaction-status', true );
	}

	private function get_auth_provider( $order ) {
		$result = '';
		$oid    = $this->get_order_id( $order );
		$type   = get_post_meta( $oid, '_businesspay-transaction-type', true );

		if ( $type == 'credit-card' || $type == 'debit-card' ) {
			$result = strtoupper( get_post_meta( $oid, '_businesspay-card-provider', true ) );
		} else if ( $type == 'billet' ) {
			$result = 'BILLET';
		} elseif ( $type == 'bank-transfer' ) {
			$result = 'TRANSFER';
		}

		return $result;
	}

	private function get_auth_mode_api() {
		return ( $this->sandbox == 'yes' ) ? 'demo' : 'live';
	}

	private function get_auth_antifraud_product_id() {
		return $this->antifraud_product_id;
	}

	private function get_auth_card_installments( $order ) {
		return get_post_meta( $this->get_order_id( $order ), '_businesspay-card-installments', true );
	}

	private function get_auth_card_brand( $order ) {
		return get_post_meta( $this->get_order_id( $order ), '_businesspay-card-brand', true );
	}


	//SETTERS

	private function set_option_auth_next( $value ) {
		update_option( 'businesspay_auth_next', $value );
		$this->auth_next = $value;
	}

	private function set_option_auth_last( $value ) {
		update_option( 'businesspay_auth_last', $value );
		$this->auth_last = $value;
	}

	private function set_option_auth_client_id( $value ) {
		update_option( 'businesspay_auth_client_id', $value );
		$this->auth_client_id = $value;
	}

	private function set_option_auth_install_id( $value ) {
		update_option( 'businesspay_auth_install_id', $value );
		$this->auth_install_id = $value;
	}

	private function set_option_auth_interval( $value ) {
		update_option( 'businesspay_auth_interval', $value );
		$this->auth_interval = $value;
	}

	private function set_option_enable_client( $value ) {
		update_option( 'businesspay_enable_client', $value );
		$this->enable_client = $value;
	}

	private function set_option_enable_install( $value ) {
		update_option( 'businesspay_enable_install', $value );
		$this->enable_install = $value;
	}

	private function set_option_enable_antifraud( $value ) {
		update_option( 'businesspay_enable_antifraud', $value );
		$this->enable_antifraud = $value;
	}

	private function set_option_antifraud_login_demo( $value ) {
		update_option( 'businesspay_antifraud_login_demo', $value );
		$this->antifraud_login_demo = $value;
	}

	private function set_option_antifraud_login_live( $value ) {
		update_option( 'businesspay_antifraud_login_live', $value );
		$this->antifraud_login_live = $value;
	}

	private function set_option_antifraud_password_demo( $value ) {
		update_option( 'businesspay_antifraud_password_demo', $value );
		$this->antifraud_password_demo = $value;
	}

	private function set_option_antifraud_password_live( $value ) {
		update_option( 'businesspay_antifraud_password_live', $value );
		$this->antifraud_password_live = $value;
	}

	private function set_option_antifraud_app_id_demo( $value ) {
		update_option( 'businesspay_antifraud_app_id_demo', $value );
		$this->antifraud_app_id_demo = $value;
	}

	private function set_option_antifraud_app_id_live( $value ) {
		update_option( 'businesspay_antifraud_app_id_live', $value );
		$this->antifraud_app_id_live = $value;
	}

	private function set_option_antifraud_product_id( $value ) {
		update_option( 'businesspay_antifraud_product_id', $value );
		$this->antifraud_product_id = $value;
	}

	private function set_option_antifraud_auth_token( $value ) {
		update_option( 'businesspay_antifraud_auth_token', $value );
		$this->antifraud_auth_token = $value;
	}

	private function set_option_antifraud_auth_expiration( $value ) {
		$expiration = new DateTime( str_replace( 'T', ' ', $value ) );
		$expiration->modify( '-1 minutes' );

		update_option( 'businesspay_antifraud_auth_expiration', $expiration->format( $this->get_date_format() ) );
		$this->antifraud_auth_expiration = $expiration;
	}

	public function set_mask( $val, $mask ) {
		$masked = '';
		$k      = 0;
		for ( $i = 0; $i <= strlen( $mask ) - 1; $i ++ ) {
			if ( $mask[ $i ] == '#' ) {
				if ( isset( $val[ $k ] ) ) {
					$masked .= $val[ $k ++ ];
				}
			} else {
				if ( isset( $mask[ $i ] ) ) {
					$masked .= $mask[ $i ];
				}
			}
		}

		return $masked;
	}

	public function set_neighborhood_required( $fields ) {
		$fields['billing_neighborhood']['required'] = true;

		return $fields;
	}


	//GETTERS

	private function get_admin_url() {
		if ( version_compare( WC_BusinessPay::get_woo_version(), '2.1', '>=' ) ) {
			return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=businesspay_gateway' );
		}

		return admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_BusinessPay_Gateway' );
	}

	private function get_blog_domain() {
		return str_replace( array( 'http://', 'https://', 'www.' ), '', get_option( 'siteurl' ) );
	}

	private function get_blog_name() {
		return get_bloginfo( 'name' );
	}

	public function get_cart_total() {
		if ( version_compare( WC_BusinessPay::get_woo_version(), '3.2.0', '>=' ) ) {
			$cart_total = WC()->cart->get_cart_contents_total();
		} else {
			global $woocommerce;
			$cart_total = $woocommerce->cart->total;
		}

		return $cart_total;
	}

	public function get_order_id( $order ) {
		return $order->get_id();
	}

	public function get_total_order( $order ) {
		return $order->get_total();
	}

	public function get_currency_symbol() {
		return get_woocommerce_currency_symbol();
	}

	private function get_postback_url() {
		if ( version_compare( WC()->version, '2.1', '>=' ) ) {
			$site_url = home_url( '/wc-api/WC_BusinessPay_Gateway/' );
		} else {
			$site_url = home_url( '/?wc-api=WC_BusinessPay_Gateway' );
		}

		return $site_url;
	}

	public function get_date_format() {
		return 'Y-m-d H:i:s';
	}

	private function get_date_interval( $date_start, $interval, $measure ) {
		$result = $date_start;
		try {
			if ( empty( $interval ) ) {
				$interval = $this->default_session_interval;
			}

			switch ( $measure ) {
				case 'days' :
					$interval_spec = 'P' . $interval . 'D';
					break;
				case 'hours':
					$interval_spec = 'PT' . $interval . 'H';
					break;
				default     :
					$interval_spec = 'PT' . $interval . 'H';
					break;
			}

			$date_interval = new DateTime( 'now', new DateTimeZone( get_option( 'timezone_string' ) ) );
			$date_interval->add( new DateInterval( $interval_spec ) );
			$result = $date_interval->format( $this->get_date_format() );
		} catch ( Exception $e ) {
			$this->logger( 'Get Date Interval Error', $e->getMessage() );
		}

		return $result;
	}

	private function get_session() {
		$today     = current_time( 'mysql' );
		$auth_last = get_option( 'businesspay_auth_last' );
		$auth_next = get_option( 'businesspay_auth_next' );

		$session = ( $auth_last == '' ) || ( $auth_next == '' ) || ( strtotime( $auth_next ) < strtotime( $today ) );

		if ( $session ) {
			$this->get_session_renew();
		}

		$client  = get_option( 'businesspay_enable_client' );
		$install = get_option( 'businesspay_enable_install' );

		return ( $client && $install );
	}

	private function get_session_renew() {
		try {
			if ( ! empty( $this->authentication_api ) && ! empty( $this->authentication_key ) ) {
				$action = $this->auth_schema . '://' . $this->auth_gateway . '.' . $this->authority . '.com.br';
				$action .= '/' . $this->auth_api . '/' . $this->auth_scope . '/' . $this->auth_version . '/newsession';

				$body = array(
					'name'           => substr( get_bloginfo( 'name' ), 0, 50 ),
					'domain'         => str_replace( array(
						'http://',
						'https://',
						'www.'
					), '', get_option( 'siteurl' ) ),
					'mode_api'       => ( $this->sandbox == 'yes' ) ? 'demo' : 'live',
					'postback_url'   => $this->get_postback_url(),
					'version_api'    => ( $this->sandbox == 'yes' ) ? str_replace( 'v', '', $this->gateway_demo_version ) : str_replace( 'v', '', $this->gateway_live_version ),
					'version_wp'     => get_bloginfo( 'version' ),
					'version_plugin' => WC_BusinessPay::VERSION,
					'version_php'    => WC_BusinessPay::get_php_version(),
					'version_woo'    => WC_BusinessPay::get_woo_version()
				);

				$payload = array(
					'method'    => 'POST',
					'sslverify' => true,
					'timeout'   => 60,
					'headers'   => array(
						'Content-Type'      => 'application/json',
						'authenticationApi' => $this->authentication_api,
						'authenticationKey' => $this->authentication_key
					),
					'body'      => json_encode( $body )
				);

				$this->logger( 'New Session | Request', $payload );

				$response = wp_safe_remote_post( $action, $payload );

				$this->logger( 'New Session | Response', $response );

				if ( ! is_wp_error( $response ) ) {
					$response_body = wp_remote_retrieve_body( $response );
					$response_body = $this->clear_response( $response_body );

					if ( ! empty( $response_body ) ) {
						$response = json_decode( $response_body );
						if ( ! empty( $response->auth_interval ) ) {
							$today = current_time( 'mysql' );
							$next  = $this->get_date_interval( $today, $response->auth_interval, 'hours' );

							$this->set_option_auth_last( $today );
							$this->set_option_auth_next( $next );
							$this->set_option_auth_client_id( $response->client_id );
							$this->set_option_auth_install_id( $response->install_id );
							$this->set_option_auth_interval( $response->auth_interval );
							$this->set_option_enable_client( $response->client_enabled );
							$this->set_option_enable_install( $response->install_enabled );
							$this->set_option_enable_antifraud( $response->antifraud_enabled );
							$this->set_option_antifraud_product_id( $response->antifraud_product_id );
							$this->set_option_antifraud_login_demo( $response->antifraud_login_demo );
							$this->set_option_antifraud_login_live( $response->antifraud_login_live );
							$this->set_option_antifraud_password_demo( $response->antifraud_password_demo );
							$this->set_option_antifraud_password_live( $response->antifraud_password_live );
							$this->set_option_antifraud_app_id_demo( $response->antifraud_app_id_demo );
							$this->set_option_antifraud_app_id_live( $response->antifraud_app_id_live );
						}
					}
				}
			}
		} catch ( Exception $e ) {
			$this->logger( 'New Session Error', $e->getMessage() );
		}
	}

	private function get_session_id() {
		$result = session_id();
		if ( empty( $result ) ) {
			session_start();
			$result = session_id();
		}

		return $result;
	}

	private function get_response_url( $order, $response ) {
		if ( isset( $response->payment->electronicTransfer->url ) ) {
			$url = $response->payment->electronicTransfer->url;
		} elseif ( isset( $response->payment->bankSlip->url ) ) {
			$url = $response->payment->bankSlip->url;
		} else {
			$url = $this->get_return_url( $order );
		}

		return $url;
	}


	//FUNCTIONS

	public function enqueue_scripts() {
		if ( $this->enabled ) {
			$asset_name_css = sprintf( 'assets/css/frontend%s.css', ( $this->debug == 'yes' ) ? '' : '.min' );
			$asset_name_js  = sprintf( 'assets/js/frontend%s.js', ( $this->debug == 'yes' ) ? '' : '.min' );
			wp_enqueue_style( 'wc-businesspay-frontend-css', plugins_url( $asset_name_css, plugin_dir_path( __FILE__ ) ), null, WC_BusinessPay::VERSION, 'all' );
			wp_enqueue_script( 'wc-businesspay-frontend-js', plugins_url( $asset_name_js, plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_BusinessPay::VERSION, true );
		}
	}

	private function is_configurated() {
		$missing_opt = ( 'no' == $this->credit_card && 'no' == $this->debit_card && 'no' == $this->transfer && 'no' == $this->billet );

		return ! $missing_opt;
	}

	private function is_brazilian() {
		return ( 'BRL' == get_woocommerce_currency() );
	}

	private function is_card_luhn( $card_number ) {
		settype( $card_number, 'string' );
		$sumTable = array(
			array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ),
			array( 0, 2, 4, 6, 8, 1, 3, 5, 7, 9 )
		);
		$sum      = 0;
		$flip     = 0;
		for ( $i = strlen( $card_number ) - 1; $i >= 0; $i -- ) {
			$sum += $sumTable[ $flip ++ & 0x1 ][ $card_number[ $i ] ];
		}

		return $sum % 10 === 0;
	}

	private function is_cpf( $cpf = null ) {

		// Verifica se um número foi informado
		if ( empty( $cpf ) ) {
			return false;
		}

		// Elimina possivel mascara
		$cpf = preg_replace( "/[^0-9]/", "", $cpf );
		$cpf = str_pad( $cpf, 11, '0', STR_PAD_LEFT );

		// Verifica se o numero de digitos informados é igual a 11
		if ( strlen( $cpf ) != 11 ) {
			return false;
		}
		// Verifica se nenhuma das sequências invalidas abaixo
		// foi digitada. Caso afirmativo, retorna falso
		else if ( $cpf == '00000000000' ||
		          $cpf == '11111111111' ||
		          $cpf == '22222222222' ||
		          $cpf == '33333333333' ||
		          $cpf == '44444444444' ||
		          $cpf == '55555555555' ||
		          $cpf == '66666666666' ||
		          $cpf == '77777777777' ||
		          $cpf == '88888888888' ||
		          $cpf == '99999999999' ) {
			return false;
			// Calcula os digitos verificadores para verificar se o CPF é válido
		} else {
			for ( $t = 9; $t < 11; $t ++ ) {
				for ( $d = 0, $c = 0; $c < $t; $c ++ ) {
					$d += $cpf{$c} * ( ( $t + 1 ) - $c );
				}
				$d = ( ( 10 * $d ) % 11 ) % 10;
				if ( $cpf{$c} != $d ) {
					return false;
				}
			}
			return true;
		}
	}

	private function is_cnpj( $cnpj = null ) {
		// Verifica se um número foi informado
		if ( empty( $cnpj ) ) {
			return false;
		}

		$cnpj = preg_replace( '/[^0-9]/', '', (string) $cnpj );
		// Valida tamanho
		if ( strlen( $cnpj ) != 14 ) {
			return false;
		}
		// Valida primeiro dígito verificador
		for ( $i = 0, $j = 5, $soma = 0; $i < 12; $i ++ ) {
			$soma += $cnpj{$i} * $j;
			$j    = ( $j == 2 ) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		if ( $cnpj{12} != ( $resto < 2 ? 0 : 11 - $resto ) ) {
			return false;
		}
		// Valida segundo dígito verificador
		for ( $i = 0, $j = 6, $soma = 0; $i < 13; $i ++ ) {
			$soma += $cnpj{$i} * $j;
			$j    = ( $j == 2 ) ? 9 : $j - 1;
		}
		$resto = $soma % 11;

		return $cnpj{13} == ( $resto < 2 ? 0 : 11 - $resto );
	}

	private function clear_response( $response ) {
		return strstr( $response, '{' ); //clear response body hack
	}

	private function update_order_status( $order, $status, $note ) {
		if ( $order->get_status() == $status ) {
			wc_create_order_note( $this->get_order_id( $order ), $note );
		} else {
			$order->update_status( $status, $note );
		}
	}

	private function antifraud_insert_fingerprint() {
		if ( $this->enable_antifraud ) {
			if ( $this->debug == 'yes' ) {
				$app_id = get_option( 'businesspay_antifraud_app_id_demo' );
			} else {
				$app_id = get_option( 'businesspay_antifraud_app_id_live' );
			}

			$session_id = $this->get_session_id();

			echo "<script>
                    (function (a, b, c, d, e, f, g){
                        a['CsdpObject'] = e;
                        a[e] = a[e] || function () {
                            (a[e].q = a[e].q || []).push(arguments)
                        },
                        a[e].l = 1 * new Date();
                        f = b.createElement(c),
                        g = b.getElementsByTagName(c)[0];
                    	f.async = 1;
                    	f.src = d;
                    	g.parentNode.insertBefore(f, g) }
                    )
                    (window, document, 'script', '//device.clearsale.com.br/p/fp.js', 'csdp');
                    csdp('app', '" . $app_id . "');
                    csdp('sessionid', '" . $session_id . "');
		          </script>";
		}
	}

	private function antifraud_insert_mapper() {
		if ( $this->enable_antifraud ) {
			if ( $this->debug == 'yes' ) {
				$app_id = get_option( 'businesspay_antifraud_app_id_demo' );
			} else {
				$app_id = get_option( 'businesspay_antifraud_app_id_live' );
			}
			if ( ! is_admin() ) {
				echo "<script>
                        (function (a, b, c, d, e, f, g) {
                                a['CsdmObject'] = e; a[e] = a[e] || function () {
                                        (a[e].q = a[e].q || []).push(arguments)
                                }, a[e].l = 1 * new Date(); f = b.createElement(c),
                                g = b.getElementsByTagName(c)[0]; f.async = 1; f.src = d; g.parentNode.insertBefore(f, g)
                        })(window, document, 'script', '//device.clearsale.com.br/m/cs.js', 'csdm');
                        csdm('app', '" . $app_id . "');
                    </script>";
			}

			$page = '';
			if ( is_product_category() ) {
				$page = 'category';
			}

			if ( is_product() ) {
				$page = 'product';
				$name = $GLOBALS['wp']->query_vars['product'];
			}

			if ( is_cart() ) {
				$page = 'cart';
			}

			if ( is_checkout() ) {
				$page = 'checkout';
			}

			if ( is_account_page() ) {
				$page = 'edit-account';
			}

			if ( is_front_page() ) {
				$page = 'home';
			}

			if ( ! empty( $page ) ) {
				echo "<meta name=\"cs:page\" content=\"$page\">";
			}

			if ( isset( $name ) ) {
				echo "<meta name=\"cs:description\" content=\"name=$name\">";
			}
		}
	}

	private function logger( $scope, $message ) {
		if ( $this->debug == 'yes' ) {
			global $woocommerce;
			if ( is_null( $this->logger ) ) {
				if ( class_exists( 'WC_Logger' ) ) {
					$this->logger = new WC_Logger();
				} else {
					$this->logger = $woocommerce->logger();
				}
			}
			$this->logger->add( 'BusinessPay', $scope . ': ' . print_r( $message, true ) );
		}
	}


	//NOTICES

	private function admin_notices() {
		if ( is_admin() ) {
			if ( 'yes' == $this->enabled ) {
				if ( ! $this->is_configurated() ) {
					$this->enabled = false;
					add_action( 'admin_notices', array( $this, 'notice_missing_options' ) );
				}

				if ( ! $this->is_brazilian() ) {
					$this->enabled = false;
					add_action( 'admin_notices', array( $this, 'notice_wrong_currency' ) );
				}
			}
		}
	}

	protected function notice_missing_options() {
		$msg1 = esc_html__( 'BusinessPay Disabled', 'woocommerce-businesspay' );
		$msg2 = esc_html__( 'You must enable at least one form of payment.', 'woocommerce-businesspay' );
		$link = '<a href="' . $this->get_admin_url() . '">' . esc_html__( 'Click here to configure!', 'woocommerce-businesspay' ) . '</a>';
		echo sprintf( '<div class="error"><p><strong>%s</strong> %s %s</p></div>', $msg1, $msg2, $link );
	}

	protected function notice_wrong_currency() {
		$msg1 = esc_html__( 'BusinessPay Disabled', 'woocommerce-businesspay' );
		$msg2 = sprintf( esc_html__( 'Currency <code>%s</code> is not supported. Works only with <code>BRL</code> (Brazilian Real).', 'woocommerce-businesspay' ), get_woocommerce_currency() );
		echo sprintf( '<div class="error"><p><strong>%s</strong> %s</p></div>', $msg1, $msg2 );
	}

}