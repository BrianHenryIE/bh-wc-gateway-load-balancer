<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use Codeception\TestCase\WPTestCase;
use Psr\Log\NullLogger;

/**
 * Class Order_WP_Unit_Test
 *
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Order
 */
class Order_WP_Unit_Test extends WPTestCase {

	/**
	 * @covers ::update_running_totals_on_payment_complete
	 */
	public function test_order_paid() {

		$order = new \WC_Order();
		$order->set_total( 100.00 );
		$order->set_payment_method( 'gateway_1' );
		$order_id = $order->save();

		$api_mock = $this->createMock( API_Interface::class );
		$api_mock->expects( $this->once() )
			->method( 'record_payment' )
			->with( 'gateway_1', 100 );

		$settings_mock = $this->makeEmpty( Settings_Interface::class );

		$logger = new NullLogger();

		$sut = new Order( $api_mock, $settings_mock, $logger );

		$sut->update_running_totals_on_payment_complete( $order_id );

		// $this->assertTrue( false );

		// $this->markTestIncomplete('Passed even with wrong params');
	}


	/**
	 * @covers ::update_running_totals_on_payment_complete
	 * @doesNotPerformAssertions
	 */
	public function test_invalid_order_returns_early() {

		$api    = $this->makeEmpty( API_Interface::class );
        $settings_mock = $this->makeEmpty( Settings_Interface::class );
		$logger = new NullLogger();

		$sut = new Order( $api, $settings_mock, $logger );

		$exception = null;
		try {
			$sut->update_running_totals_on_payment_complete( 123 );
		} catch ( \Exception $e ) {
			$exception = $e;
		}

		$this->assertNull( $exception );

	}

	/**
	 * When an order status is changed to a paid status, from an unpaid status, record it.
	 */
	public function test_order_status_changed_happy() {

		$order = new \WC_Order();
		$order->set_total( 100.00 );
		$order->set_payment_method( 'gateway_1' );
		$order_id = $order->save();

		$api_mock = \Mockery::mock( API_Interface::class );
		$api_mock->shouldReceive( 'record_payment' )
			->with( 'gateway_1', 1100.00 )->once();
        $settings_mock = $this->makeEmpty( Settings_Interface::class );
		$logger = new NullLogger();

		$sut = new Order( $api_mock, $settings_mock, $logger );

		$paid_status       = array_rand( wc_get_is_paid_statuses() );
		$not_paid_statuses = array_rand( array_diff( wc_get_order_statuses(), wc_get_is_paid_statuses() ) );

		$sut->update_running_totals_on_status_changed( $order_id, $not_paid_statuses, $paid_status, $order );

		$this->markTestIncomplete( 'Passed even with wrong params' );

	}

	/**
	 * When an order status is changed from a paid status to a unpaid status, do nothing.
	 */
	public function test_order_status_changed_never() {

		$order = new \WC_Order();
		$order->set_total( 100.00 );
		$order->set_payment_method( 'gateway_1' );
		$order_id = $order->save();

		$api_mock = \Mockery::mock( API_Interface::class );
		$api_mock->shouldReceive( 'record_payment' )->once();
        $settings_mock = $this->makeEmpty( Settings_Interface::class );
		$logger = new NullLogger();

		$sut = new Order( $api_mock, $settings_mock, $logger );

		$paid_status_1 = array_rand( wc_get_is_paid_statuses() );
		$paid_status_2 = array_rand( wc_get_is_paid_statuses() );

		$sut->update_running_totals_on_status_changed( $order_id, $paid_status_1, $paid_status_2, $order );
	}

}
