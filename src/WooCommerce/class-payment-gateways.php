<?php
/**
 * When the list of gateways is requested, show available/enabled based on the recorded running totals window.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\Psr\Log\LoggerAwareTrait;
use BrianHenryIE\WC_Gateway_Load_Balancer\Psr\Log\LoggerInterface;

class Payment_Gateways {

	use LoggerAwareTrait;

	protected Settings_Interface $settings;

	protected API_Interface $api;

	/**
	 * Order constructor.
	 *
	 * @param API_Interface      $api Main plugin functions.
	 * @param Settings_Interface $settings Plugin settings.
	 * @param LoggerInterface    $logger PSR logger.
	 */
	public function __construct( API_Interface $api, Settings_Interface $settings, LoggerInterface $logger ) {
		$this->setLogger( $logger );
		$this->settings = $settings;
		$this->api      = $api;
	}

	/**
	 * Given a list of gateways, checks for any set to load balance, checks which gateways should be removed.
	 *
	 * TODO: Where should this run? Checkout... anywhere else? Is order payment page 'checkout' too?
	 *
	 * @hooked woocommerce_available_payment_gateways
	 * @see \WC_Payment_Gateways::get_available_payment_gateways()
	 *
	 * @param array<string, \WC_Payment_Gateway> $available_gateways The payment gateways already determined as available by WooCommerce.
	 *
	 * @return array<string, \WC_Payment_Gateway> Filtered $available_gateways.
	 */
	public function load_balance_gateways( array $available_gateways ): array {

		if ( ! is_checkout() ) {

			$this->logger->debug( 'Not on checkout, returning.' );

			return $available_gateways;
		}

		$gateways_proportion_config = $this->settings->get_load_balance_config();

		/**
		 * Intersection of available gateways and gateways in load balance config.
		 *
		 * @var array<string, int> $available_gateways_to_balance
		 */
		$available_gateways_to_balance = array();

		foreach ( $gateways_proportion_config as $gateway_id => $proportion ) {
			if ( isset( $available_gateways[ $gateway_id ] ) ) {
				$available_gateways_to_balance[ $gateway_id ] = $proportion;
			}
		}

		// If there is nothing to balance, just return.
		if ( count( $available_gateways_to_balance ) < 2 ) {
			return $available_gateways;
		}

		// We have at least two gateways and we only want one to appear.

		$chosen_gateway_id = $this->api->determine_chosen_gateway( $available_gateways_to_balance );

		$gateways_to_remove = $available_gateways_to_balance;
		// Don't remove the chosen one!
		unset( $gateways_to_remove[ $chosen_gateway_id ] );

		$gateways_to_remove = array_keys( $gateways_to_remove );

		// Remove the remainder.
		foreach ( $gateways_to_remove as $gateway_to_remove ) {
			unset( $available_gateways[ $gateway_to_remove ] );
		}

		$this->logger->info(
			'Load balanced',
			array(
				'chosen_gateway'   => $chosen_gateway_id,
				'removed_gateways' => $gateways_to_remove,
			)
		);

		return $available_gateways;
	}
}
