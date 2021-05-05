<?php
/**
 * The plugin's main functions.
 *
 * * Record payments.
 * * Determine which gateway to show customers based on recorded payments.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class API
 *
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\API
 */
class API implements API_Interface {

	use LoggerAwareTrait;

	const RECENT_VALUES_OPTION_NAME = 'bh_wc_load_balancer_gateways_running_totals';

	/**
	 * Used to get the time-period the orders' totals should be counted against.
	 *
	 * @var Settings_Interface The plugin's settings.
	 */
	protected Settings_Interface $settings;

	/**
	 * API constructor.
	 *
	 * @param Settings_Interface $settings The plugin's settings.
	 * @param LoggerInterface    $logger PSR logger.
	 */
	public function __construct( Settings_Interface $settings, LoggerInterface $logger ) {
		$this->setLogger( $logger );
		$this->settings = $settings;
	}

	/**
	 * Record the payment for the gateway.
	 *
	 * Deletes expired records from the list of saved payments.
	 *
	 * @param string $payment_method_id The gateway id.
	 * @param float  $payment_amount The paid order amount.
	 */
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
	 * Get a saved list of {time:{gateway: amount}} for summing.
	 *
	 * @return array<int, array{gateway_id:string, amount: float}>
	 */
	protected function get_saved_order_amounts(): array {

		$current = get_option( self::RECENT_VALUES_OPTION_NAME, array() );

		return $current;
	}

	/**
	 * Return an array of arrays, containing the recorded stats for each gateway id in the past time period.
	 *
	 * @used-by API::get_existing_totals()
	 * @used-by Payment_Gateways_UI::print_bh_wc_gateway_load_balancer_setting()
	 *
	 * @see Settings::get_period()
	 *
	 * @param int $since_time Period of time in seconds to fetch saved recent totals. e.g. HOUR_IN_SECONDS or 3600. Up to the max returned from Settings::get_period().
	 * @return array<string, array{count: int, total: float, oldest_time: int}> Keyed by gateway_id.
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
	 * Give a list of payment gateways and the ratio they should be used in, this function gets their recent
	 * orders' totals and determines which gateway should be shown to the customer.
	 *
	 * @param array<string, int> $available_gateway_ratios The gateway id and ratio it should be used.
	 * @return string The chosen gateway id.
	 * @throws \Exception When no gateways are provided, none can be returned.
	 */
	public function determine_chosen_gateway( array $available_gateway_ratios ): string {

		if ( 0 === count( $available_gateway_ratios ) ) {
			throw new \Exception( 'No gateways passed to function.' );
		}

		if ( 1 === count( $available_gateway_ratios ) ) {
			return array_key_first( $available_gateway_ratios );
		}

		$since_time = time() - $this->settings->get_period();

		$totals = $this->get_existing_totals( $since_time );

		$ratios_total = array_sum( $available_gateway_ratios );

		$adjusted_totals = array();

		foreach ( $available_gateway_ratios as $gateway_id => $ratio ) {
			if ( ! isset( $totals[ $gateway_id ] ) ) {
				$totals[ $gateway_id ] = 0.0;
			}

			$adjusted_totals[ $gateway_id ] = $totals[ $gateway_id ] * $ratios_total / $ratio;
		}

		asort( $adjusted_totals, SORT_NUMERIC );

		return array_key_first( $adjusted_totals );

	}
}
