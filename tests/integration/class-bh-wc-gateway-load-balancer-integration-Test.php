<?php
/**
 * Class Plugin_Test. Tests the root plugin setup.
 *
 * @package BH_WC_Gateway_Load_Balancer
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API;
use BrianHenryIE\WC_Gateway_Load_Balancer\Includes\BH_WC_Gateway_Load_Balancer;

/**
 * Verifies the plugin has been instantiated and added to PHP's $GLOBALS variable.
 */
class Plugin_Integration_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Test the main plugin object is added to PHP's GLOBALS and that it is the correct class.
	 */
	public function test_plugin_instantiated() {

		$this->assertArrayHasKey( 'bh_wc_gateway_load_balancer', $GLOBALS );

		$this->assertInstanceOf( API::class, $GLOBALS['bh_wc_gateway_load_balancer'] );
	}

}
