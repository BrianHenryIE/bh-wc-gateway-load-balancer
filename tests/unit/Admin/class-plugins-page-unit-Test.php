<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\Admin;

use Codeception\Test\Unit;

/**
 * Class Plugins_Page_Unit_Test
 *
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\Admin
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\Admin\Plugins_Page
 */
class Plugins_Page_Unit_Test extends Unit {

	/**
	 * When the settings actions link is added, but WooCommerce is absent, just return without adding anything.
	 *
	 * @covers ::action_links
	 */
	public function test_dont_add_settings_when_woocommerce_absent() {

		$sut = new Plugins_Page();

		$result = $sut->action_links( array() );

		$this->assertEmpty( $result );

	}

}
