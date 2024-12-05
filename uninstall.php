<?php
/**
 * Uninstall Custom Library for Elementor.
 *
 * @package AnalogWP/CustomLibrary
 */

// Exit if accessed directly.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$options = get_option( 'analog_custom_library_options' );

if ( is_array( $options ) && isset( $options['remove_on_uninstall'] ) && true === $options['remove_on_uninstall'] ) {
	delete_option( 'analog_custom_library_options' );
	delete_option( '_analog_custom_library_import_history' );
	delete_option( 'analog_custom_library_previous_db_version' );
	delete_option( 'analog_custom_library_db_version' );
}

delete_transient( 'analog_custom_library_info' );

if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
