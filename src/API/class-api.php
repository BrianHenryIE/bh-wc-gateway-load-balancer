<?php
/**
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

use BrianHenryIE\WC_Gateway_Load_Balancer\Psr\Log\LoggerAwareTrait;
use BrianHenryIE\WC_Gateway_Load_Balancer\Psr\Log\LoggerInterface;

class API implements API_Interface {

	use LoggerAwareTrait;

	const RECENT_VALUES_OPTION_NAME = 'bh_wc_load_balancer_gateways_running_totals';

	protected Settings_Interface $settings;

	public function __construct( Settings_Interface $settings, LoggerInterface $logger ) {
		$this->setLogger( $logger );
		$this->settings = $settings;
	}

	public function record_payment( string $payment_method_id, float $payment_amount ): void {

		$since_time = time() - $this->settings->get_period();

		$existing = $this->get_saved_order_amounts();

		$filtered_since_time = array_filter(
			$existing,
			function ( $key ) use ( $since_time ) {
				return $key > $since_time;
			},
			ARRAY_FILTER_USE_KEY
		);

		$filtered_since_time[ time() ] = array(
			'gateway_id' => $payment_method_id,
			'amount'     => $payment_amount,
		);

		update_option( self::RECENT_VALUES_OPTION_NAME, $filtered_since_time );

	}

	/**
	 * Get a saved list of time:{gateway, amount}
	 *
	 * @return array<int, array{gateway_id:string, amount: float}>
	 */
	protected function get_saved_order_amounts(): array {

		$current = get_option( self::RECENT_VALUES_OPTION_NAME, array() );

		return $current;
	}

	/**
	 * gateway_id => amount
	 *
	 * @return array<string, float>
	 */
	protected function get_existing_totals( int $since_time ): array {

		$records = $this->get_saved_order_amounts();

		$totals = array();

		foreach ( $records as $recorded_time => $details ) {

			if ( $recorded_time < $since_time ) {
				continue;
			}

			$gateway_id = $details['gateway_id'];

			if ( ! isset( $totals[ $gateway_id ] ) ) {
				$totals[ $gateway_id ] = 0.0;
			}

			$totals[ $gateway_id ] += $details['amount'];

		}

		return $totals;
	}

	/**
	 *
	 * @param array<string, int> $available_gateways The gateway id and proportion it should be used.
	 * @return string The chosen gateway id.
	 */
	public function determine_chosen_gateway( array $available_gateways ): string {

		if ( 0 === count( $available_gateways ) ) {
			throw new \Exception( 'No gateways passed to function.' );
		}

		if ( 1 === count( $available_gateways ) ) {
			return array_key_first( $available_gateways );
		}

		$since_time = time() - $this->settings->get_period();

		$totals = $this->get_existing_totals( $since_time );

		$ratios_total = array_sum( $available_gateways );

		$adjusted_totals = array();

		foreach ( $available_gateways as $gateway_id => $ratio ) {
			if ( ! isset( $totals[ $gateway_id ] ) ) {
				$totals[ $gateway_id ] = 0.0;
			}

			$adjusted_totals[ $gateway_id ] = $totals[ $gateway_id ] * $ratios_total / $ratio;
		}

		asort( $adjusted_totals, SORT_NUMERIC );

		return array_key_first( $adjusted_totals );

	}
}
