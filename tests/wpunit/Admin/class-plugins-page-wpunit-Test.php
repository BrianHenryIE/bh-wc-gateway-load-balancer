<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\Admin;


/**
 * Class Plugins_Page_WP_Unit_Test
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\Admin
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\Admin\Plugins_Page
 */
class Plugins_Page_WP_Unit_Test extends \Codeception\TestCase\WPTestCase {

    /**
     * @covers ::action_links
     */
    public function test_add_settings_link() {

        $sut = new Plugins_Page();

        $result = $sut->action_links( array() );

        $this->assertNotEmpty( $result );

        $link = $result[0];

        $this->assertStringContainsString( 'Settings', $link );

    }
}

