<?php
/**
 *
 *
 * @package BH_WC_Gateway_Load_Balancer
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\Includes;

/**
 * Class Plugin_WP_Mock_Test
 *
 * @coversDefaultClass  \BrianHenryIE\WC_Gateway_Load_Balancer\Includes\I18n
 */
class I18n_Unit_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	protected function _tearDown() {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * Verify load_plugin_textdomain is correctly called.
	 *
	 * @covers ::load_plugin_textdomain
	 */
	public function test_load_plugin_textdomain() {

		global $plugin_root_dir;

		\WP_Mock::userFunction(
			'load_plugin_textdomain',
			array(
				'args' => array(
					'bh-wc-gateway-load-balancer',
					false,
					$plugin_root_dir . '/languages/',
				),
			)
		);
	}
}
