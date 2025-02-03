<?php
/**
 * Plugin main file.
 *
 * @package     AnalogWP/CustomLibrary
 * @copyright   2025 SmallTownDev
 * @link        https://analogwp.com/analogwp-library
 *
 * @wordpress-plugin
 * Plugin Name: Custom Library for Elementor
 * Plugin URI:  https://github.com/analogwp/analogwp-library
 * Description: Custom Library for Elementor creates the foundation for a design framework that will help you create better, more consistent websites with Elementor.
 * Version:     1.0.5
 * Author:      AnalogWP
 * Author URI:  https://analogwp.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: analogwp-library
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * Elementor tested up to: 3.27.3
 * Elementor Pro tested up to: 3.27.2
 */

defined( 'ABSPATH' ) || exit;

define( 'AGWP_LIBRARY_ELEMENTOR_MINIMUM', '3.20.0' );
define( 'AGWP_LIBRARY_PHP_MINIMUM', '7.4' );
define( 'AGWP_LIBRARY_WP_MINIMUM', '6.0' );
define( 'AGWP_LIBRARY_VERSION', '1.0.5' );
define( 'AGWP_LIBRARY_PLUGIN_FILE', __FILE__ );
define( 'AGWP_LIBRARY_PLUGIN_URL', plugin_dir_url( AGWP_LIBRARY_PLUGIN_FILE ) );
define( 'AGWP_LIBRARY_PLUGIN_DIR', plugin_dir_path( AGWP_LIBRARY_PLUGIN_FILE ) );
define( 'AGWP_LIBRARY_PLUGIN_BASE', plugin_basename( AGWP_LIBRARY_PLUGIN_FILE ) );

/**
 * Handles plugin activation.
 *
 * Throws an error if the plugin is activated on an older version than PHP 5.6.
 *
 * @access private
 * @return void
 */
function agwp_custom_library_activate_plugin() {
	if ( version_compare( PHP_VERSION, AGWP_LIBRARY_PHP_MINIMUM, '<' ) ) {
		wp_die(
		/* translators: %s: version number */
			esc_html( sprintf( __( 'Custom Library for Elementor requires PHP version %s', 'analogwp-library' ), AGWP_LIBRARY_PHP_MINIMUM ) ),
			esc_html__( 'Error Activating', 'analogwp-library' )
		);
	}

	do_action( 'agwp_custom_library_activation' );
}

register_activation_hook( __FILE__, 'agwp_custom_library_activate_plugin' );

/**
 * Handles plugin deactivation.
 *
 * @access private
 * @return void
 */
function agwp_custom_library_deactivate_plugin() {
	if ( version_compare( PHP_VERSION, AGWP_LIBRARY_PHP_MINIMUM, '<' ) ) {
		return;
	}

	do_action( 'agwp_custom_library_deactivation' );
}

register_deactivation_hook( __FILE__, 'agwp_custom_library_deactivate_plugin' );

/**
 * Fail loading, if WordPress version requirements not met.
 *
 * @return void
 */
function agwp_custom_library_fail_wp_version() {
	/* translators: %s: WordPress version */
	$message      = sprintf( esc_html__( 'Custom Library for Elementor requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'analogwp-library' ), AGWP_LIBRARY_WP_MINIMUM );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );

	echo wp_kses_post( $html_message );
}

/**
 * Elementor version requirements are not met.
 *
 * @return mixed
 */
function agwp_custom_library_require_minimum_elementor() {
	$file_path = 'elementor/elementor.php';

	$link = add_query_arg(
		array(
			'action' => 'upgrade-plugin',
			'plugin' => $file_path,
		),
		admin_url( 'update.php' )
	);

	$update_url = wp_nonce_url( $link, 'upgrade-plugin_' . $file_path );

	/* translators: %s: Minimum required Elementor version. */
	$message = '<p>' . sprintf( esc_html__( 'Custom Library for Elementor requires Elementor v%s or newer in order to work. Please update Elementor to the latest version.', 'analogwp-library' ), AGWP_LIBRARY_ELEMENTOR_MINIMUM ) . '</p>';

	$message .= '<p>';
	/* translators: %s: Link to update Elementor. */
	$message .= sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $update_url ), esc_html__( 'Update Elementor Now', 'analogwp-library' ) );
	$message .= '</p>';

	echo '<div class="error"><p>' . $message . '</p></div>'; // @codingStandardsIgnoreLine
}

/**
 * Fail plugin initiialization if requirements are not met.
 *
 * @return mixed|bool
 */
function agwp_custom_library_fail_load() {
	$screen = get_current_screen();

	if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
		return;
	}

	$file_path = 'elementor/elementor.php';

	$is_not_activated = false;
	$is_not_installed = false;

	$installed_plugins = get_plugins();
	$elementor         = isset( $installed_plugins[ $file_path ] );

	if ( $elementor ) {
		$is_not_activated = true;
	} else {
		$is_not_installed = true;
	}

	$message = '';

	if ( $is_not_activated ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $file_path . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $file_path );
		$message        = '<p>' . esc_html__( 'Custom Library for Elementor is not working because you need to activate the Elementor plugin.', 'analogwp-library' ) . '</p>';
		$message       .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $activation_url ), esc_html__( 'Activate Elementor Now', 'analogwp-library' ) ) . '</p>';
	} elseif ( $is_not_installed ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
		$message     = '<p>' . esc_html__( 'Custom Library for Elementor is not working because you need to install the Elementor plugin.', 'analogwp-library' ) . '</p>';
		$message    .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $install_url ), esc_html__( 'Install Elementor Now', 'analogwp-library' ) ) . '</p>';
	}

	echo '<div class="error"><p>' . $message . '</p></div>'; // @codingStandardsIgnoreLine
}

// Load dependencies.
$vendor_file = __DIR__ . '/vendor/autoload.php';

if ( is_readable( $vendor_file ) ) {
	require_once $vendor_file;
}

if ( ! function_exists( 'agwp_custom_library_for_elementor_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 *
	 * @return object
	 */
	function agwp_custom_library_for_elementor_fs() {
		global $agwp_custom_library_for_elementor_fs;

		if ( ! isset( $agwp_custom_library_for_elementor_fs ) ) {
			// Manually include the Freemius SDK (not needed if using Composer).

			$agwp_custom_library_for_elementor_fs = fs_dynamic_init(
				array(
					'id'             => '17229',
					'slug'           => 'analogwp-library',
					'premium_slug'   => 'custom-library-for-elementor-premium',
					'type'           => 'plugin',
					'public_key'     => 'pk_933cd86a01a4af4c84ed15dae1d5f',
					'is_premium'     => false,
					'has_addons'     => true,
					'has_paid_plans' => false,
					'menu'           => array(
						'slug'           => 'analog-custom-library-settings',
						'override_exact' => true,
						'first-path'     => 'admin.php?page=analog-custom-library-settings',
						'account'        => false,
						'support'        => false,
						'parent'         => array(
							'slug' => 'edit.php?post_type=elementor_library',
						),
					),
				)
			);
		}

		return $agwp_custom_library_for_elementor_fs;
	}

	// Init Freemius.
	agwp_custom_library_for_elementor_fs();
	// Signal that SDK was initiated.
	do_action( 'agwp_custom_library_for_elementor_fs_loaded' );

	function agwp_custom_library_for_elementor_fs_settings_url() {
		return admin_url( 'edit.php?post_type=elementor_library&page=analog-custom-library-settings' );
	}

	agwp_custom_library_for_elementor_fs()->add_filter( 'connect_url', 'agwp_custom_library_for_elementor_fs_settings_url' );
	agwp_custom_library_for_elementor_fs()->add_filter( 'after_skip_url', 'agwp_custom_library_for_elementor_fs_settings_url' );
	agwp_custom_library_for_elementor_fs()->add_filter( 'after_connect_url', 'agwp_custom_library_for_elementor_fs_settings_url' );
	agwp_custom_library_for_elementor_fs()->add_filter( 'after_pending_connect_url', 'agwp_custom_library_for_elementor_fs_settings_url' );
}

/**
 * Fire up plugin instance.
 */
add_action(
	'plugins_loaded',
	static function () {
		if ( version_compare( PHP_VERSION, AGWP_LIBRARY_PHP_MINIMUM, '<' ) ) {
			wp_die(
			/* translators: %s: version number */
				esc_html( sprintf( __( 'Custom Library for Elementor requires PHP version %s', 'analogwp-library' ), AGWP_LIBRARY_PHP_MINIMUM ) ),
				esc_html__( 'Error Activating', 'analogwp-library' )
			);
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', 'agwp_custom_library_fail_load' );
			return;
		}

		if ( ! version_compare( ELEMENTOR_VERSION, AGWP_LIBRARY_ELEMENTOR_MINIMUM, '>=' ) ) {
			// Include files temporarily, required for rollbacks to work.
			require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-base.php';
			require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Core/Storage/class-transients.php';
			require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Elementor/class-tools.php';
			require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-utils.php';

			add_action( 'admin_notices', 'agwp_custom_library_require_minimum_elementor' );
			return;
		}

		if ( ! version_compare( get_bloginfo( 'version' ), AGWP_LIBRARY_WP_MINIMUM, '>=' ) ) {
			add_action( 'admin_notices', 'agwp_custom_library_fail_wp_version' );
			return;
		}

		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-plugin.php';

		\AnalogWP\CustomLibrary\Plugin::load( AGWP_LIBRARY_PLUGIN_FILE );
	}
);
