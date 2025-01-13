<?php
/**
 * Registers admin screen.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings;

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
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 30 );

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
	 * @return void
	 */
	public function register_menu() {
		// Return early if Elementor menu isn't registered yet.
		if ( ! did_action( 'elementor/admin/menu/after_register' ) ) {
			return;
		}

		add_submenu_page(
			'edit.php?post_type=elementor_library',
			__( 'Custom Library Settings', 'analogwp-library' ),
			__( 'Custom Library', 'analogwp-library' ),
			'manage_options',
			'analog-custom-library-settings',
			array( $this, 'settings_page' ),
			1
		);
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

		// We should only save on the settings page.
		if ( ! is_admin() || ! isset( $_GET['page'] ) || 'analog-custom-library-settings' !== $_GET['page'] ) { // phpcs:ignore
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
