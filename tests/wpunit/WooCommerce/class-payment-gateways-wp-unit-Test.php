<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use Psr\Log\NullLogger;
use Codeception\Stub\Expected;
use Codeception\TestCase\WPTestCase;

/**
 *
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Payment_Gateways
 */
class Payment_Gateways_WP_Unit_Test extends WPTestCase {


	/**
	 * @throws \Exception
	 *
	 * @covers ::load_balance_gateways
	 */
	public function test_load_balance_gateways() {

		$api = $this->makeEmpty(
			API_Interface::class,
			array(
				'determine_chosen_gateway' => 'gateway_2',
			)
		);

		$load_balance_config = array(
			'gateway_2' => 6,
			'gateway_3' => 4,
		);
		$settings            = $this->makeEmpty(
			Settings_Interface::class,
			array(
				'get_load_balance_config' => $load_balance_config,
			)
		);

		$logger = new NullLogger();

		$sut = new Payment_Gateways( $api, $settings, $logger );

		$available_gateways = array(
			'gateway_1' => $this->make( \WC_Payment_Gateway::class ),
			'gateway_2' => $this->make( \WC_Payment_Gateway::class ),
			'gateway_3' => $this->make( \WC_Payment_Gateway::class ),
		);

		// Get is_checkout to return true.
		add_filter( 'woocommerce_is_checkout', '__return_true' );

		$result = $sut->load_balance_gateways( $available_gateways );

		$this->assertArrayHasKey( 'gateway_1', $result );
		$this->assertArrayHasKey( 'gateway_2', $result );
		$this->assertArrayNotHasKey( 'gateway_3', $result );

	}

	/**
	 * When not on checkout, whatever is passed in should be returned unchanged.
	 *
	 * @covers ::load_balance_gateways
	 */
	public function test_load_balance_gateways_not_on_checkout() {

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		$logger   = new NullLogger();

		$sut = new Payment_Gateways( $api, $settings, $logger );

		$available_gateways = array(
			'gateway_1' => $this->make( \WC_Payment_Gateway::class ),
			'gateway_2' => $this->make( \WC_Payment_Gateway::class ),
		);

		$result = $sut->load_balance_gateways( $available_gateways );

		$this->assertArrayHasKey( 'gateway_1', $result );
		$this->assertArrayHasKey( 'gateway_2', $result );

	}



	/**
	 * When only one gateways is available, just return it.
	 *
	 * @covers ::load_balance_gateways
	 */
	public function test_load_balance_only_one_gateway() {

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		$logger   = new NullLogger();

		$sut = new Payment_Gateways( $api, $settings, $logger );

		$available_gateways = array(
			'gateway_1' => $this->make( \WC_Payment_Gateway::class ),
		);

		// Get is_checkout to return true.
		add_filter( 'woocommerce_is_checkout', '__return_true' );

		$result = $sut->load_balance_gateways( $available_gateways );

		$this->assertArrayHasKey( 'gateway_1', $result );
	}

	/**
	 * @throws \Exception
	 *
	 * @covers ::__construct
	 */
	public function test_construct() {

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		$logger   = new NullLogger();

		$sut = new Payment_Gateways( $api, $settings, $logger );

		$this->assertInstanceOf( Payment_Gateways::class, $sut );

	}


}
