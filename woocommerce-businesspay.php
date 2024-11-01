<?php
/**
 * Plugin Name:             WooCommerce BusinessPay
 * Plugin URI:              https://www.businesspay.com.br
 * Description:             Solução de meio de pagamento online com as melhores taxas do mercado e antifraude by ClearSale.
 * Author:                  BusinessPay
 * Author URI:              https://www.businesspay.com.br/
 * Version:                 1.1.1
 * Text Domain:             woocommerce-businesspay
 * Domain Path:             /languages
 * License:                 GPLv2 or later
 * WC requires at least:    3.0.0
 * WC tested up to:         3.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_BusinessPay {
	const VERSION = '1.1.1';
	const VERSION_MIN_PHP = '5.4';
	const VERSION_MIN_WP = '4.7';
	const VERSION_MIN_WOO = '3.0';
	const PLUGIN_NAME = 'Woocommerce BusinessPay';
	const TEXT_DOMAIN = 'woocommerce-businesspay';

	protected static $instance = null;

	public function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		if ( $this->check_dependency() ) {
			include_once 'includes/woocommerce-businesspay-gateway.php';
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_businesspay_gateway' ) );
		}
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function check_dependency() {
		$error_num       = 0;
		$use_visual_card = true;

		if ( version_compare( self::get_php_version(), WC_BusinessPay::VERSION_MIN_PHP, '<' ) ) {
			++ $error_num;
			add_action( 'admin_notices', array( $this, 'notice_php' ) );
		} else {
			if ( ! class_exists( 'WooCommerce' ) ) {
				++ $error_num;
				add_action( 'admin_notices', array( $this, 'notice_woocommerce' ) );
			} elseif ( version_compare( $this->get_woo_version(), WC_BusinessPay::VERSION_MIN_WOO, '<' ) ) {
				++ $error_num;
				add_action( 'admin_notices', array( $this, 'notice_woocommerce_version' ) );
			}

			if ( $use_visual_card && ! class_exists( 'WC_VisualCard' ) ) {
				++ $error_num;
				add_action( 'admin_notices', array( $this, 'notice_visual_card' ) );
			}

			if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
				++ $error_num;
				add_action( 'admin_notices', array( $this, 'notice_ecfb' ) );
			}

			if ( version_compare( get_bloginfo( 'version' ), WC_BusinessPay::VERSION_MIN_WP, '<' ) ) {
				++ $error_num;
				add_action( 'admin_notices', array( $this, 'notice_wp' ) );
			}

			if ( ! is_ssl() ) {
				++ $error_num;
				add_action( 'admin_notices', array( $this, 'notice_ssl' ) );
			}
		}

		return $error_num == 0;
	}

	public function add_businesspay_gateway( $methods ) {
		array_unshift( $methods, 'WC_BusinessPay_Gateway' );

		return $methods;
	}


	//NOTICES

	public function notice_php() {
		include 'templates/missing-php-template.php';
	}

	public function notice_woocommerce() {
		include 'templates/missing-woocommerce-template.php';
	}

	public function notice_woocommerce_version() {
		include 'templates/missing-woocommerce-version-template.php';
	}

	public function notice_visual_card() {
		include 'templates/missing-visual-card-template.php';
	}

	public function notice_ecfb() {
		include 'templates/missing-ecfb-template.php';
	}

	public function notice_wp() {
		include 'templates/missing-wp-template.php';
	}

	public function notice_ssl() {
		include 'templates/missing-ssl-template.php';
	}


	//FUNCTIONS

	public static function get_templates_path() {
		return plugin_dir_path( __FILE__ ) . 'templates/';
	}

	public static function get_php_version() {
		$version = explode( '.', PHP_VERSION );

		return $version[0] . '.' . $version[1];
	}

	public static function get_woo_version() {
		global $woocommerce;

		return $woocommerce->version;
	}

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}

add_action( 'plugins_loaded', array( 'WC_BusinessPay', 'get_instance' ) );