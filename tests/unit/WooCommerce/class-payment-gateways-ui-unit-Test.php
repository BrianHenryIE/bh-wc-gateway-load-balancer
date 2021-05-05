<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use Psr\Log\NullLogger;
use Codeception\Test\Unit;

/**
 * Class Payment_Gateways_UI_Unit_Test
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Payment_Gateways_UI
 */
class Payment_Gateways_UI_Unit_Test extends Unit {

    protected function _before() {
        \WP_Mock::setUp();
    }

    protected function _tearDown() {
        parent::_tearDown();
        \WP_Mock::tearDown();
    }

    /**
     * @throws \Exception
     *
     * @covers ::add_settings_section
     */
    public function test_register_section() {

        $api = $this->makeEmpty( API_Interface::class );
        $settings = $this->makeEmpty( Settings_Interface::class );
        $logger = new NullLogger();

        $sut = new Payment_Gateways_UI( $api, $settings, $logger );

        $sections = array();

        $result = $sut->add_settings_section( $sections );

        $this->assertContains( 'Load Balancing', $result );
        $this->assertArrayHasKey( 'bh_wc_gateway_load_balancer', $result );
    }


    public function test_add_js() {

        $version = '1.0.0';

        $api = $this->makeEmpty( API_Interface::class );

        $settings = $this->makeEmpty( Settings_Interface::class,
        array(
           'get_plugin_version' => $version
        ));

        $logger = new NullLogger();

        \WP_Mock::userFunction(
            'wp_register_script',
            array(
                'args'   => array( 'bh-wc-gateway-load-balancer', '', array( 'jquery' ), $version, true )
            )
        );

        \WP_Mock::userFunction(
            'wp_enqueue_script',
            array(
                'args'   => array( 'bh-wc-gateway-load-balancer' )
            )
        );

        \WP_Mock::userFunction(
            'wp_add_inline_script',
            array(
                'args'   => array( 'bh-wc-gateway-load-balancer', \WP_Mock\Functions::type( 'string' ) )
            )
        );

        $sut = new Payment_Gateways_UI( $api, $settings, $logger );

        $sut->add_checkbox_js();
    }
}