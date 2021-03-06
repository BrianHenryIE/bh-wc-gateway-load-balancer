<?php
/**
 * Adds a UI in WooCommerce / Settings / Payments / Load Balancing for configuring a set of gateways to load balance.
 *
 * @see wp-admin/admin.php?page=wc-settings&tab=checkout&section=bh_wc_gateway_load_balancer
 *
 * TODO: This view should indicate if the gateways are enabled.
 *
 * @link              https://BrianHenryIE.com
 * @since             1.0.0
 * @package           BH_WC_Gateway_Load_Balancer
 * @license           GPL-v2.0+
 */

namespace BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce;

use BrianHenryIE\WC_Gateway_Load_Balancer\API\API_Interface;
use BrianHenryIE\WC_Gateway_Load_Balancer\API\Settings_Interface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class Payment_Gateways_UI
 *
 * @package BrianHenryIE\WC_Gateway_Load_Balancer\WooCommerce
 */
class Payment_Gateways_UI {

	use LoggerAwareTrait;

	/**
	 * Plugin version is used for JavaScript version (caching).
	 *
	 * @var Settings_Interface
	 */
	protected Settings_Interface $settings;

	/**
	 * The plugin's settings.
	 *
	 * @var API_Interface
	 */
	protected API_Interface $api;

	/**
	 * Order constructor.
	 *
	 * @param API_Interface      $api Main plugin functions.
	 * @param Settings_Interface $settings Plugin settings.
	 * @param LoggerInterface    $logger PSR logger.
	 */
	public function __construct( API_Interface $api, Settings_Interface $settings, LoggerInterface $logger ) {
		$this->setLogger( $logger );
		$this->settings = $settings;
		$this->api      = $api;
	}

	/**
	 * Register the horizontal section to display inside WooCommerce / Settings / Payments.
	 *
	 * @hooked woocommerce_get_sections_checkout
	 * @see \WC_Settings_Payment_Gateways::get_sections()
	 *
	 * @param array<string, string> $sections The horizontal sections for the current WC_Settings_Page section.
	 * @return array<string, string>
	 */
	public function add_settings_section( array $sections ): array {

		$sections['bh_wc_gateway_load_balancer'] = 'Load Balancing';

		return $sections;
	}

	/**
	 * The WooCommerce Settings API settings.
	 * * Description
	 * * Configuration table
	 * * Log level
	 *
	 * @hooked woocommerce_get_settings_checkout
	 * @see \WC_Settings_Payment_Gateways::get_settings()
	 *
	 * @param array<string|int, array<string,mixed>> $settings The settings set to be displayed.
	 * @param string                                 $current_section The current settings tab's section in the horizontal list.
	 *
	 * @return array<string|int, array<string,mixed>>
	 */
	public function get_settings( array $settings, string $current_section ): array {

		if ( 'bh_wc_gateway_load_balancer' === $current_section ) {

			$settings[] = array(
				'title'     => __( 'Load Balancing', 'bh-wc-gateway-load-balancer' ),
				'desc'      => __( 'Create a group of payment gateways and the ratio of orders\' total value that should be sent through each gateway per day. Only one gateway from the group will appear to customers at a time. Remaining gateways are unaffected.', 'bh-wc-gateway-load-balancer' ),
				'type'      => 'title',
				'id'        => 'bh_wc_gateway_load_balancer_options',
				'is_option' => false,
			);

			$settings[] = array(
				'title'    => __( 'Include all new orders', 'bh-wc-gateway-load-balancer' ),
				'type'     => 'checkbox',
				// TODO: Label is not working.
				'label'    => __( 'Consider every order in calculations.', 'bh-wc-gateway-load-balancer' ),
				'desc'     => __( 'Without this, only orders with WooCommerce\'s "paid" statuses (' . implode( ', ', wc_get_is_paid_statuses() ) . ') are counted.', 'bh-wc-gateway-load-balancer' ),
				'desc_tip' => true,
				'id'       => 'bh_wc_gateway_load_balancer_should_count_all_new_orders',
				'default'  => 'no',
			);

			$settings[] = array(
				'id'   => 'bh_wc_gateway_load_balancer_config',
				'type' => 'bh_wc_gateway_load_balancer',
			);

			// TODO: Don't offer levels that aren't used by the plugin.
			$log_levels        = array( 'none', LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG );
			$log_levels_option = array();
			foreach ( $log_levels as $log_level ) {
				$log_levels_option[ $log_level ] = ucfirst( $log_level );
			}

			$settings[] = array(
				'title'    => __( 'Log Level', 'bh-wc-gateway-load-balancer' ),
				'label'    => __( 'Enable Logging', 'bh-wc-gateway-load-balancer' ),
				'type'     => 'select',
				'options'  => $log_levels_option,
				'desc'     => __( 'Increasingly detailed logging.', 'bh-wc-gateway-load-balancer' ),
				'desc_tip' => true,
				'default'  => 'notice',
				'id'       => 'bh_wc_gateway_load_balancer_log_level',
			);

			$settings[] = array(
				'type'      => 'sectionend',
				'id'        => 'bh_wc_gateway_load_balancer_options',
				'is_option' => false,
			);

		}

		return $settings;
	}

	/**
	 * Output the settings field for the load balancing.
	 *
	 * Lists the payment gateways with a toggle/checkbox to mark them included, and an input box to set the ratio.
	 *
	 * @hooked woocommerce_admin_field_bh_wc_gateway_load_balancer
	 * @see \WC_Admin_Settings::output_fields()
	 *
	 * @param array<string, mixed> $value The get_settings() configuration with the saved $value in its `value` key.
	 */
	public function print_bh_wc_gateway_load_balancer_setting( array $value ): void {

		/**
		 * The saved settings in `$value['value']` is an array with {included: {gateway_id: on}} and
		 * {ratio: {gateway_id: int}}.
		 *
		 * @var array{included: array<string, string>, ratio: array<string, int>}
		 */
		$config = $value['value'];

		// If it has not been saved yet, it is an empty string.
		if ( empty( $config ) ) {
			$config = array();
		}

		if ( ! isset( $config['included'] ) ) {
			$config['included'] = array();
		}
		if ( ! isset( $config['ratio'] ) ) {
			$config['ratio'] = array();
		}

		?>
		<tr valign="top">
			<td class="wc_payment_gateways_wrapper bh_wc_gateway_load_balancer_settings" colspan="2">

				<table class="wc_gateways widefat" cellspacing="0">
					<thead>
					<tr>
						<?php
						$default_columns = array(
							'name'          => __( 'Method', 'bh-wc-gateway-load-balancer' ),
							'included'      => __( 'Included', 'bh-wc-gateway-load-balancer' ),
							'ratio'         => __( 'Ratio', 'bh-wc-gateway-load-balancer' ),
							'recent_totals' => __( 'Recent totals', 'bh-wc-gateway-load-balancer' ),
						);

						$columns = apply_filters( 'bh_wc_gateway_load_balancer_setting_columns', $default_columns );

						foreach ( $columns as $key => $column ) {

							switch ( $key ) {
								case 'recent_totals':
									/**
									 * Nicely display the time period.
									 *
									 * @see human_time_diff()
									 * Then remove singular.
									 * e.g. "1 day" to "day".
									 */
									$human_readable_period = human_time_diff( 0, $this->settings->get_period() );
									$human_readable_period = preg_replace( '/^1 /', '', $human_readable_period ) ?? $human_readable_period;
									echo '<th class="' . esc_attr( $key ) . '">' . esc_html( $column ) . ' (per ' . esc_html( $human_readable_period ) . ')</th>';

									break;
								default:
									echo '<th class="' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
									break;
							}
						}
						?>
					</tr>
					</thead>
					<tbody>
					<?php
					/**
					 * Loop over all gateways registered with WooCommerce.
					 *
					 * @var \WC_Payment_Gateway $gateway
					 */
					foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {

						$gateway_id = $gateway->id;

						$method_title = $gateway->get_method_title() ? $gateway->get_method_title() : $gateway->get_title();
						$custom_title = $gateway->get_title();

						$gateway_statistics = $this->api->get_recent_totals_stats( $this->settings->get_period() );

						echo '<tr data-gateway_id="' . esc_attr( $gateway_id ) . '">';

						foreach ( $columns as $key => $column ) {
							if ( ! array_key_exists( $key, $default_columns ) ) {
								do_action( 'bh_wc_gateway_load_balancer_setting_column_' . $key, $gateway );
								continue;
							}

							$width = '';

							echo '<td class="' . esc_attr( $key ) . '" width="' . esc_attr( $width ) . '">';

							switch ( $key ) {

								case 'name':
									echo '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . strtolower( $gateway->id ) ) ) . '" class="wc-payment-gateway-method-title">' . wp_kses_post( $method_title ) . '</a>';

									if ( $method_title !== $custom_title ) {
										echo '<span class="wc-payment-gateway-method-name">&nbsp;&ndash;&nbsp;' . wp_kses_post( $custom_title ) . '</span>';
									}
									break;

								case 'included':
									echo '<label for="bh_wc_gateway_load_balancer_config_included_' . esc_attr( $gateway_id ) . '">';

									if ( isset( $config['included'][ $gateway_id ] ) && 'on' === $config['included'][ $gateway_id ] ) {
										/* Translators: %s Payment gateway name. */
										echo '<span id="bh_wc_gateway_load_balancer_config_toggle_' . esc_attr( $gateway_id ) . '" class="woocommerce-input-toggle woocommerce-input-toggle--enabled" aria-label="' . esc_attr( sprintf( __( 'The "%s" payment method is currently included in the load balancing group', 'bh-wc-gateway-load-balancer' ), $method_title ) ) . '">' . esc_attr__( 'Yes', 'woocommerce' ) . '</span>';
									} else {
										/* Translators: %s Payment gateway name. */
										echo '<span id="bh_wc_gateway_load_balancer_config_toggle_' . esc_attr( $gateway_id ) . '" class="woocommerce-input-toggle woocommerce-input-toggle--disabled" aria-label="' . esc_attr( sprintf( __( 'The "%s" payment method is not currently included in the load balancing group', 'bh-wc-gateway-load-balancer' ), $method_title ) ) . '">' . esc_attr__( 'No', 'woocommerce' ) . '</span>';
									}

									echo '</label>';

									?>
									<input type="checkbox" style="display: none;"
											name="bh_wc_gateway_load_balancer_config[included][<?php echo esc_attr( $gateway_id ); ?>]"
											id="bh_wc_gateway_load_balancer_config_included_<?php echo esc_attr( $gateway_id ); ?>"
											class="bh_wc_gateway_load_balancer_config_included"
											data-gateway="<?php echo esc_attr( $gateway_id ); ?>"
											<?php checked( isset( $config['included'][ $gateway_id ] ) ? $config['included'][ $gateway_id ] : 'off', 'on' ); ?>
											/>
									<?php

									break;

								case 'ratio':
									?>
									<input
											name="bh_wc_gateway_load_balancer_config[ratio][<?php echo esc_attr( $gateway_id ); ?>]"
											id="bh_wc_gateway_load_balancer_config[ratio][<?php echo esc_attr( $gateway_id ); ?>]"
											type="number"
											style="width: 80px;"
											<?php
											$ratio_value = isset( $config['ratio'][ $gateway_id ] ) ? $config['ratio'][ $gateway_id ] : '';
											if ( 0 === $ratio_value ) {
												$ratio_value = '';
											}
											?>
											value="<?php echo esc_attr( "{$ratio_value}" ); ?>"
											class="<?php echo esc_attr( $value['class'] ); ?>"
											step="1"
											min="0"
									/>
									<?php
									break;
								case 'recent_totals':
									if ( ! isset( $gateway_statistics[ $gateway_id ] ) ) {
										break;
									}

									// TODO: Add currency symbol.
									echo '<div>' . esc_html( get_woocommerce_currency_symbol() . wc_format_localized_price( "{$gateway_statistics[ $gateway_id ]['total']}" ) );

									echo ' in ' . intval( $gateway_statistics[ $gateway_id ]['count'] ) . ' transactions. ';
									// TODO: Add the time.
									echo ' (oldest included transaction ' . esc_html( date_i18n( 'G:i ' . get_option( 'date_format' ), $gateway_statistics[ $gateway_id ]['oldest_time'] ) ) . ').';

									echo '</div>';

									?>

									<?php
							}

							echo '</td>';
						}

						echo '</tr>';
					}
					?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}

	/**
	 * Remove empty entries from config before saving.
	 * TODO: Set default values when gateways are enabled.
	 *
	 * @hooked woocommerce_admin_settings_sanitize_option_bh_wc_gateway_load_balancer_config
	 * @see \WC_Admin_Settings::save_fields()
	 *
	 * @param mixed                $value The $_POST['bh_wc_gateway_load_balancer_config'] value.
	 * @param array<string, mixed> $option The `bh_wc_gateway_load_balancer_config` settings element from get_settings() above.
	 * @param mixed                $raw_value The $_POST['bh_wc_gateway_load_balancer_config'] before it was run through wc_clean().
	 *
	 * @return array{included: array<string, string>, ratio: array<string, string>} The data to be saved.
	 */
	public function process_config( $value, array $option, $raw_value ): array {

		$included = isset( $value['included'] ) ? $value['included'] : array();

		$ratio = array_filter(
			$value['ratio'],
			function ( $element ) {
				return ! empty( $element );
			}
		);

		$active_ratio = array_filter(
			$ratio,
			function ( $key ) use ( $included ) {
				return array_key_exists( $key, $included );
			},
			ARRAY_FILTER_USE_KEY
		);

		// If not all enabled gateways have a ratio number set.
		if ( count( $included ) !== count( $active_ratio ) ) {
			$default = array_sum( $active_ratio ) / count( $active_ratio );

			foreach ( $included as $gateway_id => $_ ) {
				if ( ! isset( $ratio[ $gateway_id ] ) ) {
					$ratio[ $gateway_id ] = $default;
				}
			}
		}

		return array(
			'included' => $included,
			'ratio'    => $ratio,
		);
	}

	/**
	 * Add JavaScript to visually change the toggle when it is clicked.
	 *
	 * @hooked admin_enqueue_scripts
	 */
	public function add_checkbox_js(): void {

		wp_register_script( 'bh-wc-gateway-load-balancer', '', array( 'jquery' ), $this->settings->get_plugin_version(), true );
		wp_enqueue_script( 'bh-wc-gateway-load-balancer' );

		$script = <<<'EOD'
(function( $ ) {
    $('.bh_wc_gateway_load_balancer_config_included').change(function( e ) {
        var gateway = $(this).data('gateway');  
        var toggle = $('#bh_wc_gateway_load_balancer_config_toggle_' + gateway );
    
        if(this.checked) {
            toggle.addClass('woocommerce-input-toggle--enabled');
            toggle.removeClass('woocommerce-input-toggle--disabled');
        } else {
            toggle.addClass('woocommerce-input-toggle--disabled');
            toggle.removeClass('woocommerce-input-toggle--enabled');
        }   
    });
})( jQuery );
EOD;

		wp_add_inline_script(
			'bh-wc-gateway-load-balancer',
			$script
		);
	}
}
