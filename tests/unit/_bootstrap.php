<?php
/**
 * PHPUnit bootstrap file for WP_Mock.
 *
 * @package           BH_WC_Gateway_Load_Balancer
 */

WP_Mock::bootstrap();

global $plugin_root_dir;
require_once $plugin_root_dir . '/autoload.php';

