<?php
/**
 * @package BH_WC_Gateway_Load_Balancer_Unit_Name
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\Includes;

use BrianHenryIE\WC_Gateway_Load_Balancer\Admin\Plugins_Page;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use Psr\Log\NullLogger;
use BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Order;
use BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Payment_Gateways;
use BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce\Payment_Gateways_UI;
use WP_Mock\Matcher\AnyInstance;

/**
 * Class BH_WC_Gateway_Load_Balancer_Unit_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Gateway_Load_Balancer\Includes\BH_WC_Gateway_Load_Balancer
 */
class BH_WC_Gateway_Load_Balancer_Unit_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	protected function _tearDown() {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::__construct
	 */
	public function test_construct() {
		$this->construct_sut();
	}

	/**
	 * @covers ::set_locale
	 */
	public function test_set_locale_hooked() {

		\WP_Mock::expectActionAdded(
			'plugins_loaded',
			array( new AnyInstance( I18n::class ), 'load_plugin_textdomain' )
		);

		$this->construct_sut();
	}

	/**
	 * @covers ::define_order_hooks
	 */
	public function test_define_order_hooks() {

		\WP_Mock::expectActionAdded(
			'woocommerce_new_order',
			array( new AnyInstance( Order::class ), 'update_running_totals_on_new_order' ),
			10,
			2
		);

		\WP_Mock::expectActionAdded(
			'woocommerce_payment_complete',
			array( new AnyInstance( Order::class ), 'update_running_totals_on_payment_complete' )
		);

		\WP_Mock::expectActionAdded(
			'woocommerce_order_status_changed',
			array( new AnyInstance( Order::class ), 'update_running_totals_on_status_changed' ),
			10,
			4
		);

		$this->construct_sut();
	}

	/**
	 * @covers ::define_payment_gateway_hooks
	 */
	public function test_define_payment_gateway_hooks() {

		\WP_Mock::expectFilterAdded(
			'woocommerce_available_payment_gateways',
			array( new AnyInstance( Payment_Gateways::class ), 'load_balance_gateways' ),
			200,
			1
		);

		$this->construct_sut();
	}

	/**
	 * @covers ::define_payment_gateway_ui_hooks
	 */
	public function test_define_payment_gateway_ui_hooks() {

		\WP_Mock::expectFilterAdded(
			'woocommerce_get_sections_checkout',
			array( new AnyInstance( Payment_Gateways_UI::class ), 'add_settings_section' )
		);

		\WP_Mock::expectFilterAdded(
			'woocommerce_get_settings_checkout',
			array( new AnyInstance( Payment_Gateways_UI::class ), 'get_settings' ),
			10,
			2
		);

		\WP_Mock::expectActionAdded(
			'woocommerce_admin_field_bh_wc_gateway_load_balancer',
			array( new AnyInstance( Payment_Gateways_UI::class ), 'print_bh_wc_gateway_load_balancer_setting' )
		);

		\WP_Mock::expectFilterAdded(
			'woocommerce_admin_settings_sanitize_option_bh_wc_gateway_load_balancer_config',
			array( new AnyInstance( Payment_Gateways_UI::class ), 'process_config' ),
			10,
			3
		);

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Payment_Gateways_UI::class ), 'add_checkbox_js' )
		);

		$this->construct_sut();
	}

    /**
     * @covers ::define_plugins_page_hooks
     */
    public function test_plugins_page_hooks() {

        \WP_Mock::expectFilterAdded(
            'plugin_action_links_bh-wc-gateway-load-balancer/bh-wc-gateway-load-balancer.php',
            array( new AnyInstance( Plugins_Page::class ), 'action_links' )
        );

        $this->construct_sut();
    }

	protected function construct_sut() {

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		$logger   = new NullLogger();

		new BH_WC_Gateway_Load_Balancer( $api, $settings, $logger );
	}
}
