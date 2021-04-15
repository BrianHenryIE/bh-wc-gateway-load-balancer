<?php
/**
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

interface Settings_Interface {

	/**
	 * @return array<string, int>
	 */
	public function get_load_balance_config(): array;

	public function get_plugin_version(): string;

	/**
	 * Get the time period in seconds to check the orders' totals against.
	 * e.g. DAY_IN_SECONDS.
	 *
	 * @return int
	 */
	public function get_period(): int;
}
