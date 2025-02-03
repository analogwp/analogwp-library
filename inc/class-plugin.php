<?php
/**
 * Main class for the plugin.
 *
 * @copyright 2024 SmallTownDev
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary;

use AnalogWP\CustomLibrary\Admin\Notices;

/**
 * Class AnalogWP\CustomLibrary\Plugin.
 */
final class Plugin {

	/**
	 * Main instance of the plugin.
	 *
	 * @var Plugin|null
	 */
	private static $instance;

	/**
	 * Holds key for Favorite templates user meta.
	 *
	 * @var string
	 */
	public static $user_meta_prefix = 'analog_custom_library_favorites';

	/**
	 * Holds key for Favorite blocks user meta.
	 *
	 * @var string
	 */
	public static $user_meta_block_prefix = 'analog_custom_library_block_favorites';

	/**
	 * Database Upgrader.
	 *
	 * @var Database_Upgrader
	 */
	public $database_upgrader;

	/**
	 * Sets the plugin main file.
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 */
	public function __construct( $main_file ) {
		$this->includes();
	}

	/**
	 * Registers the plugin with WordPress.
	 */
	public function register() {
		add_action( 'init', array( self::$instance, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( AGWP_LIBRARY_PLUGIN_FILE ), array( self::$instance, 'plugin_action_links' ) );
		add_action( 'admin_enqueue_scripts', array( self::$instance, 'scripts' ) );
		add_filter( 'analog/library/app/strings', array( self::$instance, 'send_strings_to_app' ) );

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
		if ( 'toplevel_page_analog_custom_library' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'analog-custom-library-google-fonts', 'https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap', array(), '20221016' );
		wp_enqueue_style( 'analog-custom-library-components-css', AGWP_LIBRARY_PLUGIN_URL . 'assets/css/library-components.css', array(), filemtime( AGWP_LIBRARY_PLUGIN_DIR . 'assets/css/library-components.css' ) );

		wp_enqueue_script(
			'analog-custom-library-app',
			AGWP_LIBRARY_PLUGIN_URL . 'assets/js/app/index.js',
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
			filemtime( AGWP_LIBRARY_PLUGIN_DIR . 'assets/js/app/index.js' ),
			true
		);
		wp_set_script_translations( 'analog-custom-library-app', 'analogwp-library', AGWP_LIBRARY_PLUGIN_DIR . 'languages' );

		$i10n = apply_filters( // phpcs:ignore
			'analog/library/app/strings',
			array(
				'is_settings_page'  => 'toplevel_page_analog_custom_library' === $hook,
			)
		);

		wp_localize_script( 'analog-custom-library-app', 'AGWP_LIBRARY', $i10n );

		Utils::enqueue_settings_toggle_css();
	}

	/**
	 * Prepare text strings to be sent to app.
	 *
	 * @param array $domains List of translatable strings.
	 *
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

		$library_placeholder_img_id  = $options->get( 'default-placeholder-thumb' );
		$library_placeholder_img_url = '';

		if ( $library_placeholder_img_id && wp_attachment_is_image( $library_placeholder_img_id ) ) {
			$library_placeholder_img_url = wp_get_attachment_image_url( $library_placeholder_img_id, 'full' );
		}

		$new_domains = array(
			'ajaxurl'                            => admin_url( 'admin-ajax.php' ),
			'favorites'                          => $favorites,
			'blockFavorites'                     => $block_favorites,
			'isPro'                              => Utils::is_pro(),
			'version'                            => AGWP_LIBRARY_VERSION,
			'elementorURL'                       => admin_url( 'edit.php?post_type=elementor_library' ),
			'debugMode'                          => ( defined( 'ANALOG_DEV_DEBUG' ) && ANALOG_DEV_DEBUG ),
			'pluginURL'                          => AGWP_LIBRARY_PLUGIN_URL,
			'license'                            => Utils::has_pro() ? array(
				'status'  => $options->get( 'analog_custom_library_license_key_status' ),
				'message' => get_transient( 'analog_custom_library_license_message' ),
			) : false,
			'adminURL'                           => admin_url(),
			'siteURL'                            => get_site_url(),
			'isContainer'                        => Utils::is_container(),
			'activePlugins'                      => array_values( $plugins ),
			'wp_version'                         => get_bloginfo( 'version' ),

			// Settings UI toggles.
			'libraryPlaceholderImgURL'           => $library_placeholder_img_url,
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
	 * @access public
	 *
	 * @param array $links An array of plugin action links.
	 *
	 * @return array An array of plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'edit.php?post_type=elementor_library&page=analog-custom-library-settings' ), __( 'Settings', 'analogwp-library' ) );

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @return void
	 */
	private function includes() {
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Core/Storage/class-transients.php';

		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Settings/class-register-settings.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Settings/settings-helpers.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-base.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-import-image.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-options.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Core/SVGs/class-allow-svg.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-consumer.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Admin/class-notice.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Admin/class-notices.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-utils.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/API/class-local.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-analogwp-custom-library-importer.php';

		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Core/Data/class-base-db.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Core/Data/class-templates-db.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Core/Data/class-library-data.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Core/class-library-init.php';

		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-elementor.php';

		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Elementor/trait-document.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Elementor/class-tools.php';
		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/class-database-upgrader.php';

		require_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Admin/class-admin.php';
	}

	/**
	 * Returns Elementor instance.
	 *
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
		load_plugin_textdomain( 'analogwp-library', false, dirname( AGWP_LIBRARY_PLUGIN_BASE ) . '/languages/' );
	}

	/**
	 * Retrieves the main instance of the plugin.
	 *
	 * @return Plugin Plugin main instance.
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Loads the plugin main instance and initializes it.
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

		do_action( 'analog_custom_library_loaded' );

		return true;
	}
}
