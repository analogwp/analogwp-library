<?php
/**
 * Register admin screen.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings;

/**
 * Register plugin menu.
 *
 * @return void
 */
function register_menu() {
	add_submenu_page(
		'elementor',
		__( 'Custom Library Settings', 'custom-library-for-elementor' ),
		__( 'AnalogWP Custom Library', 'custom-library-for-elementor' ),
		'manage_options',
		'analog-custom-library-settings',
		__NAMESPACE__ . '\settings_page',
		1
	);

	// Remove duplicate menu hack.
	remove_submenu_page( $menu_slug, $menu_slug );

	add_action( 'load-style-kits_page_settings', __NAMESPACE__ . '\settings_page_init' );
}


/**
 * Loads methods into memory for use within settings.
 */
function settings_page_init() {

	// Include settings pages.
	Admin_Settings::get_settings_pages();

	// Add any posted messages.
	if ( ! empty( $_GET['analog_custom_library_error'] ) ) { // phpcs:ignore
		Admin_Settings::add_error( wp_kses_post( wp_unslash( $_GET['analog_custom_library_error'] ) ) ); // phpcs:ignore
	}

	if ( ! empty( $_GET['analog_custom_library_message'] ) ) { // phpcs:ignore
		Admin_Settings::add_message( wp_kses_post( wp_unslash( $_GET['analog_custom_library_message'] ) ) ); // phpcs:ignore
	}

	do_action( 'analog_custom_library_settings_page_init' );
}
add_action( 'admin_menu', __NAMESPACE__ . '\register_menu', 30 );

/**
 * Handle saving of settings.
 *
 * @return void
 */
function save_settings() {
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


// Handle saving settings earlier than load-{page} hook to avoid race conditions in conditional menus.
add_action( 'wp_loaded', __NAMESPACE__ . '\save_settings' );

/**
 * Add settings page.
 *
 * @return void
 */
function new_settings_page() {
	Admin_Settings::output();
}

/**
 * Add settings page.
 *
 * @return void
 */
function settings_page() {
	do_action( 'analog_custom_library_loaded_templates' );
	?>
	<style>body { background: #F1F1F1; }</style>
	<div id="analog-custom-library" class=""></div>
	<?php
}

/**
 * Default options.
 *
 * Sets up the default options used on the settings page.
 */
function create_options() {
	if ( ! is_admin() ) {
		return false;
	}
	// Include settings so that we can run through defaults.
	include_once __DIR__ . '/class-admin-settings.php';

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
add_action( 'init', __NAMESPACE__ . '\create_options' );
