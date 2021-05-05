<?php
/**
 * OO interface to the plugin's settings.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

interface Settings_Interface {

	/**
	 * Returns the configuration to be used when determining which gateway to display. i.e. the settings entered
	 * in the WooCommerce / Payments / Load Balancing UI.
	 *
	 * @return array<string, int>
	 */
	public function get_load_balance_config(): array;

	/**
	 * The plugin version is used in logs and sometimes for caching purposes.
	 *
	 * @return string
	 */
	public function get_plugin_version(): string;

	/**
	 * Get the time period in seconds to check the orders' totals against.
	 * e.g. DAY_IN_SECONDS.
	 *
	 * @return int
	 */
	public function get_period(): int;
}
