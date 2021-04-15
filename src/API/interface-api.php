<?php
/**
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

interface API_Interface {

	/**
	 * @param string $payment_method_id
	 * @param float  $payment_amount
	 */
	public function record_payment( string $payment_method_id, float $payment_amount ): void;

	/**
	 * Checks gateways totals for recent orders (one day) and chooses which gateway should be used.
	 *
	 * @param array<string, int> $available_gateways The gateway id and ratio it should be used.
	 * @return string The chosen gateway id.
	 */
	public function determine_chosen_gateway( array $available_gateways ): string;
}
