<?php
/**
 * Plugin main file.
 *
 * @package     Analog
 * @copyright   2024 SmallTownDev
 * @link        https://analogwp.com/custom-library-for-elementor
 *
 * @wordpress-plugin
 * Plugin Name: Custom Library for Elementor
 * Plugin URI:  https://analogwp.com/custom-library-for-elementor
 * Description: Custom Library for Elementor extends the Elementor library with a custom library of your own templates. Boost your design workflow in Elementor with this plugin.
 * Version:     2.1.0
 * Author:      AnalogWP
 * Author URI:  https://analogwp.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-library-for-elementor
 * Elementor tested up to: 3.25.10
 * Elementor Pro tested up to: 3.23.4
 */

defined( 'ABSPATH' ) || exit;

define( 'AGWP_LIBRARY_ELEMENTOR_MINIMUM', '3.10.0' );
define( 'AGWP_LIBRARY_PHP_MINIMUM', '7.4' );
define( 'AGWP_LIBRARY_WP_MINIMUM', '6.0' );
define( 'AGWP_LIBRARY_VERSION', '2.1.0' );
define( 'AGWP_LIBRARY_PLUGIN_FILE', __FILE__ );
define( 'AGWP_LIBRARY_PLUGIN_URL', plugin_dir_url( AGWP_LIBRARY_PLUGIN_FILE ) );
define( 'AGWP_LIBRARY_PLUGIN_DIR', plugin_dir_path( AGWP_LIBRARY_PLUGIN_FILE ) );
define( 'AGWP_LIBRARY_PLUGIN_BASE', plugin_basename( AGWP_LIBRARY_PLUGIN_FILE ) );

/**
 * Handles plugin activation.
 *
 * Throws an error if the plugin is activated on an older version than PHP 5.6.
 *
 * @since 1.6.0
 * @access private
 * @return void
 */
function analog_activate_plugin() {
	if ( version_compare( PHP_VERSION, AGWP_LIBRARY_PHP_MINIMUM, '<' ) ) {
		wp_die(
			/* translators: %s: version number */
			esc_html( sprintf( __( 'Custom Library for Elementor requires PHP version %s', 'custom-library-for-elementor' ), AGWP_LIBRARY_PHP_MINIMUM ) ),
			esc_html__( 'Error Activating', 'custom-library-for-elementor' )
		);
	}

	do_action( 'analog_activation' );
}

register_activation_hook( __FILE__, 'analog_activate_plugin' );

/**
 * Handles plugin deactivation.
 *
 * @since 1.6.0
 * @access private
 * @return void
 */
function analog_deactivate_plugin() {
	if ( version_compare( PHP_VERSION, AGWP_LIBRARY_PHP_MINIMUM, '<' ) ) {
		return;
	}

	do_action( 'analog_deactivation' );
}

register_deactivation_hook( __FILE__, 'analog_deactivate_plugin' );

/**
 * Fail loading, if WordPress version requirements not met.
 *
 * @since 1.1
 * @return void
 */
function analog_fail_wp_version() {
	/* translators: %s: WordPress version */
	$message      = sprintf( esc_html__( 'Analog Library requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'custom-library-for-elementor' ), AGWP_LIBRARY_WP_MINIMUM );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );

	echo wp_kses_post( $html_message );
}

/**
 * Elementor version requirements are not met.
 *
 * @return mixed
 */
function analog_require_minimum_elementor() {
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
	$message = '<p>' . sprintf( __( 'Analog Library requires Elementor v%s or newer in order to work. Please update Elementor to the latest version.', 'custom-library-for-elementor' ), AGWP_LIBRARY_ELEMENTOR_MINIMUM ) . '</p>';

	$versions = get_transient( 'ang_rollback_versions_' . AGWP_LIBRARY_VERSION );

	$message .= '<p>';
	/* translators: %s: Link to update Elementor. */
	$message .= sprintf( '<a href="%s" class="button-primary">%s</a>', $update_url, __( 'Update Elementor Now', 'custom-library-for-elementor' ) );
	/* translators: %s: Link to rollback plugin to previous version. */
	$message .= sprintf(
		'<a href="%s" class="button-secondary" style="margin-left:10px">%s</a>',
		wp_nonce_url( admin_url( 'admin-post.php?action=ang_rollback&version=' . $versions[0] ), 'ang_rollback' ),
		/* translators: %s: Version number. */
		sprintf( __( 'Rollback to v%s', 'custom-library-for-elementor' ), $versions[0] )
	);
	$message .= '</p>';

	echo '<div class="error"><p>' . $message . '</p></div>'; // @codingStandardsIgnoreLine
}

/**
 * Fail plugin initiialization if requirements are not met.
 *
 * @return mixed|bool
 */
function analog_fail_load() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		require_once ABSPATH . 'wp-admin/includes/screen.php';
	}

	$screen = get_current_screen();

	if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
		return;
	}

	$file_path = 'elementor/elementor.php';

	$is_not_activated = false;
	$is_not_installed = false;

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$installed_plugins = get_plugins();
	$elementor         = isset( $installed_plugins[ $file_path ] );

	if ( $elementor ) {
		$is_not_activated = true;
	} else {
		$is_not_installed = true;
	}

	if ( $is_not_activated ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $file_path . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $file_path );
		$message        = '<p>' . __( 'Analog Library is not working because you need to activate the Elementor plugin.', 'custom-library-for-elementor' ) . '</p>';
		$message       .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Elementor Now', 'custom-library-for-elementor' ) ) . '</p>';
	} elseif ( $is_not_installed ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
		$message     = '<p>' . __( 'Analog Library is not working because you need to install the Elementor plugin.', 'custom-library-for-elementor' ) . '</p>';
		$message    .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Elementor Now', 'custom-library-for-elementor' ) ) . '</p>';
	}

	echo '<div class="error"><p>' . $message . '</p></div>'; // @codingStandardsIgnoreLine
}

// Third party dependencies.
$vendor_file = __DIR__ . '/third-party/vendor/scoper-autoload.php';

if ( is_readable( $vendor_file ) ) {
	require_once $vendor_file;
}

/**
 * Fire up plugin instance.
 *
 * @since 1.6.0 Add PHP version check.
 */
add_action(
	'plugins_loaded',
	static function() {
		if ( version_compare( PHP_VERSION, AGWP_LIBRARY_PHP_MINIMUM, '<' ) ) {
			wp_die(
			/* translators: %s: version number */
				esc_html( sprintf( __( 'Custom Library for Elementor requires PHP version %s', 'custom-library-for-elementor' ), AGWP_LIBRARY_PHP_MINIMUM ) ),
				esc_html__( 'Error Activating', 'custom-library-for-elementor' )
			);
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', 'analog_fail_load' );
			return;
		}

		if ( ! version_compare( ELEMENTOR_VERSION, AGWP_LIBRARY_ELEMENTOR_MINIMUM, '>=' ) ) {
			// Include files temporarily, required for rollbacks to work.
			require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-base.php';
			require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Core/Storage/class-transients.php';
			require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/elementor/class-tools.php';
			require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Utils.php';

			add_action( 'admin_notices', 'analog_require_minimum_elementor' );
			return;
		}

		if ( ! version_compare( get_bloginfo( 'version' ), AGWP_LIBRARY_WP_MINIMUM, '>=' ) ) {
			add_action( 'admin_notices', 'analog_fail_wp_version' );
			return;
		}

		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-plugin.php';

		\Analog\Plugin::load( AGWP_LIBRARY_PLUGIN_FILE );
	}
);
