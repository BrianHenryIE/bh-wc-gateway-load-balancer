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
				'return' => array( 'ratio' => array() ),
			)
		);

		$sut = new Settings();

		$result = $sut->get_load_balance_config();

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	public function test_plugin_name() {

		$sut = new Settings();

		$result = $sut->get_plugin_name();

		$this->assertEquals( 'Gateway Load Balancer', $result );
	}

	public function test_get_plugin_slug() {

		$sut = new Settings();

		$result = $sut->get_plugin_slug();

		$this->assertEquals( 'bh-wc-gateway-load-balancer', $result );
	}

	public function test_get_loglevel_returns_notice_default() {

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'       => array( 'bh_wc_gateway_load_balancer_log_level', \WP_Mock\Functions::type( 'string' ) ),
				'return_arg' => 1,
			)
		);

		$sut = new Settings();

		$result = $sut->get_log_level();

		$this->assertEquals( 'notice', $result );
	}

	public function test_get_loglevel_returns_option_when_specified() {

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'bh_wc_gateway_load_balancer_log_level', \WP_Mock\Functions::type( 'string' ) ),
				'return' => 'debug',
			)
		);

		$sut = new Settings();

		$result = $sut->get_log_level();

		$this->assertEquals( 'debug', $result );
	}
}
