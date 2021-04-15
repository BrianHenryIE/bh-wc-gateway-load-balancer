<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

use Codeception\Test\Unit;

class Settings_Unit_Test extends Unit {

    protected function _before() {
        \WP_Mock::setUp();
    }

    protected function _tearDown() {
        parent::_tearDown();
        \WP_Mock::tearDown();
    }

    /**
     * When no config has been saved, return an empty array.
     */
    public function test_get_empty_config() {

        \WP_Mock::userFunction(
            'get_option',
            array(
                'args'   => array( 'bh_wc_gateway_load_balancer_config', \WP_Mock\Functions::type( 'array' ) ),
                'return' => array('ratio'=>array())
            )
        );

        $sut = new Settings();

        $result = $sut->get_load_balance_config();

        $this->assertIsArray(  $result );
        $this->assertEmpty( $result );
    }
}