<?php

namespace BrianHenryIE\WC_Gateway_Load_Balancer\API;

/**
 * Class Settings_Integration_Test
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\API
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings
 */
class Settings_Integration_Test extends \Codeception\TestCase\WPTestCase {

    /**
     * Verify the version in settings matches the versions in the main plugin file.
     *
     * @covers ::get_plugin_version
     */
    public function test_settings_version() {

        global $plugin_root_dir;

        $settings = new Settings();

        $plugin_data = get_plugin_data( "$plugin_root_dir/{$settings->get_plugin_slug()}.php", false, false );

        $this->assertEquals( $settings->get_plugin_version(), $plugin_data['Version'] );
        $this->assertEquals( BH_WC_GATEWAY_LOAD_BALANCER_VERSION, $plugin_data['Version'] );
        $this->assertEquals( $settings->get_plugin_version(), BH_WC_GATEWAY_LOAD_BALANCER_VERSION );

    }
}
