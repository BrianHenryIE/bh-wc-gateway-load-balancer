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

	use LoggerAwareTrait;

	/**
	 * Payment is recorded by API class.
	 *
	 * @var API_Interface Plugin API class.
	 */
	protected API_Interface $api;

	/**
	 * Order constructor.
	 *
	 * @param API_Interface   $api Plugin API class.
	 * @param LoggerInterface $logger PSR Logger.
	 */
	public function __construct( API_Interface $api, LoggerInterface $logger ) {
		$this->setLogger( $logger );
		$this->api = $api;
	}

	/**
	 * When an order is marked paid, record the total.
	 *
	 * @hooked woocommerce_payment_complete
	 * @see WC_Order::payment_complete()
	 *
	 * @param int $order_id The id of the order that has been paid.
	 */
	public function update_gateways_running_totals( int $order_id ): void {

		$order = wc_get_order( $order_id );

		if ( ! ( $order instanceof WC_Order ) ) {

			$this->logger->debug( "Order {$order_id} not found", array( 'order_id' => $order_id ) );
			return;
		}

		$payment_method_id = $order->get_payment_method();
		$payment_amount    = floatval( $order->get_total() ); // Typecast is not redundant.

		$this->logger->info( "Recording payment amount {$payment_amount} for gateway {$payment_method_id}" );

		$this->api->record_payment( $payment_method_id, $payment_amount );

	}
}

