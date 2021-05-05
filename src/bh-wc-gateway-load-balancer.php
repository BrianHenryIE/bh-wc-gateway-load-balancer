<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Gateway Load Balancer
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wc-gateway-load-balancer/
 * Description:       Weighted load balancer for WooCommerce payment gateways: decides one gateway at a time to display to customers, rotates through group of gateways based on ratios of orders' totals specified in settings.
 * Version:           1.2.0
 * Author:            BrianHenryIE
 * Author URI:        https://BrianHenryIE.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wc-gateway-load-balancer
 * Domain Path:       /languages
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings;
use BrianHenryIE\WC_Gateway_Load_Balancer\BrianHenryIE\WP_Logger\Logger;
use BrianHenryIE\WC_Gateway_Load_Balancer\Includes\Activator;
use BrianHenryIE\WC_Gateway_Load_Balancer\Includes\Deactivator;
use BrianHenryIE\WC_Gateway_Load_Balancer\Includes\BH_WC_Gateway_Load_Balancer;
use BrianHenryIE\WC_Gateway_Load_Balancer\Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


require_once __DIR__ . '/autoload.php';

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BH_WC_GATEWAY_LOAD_BALANCER_VERSION', '1.2.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 */
register_activation_hook(
	__FILE__,
	function() {
		Activator::activate();
	}
);

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
register_deactivation_hook(
	__FILE__,
	function() {
		Deactivator::deactivate();
	}
);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function instantiate_bh_wc_gateway_load_balancer(): API_Interface {

	$settings = new Settings();
	$logger   = Logger::instance( $settings );
	$api      = new API( $settings, $logger );

	new BH_WC_Gateway_Load_Balancer( $api, $settings, $logger );

	return $api;
}

/**
 * The plugins' primary methods.
 *
 * @var API_Interface $GLOBALS['bh_wc_gateway_load_balancer']
 */
$GLOBALS['bh_wc_gateway_load_balancer'] = instantiate_bh_wc_gateway_load_balancer();
