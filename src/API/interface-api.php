<?php
/**
 * Set of functions that are central to the plugin's operation.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

/**
 * Interface API_Interface
 *
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\API
 */
interface API_Interface {

	/**
	 * Function to record all payments for all gateways.
	 *
	 * @used-by Order::update_gateways_running_totals()
	 *
	 * @param string $payment_method_id The payment gateway id used in the order.
	 * @param float  $payment_amount The order total to record.
	 */
	public function record_payment( string $payment_method_id, float $payment_amount ): void;

	/**
	 * Checks gateways totals for recent orders (one day) and chooses which gateway should be used, using the ratios supplied.
	 *
	 * @param array<string, int> $available_gateway_ratios The gateway id and ratio it should be used.
	 * @return string The chosen gateway id.
	 */
	public function determine_chosen_gateway( array $available_gateway_ratios ): string;

	/**
	 * Get recently recorded statistics for all gateways.
	 *
	 * This data is used internally in API to calculate the load balancing.
	 * It is made public for display on the settings screen.
	 *
	 * @param int $since_time The unix time to count from. This must be more recent that Settings::get_period() which sets the expiry time for records.
	 * @return array<string, array{count: int, total: float, oldest_time: int}> Keyed by gateway_id.
	 */
	public function get_recent_totals_stats( int $since_time ): array;

}
