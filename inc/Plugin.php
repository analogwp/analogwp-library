<?php
/**
 * Class Analog\Plugin.
 *
 * @copyright 2024 SmallTownDev
 * @package Analog
 */

namespace Analog;

use Analog\Admin\Notices;

/**
 * Main class for the plugin.
 *
 * @since 1.6.0
 */
final class Plugin {

	/**
	 * Main instance of the plugin.
	 *
	 * @since 1.6.0
	 * @var Plugin|null
	 */
	private static $instance;

	/**
	 * Holds key for Favorite templates user meta.
	 *
	 * @var string
	 */
	public static $user_meta_prefix = 'analog_library_favorites';

	/**
	 * Holds key for Favorite blocks user meta.
	 *
	 * @var string
	 */
	public static $user_meta_block_prefix = 'analog_block_favorites';

	/**
	 * Database Upgrader.
	 *
	 * @var Database_Upgrader
	 */
	public $database_upgrader;

	/**
	 * Sets the plugin main file.
	 *
	 * @since 1.6.0
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 */
	public function __construct( $main_file ) {
		$this->includes();
	}

	/**
	 * Registers the plugin with WordPress.
	 *
	 * @since 1.6.0
	 */
	public function register() {
		add_action( 'init', array( self::$instance, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( ANG_PLUGIN_FILE ), array( self::$instance, 'plugin_action_links' ) );
		add_action( 'admin_enqueue_scripts', array( self::$instance, 'scripts' ) );
		add_filter( 'analog/app/strings', array( self::$instance, 'send_strings_to_app' ) );

		( new Consumer() )->register();
		( new Notices() )->register();

		// Migrations.
		$this->database_upgrader = new Database_Upgrader();
		add_action( 'admin_init', array( $this->database_upgrader, 'init' ) );
	}

	/**
	 * Enqueue plugin assets.
	 *
	 * @param string $hook Current page hook.
	 */
	public function scripts( $hook ) {
		if ( 'toplevel_page_analog_library' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'analog-google-fonts', 'https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap', array(), '20221016' );
		wp_enqueue_style( 'analogwp-components-css', ANG_PLUGIN_URL . 'assets/css/sk-components.css', array(), filemtime( ANG_PLUGIN_DIR . 'assets/css/sk-components.css' ) );

		wp_enqueue_script(
			'analogwp-library-app',
			ANG_PLUGIN_URL . 'assets/js/app/index.js',
			array(
				'react',
				'react-dom',
				'jquery',
				'wp-components',
				'wp-hooks',
				'wp-i18n',
				'wp-element',
				'wp-api-fetch',
				'wp-html-entities',
			),
			filemtime( ANG_PLUGIN_DIR . 'assets/js/app/index.js' ),
			true
		);
		wp_set_script_translations( 'analogwp-library-app', 'custom-library-for-elementor', ANG_PLUGIN_DIR . 'languages' );

		$i10n = apply_filters( // phpcs:ignore
			'analog/app/strings',
			array(
				'is_settings_page'  => 'toplevel_page_analog_library' === $hook,
				'rollback_url'      => wp_nonce_url( admin_url( 'admin-post.php?action=ang_rollback&version=VERSION' ), 'ang_rollback' ),
				'rollback_versions' => Utils::get_rollback_versions(),
			)
		);

		wp_localize_script( 'analogwp-library-app', 'AGWP_LIBRARY', $i10n );

		Utils::enqueue_settings_toggle_css();
	}

	/**
	 * Prepare text strings to be sent to app.
	 *
	 * @param array $domains List of translatable strings.
	 *
	 * @since 1.3.4
	 * @return array
	 */
	public function send_strings_to_app( $domains ) {
		if ( ! is_array( $domains ) ) {
			$domains = array();
		}

		$options = Options::get_instance();

		$favorites       = get_user_meta( get_current_user_id(), self::$user_meta_prefix, true );
		$block_favorites = get_user_meta( get_current_user_id(), self::$user_meta_block_prefix, true );

		if ( ! $favorites ) {
			$favorites = array();
		}
		if ( ! $block_favorites ) {
			$block_favorites = array();
		}

		$plugins = get_option( 'active_plugins' );
		$plugins = array_map( array( $this, 'filter_plugins' ), $plugins );

		$new_domains = array(
			'ajaxurl'                            => admin_url( 'admin-ajax.php' ),
			'favorites'                          => $favorites,
			'blockFavorites'                     => $block_favorites,
			'isPro'                              => Utils::is_pro(),
			'version'                            => ANG_VERSION,
			'elementorURL'                       => admin_url( 'edit.php?post_type=elementor_library' ),
			'debugMode'                          => ( defined( 'ANALOG_DEV_DEBUG' ) && ANALOG_DEV_DEBUG ),
			'pluginURL'                          => ANG_PLUGIN_URL,
			'license'                            => Utils::has_pro() ? array(
				'status'  => $options->get( 'ang_license_key_status' ),
				'message' => get_transient( 'ang_license_message' ),
			) : false,
			'adminURL'                           => admin_url(),
			'siteURL'                            => get_site_url(),
			'isContainer'                        => Utils::is_container(),
			'activePlugins'                      => array_values( $plugins ),
			'wp_version'                         => get_bloginfo( 'version' ),

			// Settings UI toggles.
			'libraryTemplateCols'                => $options->get( 'library_template_columns' ),
			'libraryCategoriesLocation'          => $options->get( 'library_categories_location' ),
			'showLibraryCategoriesTemplateCount' => $options->get( 'show_library_categories_template_count' ),
		);

		$domains += $new_domains;

		return $domains;
	}

	/**
	 * Filter plugin name.
	 *
	 * @param string $plugin Plugin name.
	 * @return string
	 */
	public function filter_plugins( $plugin ) {
		$plugin = explode( '/', $plugin );
		return $plugin[0];
	}

	/**
	 * Plugin action links.
	 *
	 * Adds action links to the plugin list table
	 *
	 * Fired by `plugin_action_links` filter.
	 *
	 * @since 1.3.1
	 * @access public
	 *
	 * @param array $links An array of plugin action links.
	 *
	 * @return array An array of plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=ang-library-settings' ), __( 'Settings', 'custom-library-for-elementor' ) );

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Include required files.
	 *
	 * @since 1.6.0
	 *
	 * @access private
	 * @return void
	 */
	private function includes() {
		require_once ANG_PLUGIN_DIR . 'inc/Core/Storage/Transients.php';

		require_once ANG_PLUGIN_DIR . 'inc/register-settings.php';
		require_once ANG_PLUGIN_DIR . 'inc/settings-helpers.php';
		require_once ANG_PLUGIN_DIR . 'inc/class-base.php';
		require_once ANG_PLUGIN_DIR . 'inc/class-import-image.php';
		require_once ANG_PLUGIN_DIR . 'inc/class-options.php';
		require_once ANG_PLUGIN_DIR . 'inc/Core/SVGs/Allow.php';
		require_once ANG_PLUGIN_DIR . 'inc/Consumer.php';
		require_once ANG_PLUGIN_DIR . 'inc/admin/Notice.php';
		require_once ANG_PLUGIN_DIR . 'inc/admin/Notices.php';
		require_once ANG_PLUGIN_DIR . 'inc/Utils.php';
		require_once ANG_PLUGIN_DIR . 'inc/api/class-remote.php';
		require_once ANG_PLUGIN_DIR . 'inc/api/class-local.php';
		require_once ANG_PLUGIN_DIR . 'inc/class-analog-importer.php';

		require_once ANG_PLUGIN_DIR . 'inc/Core/Data/class-base-db.php';
		require_once ANG_PLUGIN_DIR . 'inc/Core/Data/class-templates-db.php';
		require_once ANG_PLUGIN_DIR . 'inc/Core/Data/class-library-data.php';
		require_once ANG_PLUGIN_DIR . 'inc/Core/class-library-init.php';

		require_once ANG_PLUGIN_DIR . 'inc/class-elementor.php';
		require_once ANG_PLUGIN_DIR . 'inc/class-cron.php';

		require_once ANG_PLUGIN_DIR . 'inc/elementor/trait-document.php';
		require_once ANG_PLUGIN_DIR . 'inc/elementor/class-tools.php';
		require_once ANG_PLUGIN_DIR . 'inc/Database_Upgrader.php';

		require_once ANG_PLUGIN_DIR . 'inc/admin/class-admin.php';

		require_once ANG_PLUGIN_DIR . 'inc/class-beta-testers.php';

		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			require_once ANG_PLUGIN_DIR . 'inc/cli/commands.php';
		}
	}

	/**
	 * Returns Elementor instance.
	 *
	 * @since 1.6.1
	 * @return \Elementor\Plugin
	 */
	public static function elementor() {
		return \Elementor\Plugin::$instance;
	}

	/**
	 * Load plugin language files.
	 *
	 * @access public
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'custom-library-for-elementor', false, dirname( ANG_PLUGIN_BASE ) . '/languages/' );
	}

	/**
	 * Retrieves the main instance of the plugin.
	 *
	 * @since 1.6.0
	 *
	 * @return Plugin Plugin main instance.
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Loads the plugin main instance and initializes it.
	 *
	 * @since 1.6.0
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 * @return bool True if the plugin main instance could be loaded, false otherwise.
	 */
	public static function load( $main_file ) {
		if ( null !== self::$instance ) {
			return false;
		}

		self::$instance = new self( $main_file );
		self::$instance->register();

		do_action( 'ang_loaded' );

		return true;
	}
}

/**
 * Class Analog_Templates.
 *
 * Required for backwards compatibility with Pro.
 *
 * @package Analog
 */
final class Analog_Templates {

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	public static $version = ANG_VERSION;

	public function __construct() {}
}
