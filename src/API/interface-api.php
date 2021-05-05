<?php
/**
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
}
