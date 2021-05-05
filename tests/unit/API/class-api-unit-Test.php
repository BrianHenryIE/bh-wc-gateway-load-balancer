<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

use Psr\Log\NullLogger;
use Codeception\TestCase\WPTestCase;

/**
 * Class Plugin_Unit_Test
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\API
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\API\API
 */
class Plugin_Unit_Test extends \Codeception\Test\Unit {

    /**
     * Both gateways have the same amount processed already.
     * One gateway should be used 40% of the time, the other 60%.
     *
     * The second gateway should be chosen.
     *
     * @covers ::determine_chosen_gateway
     */
    public function test_decide() {

        $settings = $this->makeEmpty( Settings_Interface::class,
        array(
            'get_period' => 60*60*24
        ));

        $logger = new NullLogger();

        $sut = new API( $settings, $logger );

        $recent_values = array(
            time() - 60 => array( 'gateway_id' => 'gateway_1', 'amount' => 100.00 ),
            time() - 120 => array( 'gateway_id' => 'gateway_2', 'amount' => 100.00 )
        );

        \WP_Mock::userFunction(
            'get_option',
            array(
                'args'   => array( API::RECENT_VALUES_OPTION_NAME, \WP_Mock\Functions::type( 'array' ) ),
                'return' => $recent_values
            )
        );

        $available_gateways = array(
            'gateway_1' => 4,
            'gateway_2' => 6
        );


        $chosen_gateway = $sut->determine_chosen_gateway( $available_gateways );

        $this->assertEquals( 'gateway_2', $chosen_gateway );

    }
}