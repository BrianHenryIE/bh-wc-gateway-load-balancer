<?php
/**
 * Adds a Settings link on plugins.php to WooCommerce/Settings/Payments/Load Balancing.
 *
 * @see admin.php?page=wc-settings&tab=checkout&section=bh_wc_gateway_load_balancer
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\Admin;

/**
 * This class adds a `Settings` link on the plugins.php page.
 */
class Plugins_Page {


	/**
	 * Add link to settings page in plugins.php list.
	 *
	 * @hooked plugin_action_links_{basename}
	 *
	 * @param array<int|string, string> $links_array The existing plugin links (usually "Deactivate").
	 *
	 * @return array<int|string, string> The links to display below the plugin name on plugins.php.
	 */
	public function action_links( $links_array ): array {

		// Don't add the link if the destination does not exist.
		if ( ! class_exists( \WooCommerce::class ) ) {
			return $links_array;
		}

		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bh_wc_gateway_load_balancer' );

		array_unshift( $links_array, '<a href="' . $settings_url . '">Settings</a>' );

		return $links_array;
	}
}
