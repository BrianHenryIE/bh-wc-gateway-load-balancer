<?php
/**
 * Tests for the root plugin file.
 *
 * @package BH_WC_Gateway_Load_Balancer
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API;
use BrianHenryIE\WC_Gateway_Load_Balancer\Includes\BH_WC_Gateway_Load_Balancer;

/**
 * Class Plugin_WP_Mock_Test
 *
 * @coversNothing
 */
class Plugin_Unit_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	// This is required for `'times' => 1` to be verified.
	protected function _tearDown() {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}
	
	/**
	 * Verifies the plugin initialization.
	 */
	public function test_plugin_include() {

        global $plugin_root_dir;

        \WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'register_activation_hook'
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook'
		);

        \WP_Mock::userFunction(
            'is_admin',
            array(
                'return_arg' => false
            )
        );

        \WP_Mock::userFunction(
            'get_current_user_id'
        );

        \WP_Mock::userFunction(
            'wp_normalize_path',
            array(
                'return_arg' => true
            )
        );

		require_once $plugin_root_dir . '/bh-wc-gateway-load-balancer.php';

		$this->assertArrayHasKey( 'bh_wc_gateway_load_balancer', $GLOBALS );

		$this->assertInstanceOf( API::class, $GLOBALS['bh_wc_gateway_load_balancer'] );

	}


	/**
	 * Verifies the plugin does not output anything to screen.
	 */
	public function test_plugin_include_no_output() {

	    global $plugin_root_dir;

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'register_activation_hook'
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook'
		);

        \WP_Mock::userFunction(
            'is_admin',
            array(
                'return_arg' => false
            )
        );

        \WP_Mock::userFunction(
            'get_current_user_id'
        );

        \WP_Mock::userFunction(
            'wp_normalize_path',
            array(
                'return_arg' => true
            )
        );

		ob_start();

		require_once $plugin_root_dir . '/bh-wc-gateway-load-balancer.php';

		$printed_output = ob_get_contents();

		ob_end_clean();

		$this->assertEmpty( $printed_output );

	}

}
