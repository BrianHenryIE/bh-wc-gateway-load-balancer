<?php
/**
 * When orders are marked paid, add their statistics to the running total window.
 *
 * TODO: Check and double check this hook is better than order status changed hook.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use WC_Order;

/**
 * Class Order
 *
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce
 */
class Order {

	const ORDER_RECORDED_META_KEY = 'bh_wc_gateway_load_balancer_recorded_time';

	use LoggerAwareTrait;

	/**
	 * Used to decide if every order should be counted, or only "paid" orders.
	 *
	 * @var Settings_Interface The plugin's settings.
	 */
	protected Settings_Interface $settings;

	/**
	 * Payment is recorded by API class.
	 *
	 * @var API_Interface Plugin API class.
	 */
	protected API_Interface $api;

	/**
	 * Order constructor.
	 *
	 * @param API_Interface      $api Plugin API class.
	 * @param Settings_Interface $settings The plugin settings.
	 * @param LoggerInterface    $logger PSR Logger.
	 */
	public function __construct( API_Interface $api, Settings_Interface $settings, LoggerInterface $logger ) {
		$this->setLogger( $logger );
		$this->settings = $settings;
		$this->api      = $api;
	}

	/**
	 * When a new order is new, if its status indicates it has been paid, record it against the gateway running totals.
	 *
	 * @hooked woocommerce_new_order
	 *
	 * @param int      $order_id The order id.
	 * @param WC_Order $_order The WooCommerce order object.
	 */
	public function update_running_totals_on_new_order( int $order_id, WC_Order $_order ): void {

		// Use wc_get_order() to get the freshest version of the order.
		$order = wc_get_order( $order_id );

		if ( ! ( $order instanceof WC_Order ) ) {
			// Almost definitely not going to happen.
			$this->logger->debug( "Order {$order_id} not found", array( 'order_id' => $order_id ) );
			return;
		}

		// If we've already recorded the payment, return.
		if ( ! empty( $order->get_meta( self::ORDER_RECORDED_META_KEY ) ) ) {
			return;
		}

		if ( ! $this->settings->get_should_count_all_new_orders() && ! in_array( $order->get_status(), wc_get_is_paid_statuses(), true ) ) {
			return;
		}

		$payment_method_id = $order->get_payment_method();
		$payment_amount    = floatval( $order->get_total() ); // Typecast is not redundant.

		$this->logger->info(
			"Recording payment amount {$payment_amount} for gateway {$payment_method_id}",
			array(
				'hook',
				'woocommerce_new_order',
				'status' => $order->get_status(),
			)
		);

		$this->api->record_payment( $payment_method_id, $payment_amount );

		$order->add_meta_data( self::ORDER_RECORDED_META_KEY, array( 'time' => time() ), true );
		$order->save();
	}

	/**
	 * When an order is marked paid, record the total.
	 *
	 * @hooked woocommerce_payment_complete
	 * @see WC_Order::payment_complete()
	 *
	 * @param int $order_id The id of the order that has been paid.
	 */
	public function update_running_totals_on_payment_complete( int $order_id ): void {

		$order = wc_get_order( $order_id );

		if ( ! ( $order instanceof WC_Order ) ) {

			$this->logger->debug( "Order {$order_id} not found", array( 'order_id' => $order_id ) );
			return;
		}

		// If we've already recorded the payment, return.
		if ( ! empty( $order->get_meta( self::ORDER_RECORDED_META_KEY ) ) ) {
			return;
		}

		$payment_method_id = $order->get_payment_method();
		$payment_amount    = floatval( $order->get_total() ); // Typecast is not redundant.

		$this->logger->info( "Recording payment amount {$payment_amount} for gateway {$payment_method_id}", array( 'hook' => 'woocommerce_payment_complete' ) );

		$this->api->record_payment( $payment_method_id, $payment_amount );

		$order->add_meta_data( self::ORDER_RECORDED_META_KEY, array( 'time' => time() ), true );
		$order->save();
	}

	/**
	 * When an order status changes, if it is from an unpaid status to a paid status, record the payment against the gateway.
	 *
	 * @hooked woocommerce_order_status_changed
	 * @see WC_Order::status_transition()
	 *
	 * @param int      $order_id The order id.
	 * @param string   $status_from The previous status.
	 * @param string   $status_to The new status.
	 * @param WC_Order $_order The WooCommerce order object.
	 */
	public function update_running_totals_on_status_changed( int $order_id, string $status_from, string $status_to, WC_Order $_order ): void {

		// Use wc_get_order() to get the freshest version of the order.
		$order = wc_get_order( $order_id );

		if ( ! ( $order instanceof WC_Order ) ) {
			// Almost definitely not going to happen.
			$this->logger->debug( "Order {$order_id} not found", array( 'order_id' => $order_id ) );
			return;
		}

		$is_paid_statuses = wc_get_is_paid_statuses();

		if ( ( ! in_array( $status_from, $is_paid_statuses, true )
				&& in_array( $status_to, $is_paid_statuses, true ) )
			|| $this->settings->get_should_count_all_new_orders() ) {

			// The order has just been paid.

			// If we've already recorded the payment, return.
			if ( ! empty( $order->get_meta( self::ORDER_RECORDED_META_KEY ) ) ) {
				return;
			}

			$payment_method_id = $order->get_payment_method();
			$payment_amount    = floatval( $order->get_total() ); // Typecast is not redundant.

			$this->logger->info( "Recording payment amount {$payment_amount} for gateway {$payment_method_id}", array( 'hook', 'woocommerce_order_status_changed' ) );

			$this->api->record_payment( $payment_method_id, $payment_amount );

			$order->add_meta_data( self::ORDER_RECORDED_META_KEY, array( 'time' => time() ), true );
			$order->save();
		}
	}

}

