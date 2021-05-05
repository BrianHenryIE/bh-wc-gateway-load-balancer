<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * frontend-facing side of the site and the admin area.
 *
 * @link       https://BrianHenryIE.com
 * @since      1.0.0
 *
 * @package    BH_WC_Gateway_Load_Balancer
 * @subpackage BH_WC_Gateway_Load_Balancer/includes
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\Includes;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Order;
use BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Payment_Gateways;
use BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Payment_Gateways_UI;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * frontend-facing site hooks.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */
class BH_WC_Gateway_Load_Balancer {

	use LoggerAwareTrait;

	/**
	 * The settings instance to pass to new objects.
	 *
	 * @var Settings_Interface
	 */
	protected Settings_Interface $settings;

	/**
	 * The API instance to pass to new objects.
	 *
	 * @var API_Interface
	 */
	protected API_Interface $api;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the frontend-facing side of the site.
	 *
	 * @since    1.0.0
	 *
	 * @param API_Interface      $api The main plugin functions.
	 * @param Settings_Interface $settings The plugin's settings.
	 * @param LoggerInterface    $logger PSR logger.
	 */
	public function __construct( API_Interface $api, Settings_Interface $settings, LoggerInterface $logger ) {

		$this->setLogger( $logger );
		$this->settings = $settings;
		$this->api      = $api;

		$this->set_locale();

		$this->define_order_hooks();
		$this->define_payment_gateway_hooks();
		$this->define_payment_gateway_ui_hooks();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function set_locale(): void {

		$plugin_i18n = new I18n();

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register actions to record order values by gateway when orders are paid.
	 *
	 * @since    1.0.0
	 */
	protected function define_order_hooks(): void {

		$order = new Order( $this->api, $this->logger );

		add_action( 'woocommerce_payment_complete', array( $order, 'update_gateways_running_totals' ) );
	}

	/**
	 * Payment gateway hooks: filter the payment gateways based on the configured settings.
	 *
	 * @since    1.0.0
	 */
	protected function define_payment_gateway_hooks(): void {

		$payment_gateways = new Payment_Gateways( $this->api, $this->settings, $this->logger );

		add_filter( 'woocommerce_available_payment_gateways', array( $payment_gateways, 'load_balance_gateways' ), 200, 1 );
	}

	/**
	 * Register the hooks and filters for the settings screen.
	 *
	 * @since    1.0.0
	 */
	protected function define_payment_gateway_ui_hooks(): void {

		$payment_gateways_ui = new Payment_Gateways_UI( $this->settings, $this->logger );

		add_filter( 'woocommerce_get_sections_checkout', array( $payment_gateways_ui, 'add_settings_section' ) );
		add_filter( 'woocommerce_get_settings_checkout', array( $payment_gateways_ui, 'get_settings' ), 10, 2 );
		add_action( 'woocommerce_admin_field_bh_wc_gateway_load_balancer', array( $payment_gateways_ui, 'print_bh_wc_gateway_load_balancer_setting' ) );
		add_filter( 'woocommerce_admin_settings_sanitize_option_bh_wc_gateway_load_balancer_config', array( $payment_gateways_ui, 'process_config' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $payment_gateways_ui, 'add_checkbox_js' ) );
	}

}
