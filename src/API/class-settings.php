<?php
/**
 * Plain object for accessing settings. Facade for WooCommerce saved settings.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

use BrianHenryIE\WC_Gateway_Load_Balancer\WP_Logger\API\Logger_Settings_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\WP_Logger\WooCommerce\WooCommerce_Logger_Interface;
use Psr\Log\LogLevel;

/**
 * Class Settings
 *
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\API
 */
class Settings implements Settings_Interface, Logger_Settings_Interface, WooCommerce_Logger_Interface {

	/**
	 * An array with payment gateway ids and the ratio they should be used.
	 *
	 * Returns array of <gateway_id, ratio>.
	 *
	 * @return array<string, int>
	 */
	public function get_load_balance_config(): array {

		$default = array(
			'ratio' => array(), // An empty array of gateway:ratio.
		);
		$config  = get_option( 'bh_wc_gateway_load_balancer_config', $default );

		return $config['ratio'];

	}

	/**
	 * How detailed logs should be.
	 *
	 * TODO: Add to settings.
	 *
	 * @see LogLevel
	 *
	 * @return string
	 */
	public function get_log_level(): string {
		$default = LogLevel::NOTICE;
		$config  = get_option( 'bh_wc_gateway_load_balancer_log_level', $default );

		return $config;
	}

	/**
	 * Plugin name for use by the logger in friendly messages printed to WordPress admin UI.
	 *
	 * @return string
	 * @see Logger
	 */
	public function get_plugin_name(): string {
		return 'Gateway Load Balancer';
	}

	/**
	 * The plugin slug is used by the logger in file and URL paths.
	 *
	 * @return string
	 */
	public function get_plugin_slug(): string {
		return 'bh-wc-gateway-load-balancer';
	}

	/**
	 * The plugin basename is used by the logger to add the plugins page action link.
	 * (and maybe for PHP errors)
	 *
	 * @return string
	 * @see Logger
	 */
	public function get_plugin_basename(): string {
		return 'bh-wc-gateway-load-balancer/bh-wc-gateway-load-balancer.php';
	}

	/**
	 * Plugin version for use in enqueuing (caching) JS/CSS.
	 *
	 * @return string Semver version.
	 */
	public function get_plugin_version(): string {
		return '1.3.1';
	}

	/**
	 * Get the time period in seconds to check the orders' totals against.
	 * e.g. DAY_IN_SECONDS.
	 *
	 * @return int
	 */
	public function get_period(): int {
		return DAY_IN_SECONDS;
	}

	/**
	 * Toogles whether to only consider "paid" orders, or to count every order created.
	 *
	 * @return bool
	 */
	public function get_should_count_all_new_orders(): bool {

		$include = get_option( 'bh_wc_gateway_load_balancer_should_count_all_new_orders', 'no' ) === 'yes';

		return $include;
	}
}
