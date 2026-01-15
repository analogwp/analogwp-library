<?php
/**
 * Registers admin screen.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings;

use AnalogWP\CustomLibrary\Plugin;

/**
 * class Register_Settings.
 */
class Register_Settings {
	/**
	 * Class instance
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * New menu slug for the top-level menu.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'agwp-custom-library';

	/**
	 * Old menu slug (legacy - under Elementor Templates).
	 *
	 * @var string
	 */
	const LEGACY_MENU_SLUG = 'analog-custom-library-settings';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 30 );

		// @deprecated 1.9.0 Due for removal in 2.2.0.
		add_action( 'admin_menu', array( $this, 'register_legacy_menu' ), 30 );

		// Handle saving settings earlier than load-{page} hook to avoid race conditions in conditional menus.
		add_action( 'wp_loaded', array( $this, 'save_settings' ) );

		add_action( 'init', array( $this, 'create_options' ) );
	}

	/**
	 * Get a class instance.
	 *
	 * @return Register_Settings
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register plugin menu.
	 *
	 * Creates a new top-level menu below Elementor.
	 *
	 * @return void
	 */
	public function register_menu() {
		$permission = 'manage_options';
		if ( has_filter( 'analog_library_visibility_enabled', '__return_true' ) ) {
			$permission = 'read';
		}

		// Get the Elementor menu position to place our menu right below it.
		$menu_position = $this->get_menu_position_after_elementor();

		// SVG icon encoded as base64 for admin menu.
		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode(
			'<svg width="128" height="128" viewBox="0 0 128 128" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M90.3619 24H24V90.3619H31.9244V31.9244H90.3619V24Z" fill="#a7aaad"/><path d="M103.24 36.873H36.8777V103.235H103.24V36.873Z" fill="#a7aaad"/></svg>'
		); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		$custom_library_menu_title = Plugin::get_plugin_public_name();

		// Add top-level menu.
		add_menu_page(
			$custom_library_menu_title,
			$custom_library_menu_title,
			$permission,
			self::MENU_SLUG,
			array( $this, 'settings_page' ),
			$icon_svg,
			$menu_position
		);

		// Add Settings submenu (same as parent).
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'analogwp-library' ),
			__( 'Settings', 'analogwp-library' ),
			$permission,
			self::MENU_SLUG,
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Register legacy menu under Elementor Templates.
	 *
	 * Keeps the old menu location with a redirect message.
	 *
	 * @deprecated 1.9.0 Due for removal in 2.2.0.
	 *
	 * @return void
	 */
	public function register_legacy_menu() {
		// Return early if Elementor menu isn't registered yet.
		if ( ! did_action( 'elementor/admin/menu/after_register' ) ) {
			return;
		}

		$permission = 'manage_options';
		if ( has_filter( 'analog_library_visibility_enabled', '__return_true' ) ) {
			$permission = 'read';
		}

		$custom_library_menu_title = Plugin::get_plugin_public_name();

		add_submenu_page(
			'edit.php?post_type=elementor_library',
			$custom_library_menu_title . ' ' . __( 'Settings', 'analogwp-library' ),
			$custom_library_menu_title,
			$permission,
			self::LEGACY_MENU_SLUG,
			array( $this, 'legacy_redirect_page' ),
			1
		);
	}

	/**
	 * Get menu position after Elementor.
	 *
	 * @return float
	 */
	private function get_menu_position_after_elementor() {
		global $menu;

		$elementor_position = 58; // Default Elementor position.

		if ( ! empty( $menu ) ) {
			foreach ( $menu as $position => $item ) {
				if ( isset( $item[2] ) && 'elementor' === $item[2] ) {
					$elementor_position = $position;
					break;
				}
			}
		}

		// Place our menu right after Elementor (add small decimal to avoid conflicts).
		return $elementor_position + 0.1;
	}

	/**
	 * Legacy redirect page content.
	 *
	 * Shows a redirect message and auto-redirects to the new location.
	 *
	 * @return void
	 */
	public function legacy_redirect_page() {
		$new_url = admin_url( 'admin.php?page=' . self::MENU_SLUG );

		// Get current tab if any to preserve it.
		if ( isset( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$new_url = add_query_arg( 'tab', sanitize_key( $_GET['tab'] ), $new_url ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		?>
		<style>
			.agwp-redirect-container {
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				min-height: 300px;
				text-align: center;
				background: #fff;
				margin: 20px 20px 20px 0;
				padding: 40px;
				border-radius: 8px;
				box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
			}
			.agwp-redirect-container h2 {
				margin-bottom: 10px;
				color: #1d2327;
			}
			.agwp-redirect-container p {
				font-size: 14px;
				color: #50575e;
				margin-bottom: 20px;
			}
			.agwp-redirect-container .redirect-link {
				display: inline-flex;
				align-items: center;
				gap: 5px;
				font-size: 14px;
				text-decoration: none;
			}
			.agwp-redirect-container .redirect-link:hover {
				text-decoration: underline;
			}
			.agwp-redirect-spinner {
				display: inline-block;
				width: 20px;
				height: 20px;
				border: 2px solid #f3f3f3;
				border-top: 2px solid #5C32B6;
				border-radius: 50%;
				animation: agwp-spin 1s linear infinite;
				margin-right: 10px;
			}
			@keyframes agwp-spin {
				0% { transform: rotate(0deg); }
				100% { transform: rotate(360deg); }
			}
		</style>

		<div class="agwp-redirect-container">
			<h2><?php esc_html_e( 'This page has been moved to a new location', 'analogwp-library' ); ?></h2>
			<p>
				<span class="agwp-redirect-spinner"></span>
				<?php esc_html_e( 'Redirecting you now...', 'analogwp-library' ); ?>
			</p>
			<a href="<?php echo esc_url( $new_url ); ?>" class="redirect-link">
				<?php esc_html_e( 'Click here if you are not redirected automatically', 'analogwp-library' ); ?> &rarr;
			</a>
		</div>

		<script>
			(function() {
				var countdown = 1.5;
				setTimeout(function() {
					window.location.href = '<?php echo esc_js( $new_url ); ?>';
				}, countdown * 1000);
			})();
		</script>
		<?php
	}

	/**
	 * Add settings page.
	 *
	 * @return void
	 */
	public function settings_page() {
		Admin_Settings::output();
	}

	/**
	 * Handle saving of settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		global $current_tab, $current_section;

		// We should only save on the settings page (check both new and legacy slugs).
		if ( ! is_admin() || ! isset( $_GET['page'] ) ) { // phpcs:ignore
			return;
		}

		$current_page = sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore

		if ( ! in_array( $current_page, array( self::MENU_SLUG, self::LEGACY_MENU_SLUG ), true ) ) {
			return;
		}

		// Include settings pages.
		Admin_Settings::get_settings_pages();

		// Get current tab/section.
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( wp_unslash( $_REQUEST['section'] ) ); // phpcs:ignore

		// Save settings if data has been posted.
		if ( '' !== $current_section && apply_filters( "ang_save_settings_{$current_tab}_{$current_section}", ! empty( $_POST['save'] ) ) ) { // phpcs:ignore
			Admin_Settings::save();
		} elseif ( '' === $current_section && apply_filters( "ang_save_settings_{$current_tab}", ! empty( $_POST['save'] ) ) ) { // phpcs:ignore
			Admin_Settings::save();
		}

		do_action( 'analog_custom_library_settings_pages', $current_tab, $current_section );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 *
	 * @return void|bool
	 */
	public function create_options() {
		if ( ! is_admin() ) {
			return false;
		}
		// Include settings so that we can run through defaults.
		include_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Settings/class-admin-settings.php';

		$settings = array_filter( Admin_Settings::get_settings_pages() );

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( isset( $value['default'], $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}
}

// Instantiate settings.
Register_Settings::get_instance();
