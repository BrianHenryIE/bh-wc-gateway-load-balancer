<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use Psr\Log\NullLogger;
use Codeception\Stub\Expected;
use Codeception\TestCase\WPTestCase;

/**
 * Class Order_WP_Unit_Test
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Order
 */
class Order_WP_Unit_Test extends WPTestCase {

    /**
     * @covers ::update_gateways_running_totals
     */
    public function test_order_paid() {

        $order = new \WC_Order();
        $order->set_total( 100.00 );
        $order->set_payment_method('gateway_1');
        $order_id = $order->save();

        $api = $this->makeEmpty( API_Interface::class,
            array(
                'record_payment' => Expected::once( array( 'gateway_1', 1100.00 ) )
            )
        );
        $logger = new NullLogger();

        $sut = new Order( $api, $logger );

        $sut->update_gateways_running_totals( $order_id );


        $this->markTestIncomplete('Passed even with wrong params');

    }


    /**
     * @covers ::update_gateways_running_totals
     * @doesNotPerformAssertions
     */
    public function test_invalid_order_returns_early() {

        $api = $this->makeEmpty( API_Interface::class );
        $logger = new NullLogger();

        $sut = new Order( $api, $logger );

        $exception = null;
        try {
            $sut->update_gateways_running_totals( 123 );
        } catch ( \Exception $e ) {
            $exception = $e;
        }

        $this->assertNull( $exception );

    }

}
