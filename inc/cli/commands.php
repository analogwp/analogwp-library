<?php
/**
 * WP CLI Commands.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\CLI;

use WP_CLI;

// Register WP CLI command.
WP_CLI::add_command( 'analog custom-library refresh', __NAMESPACE__ . '\refresh_library' );

/**
 * Callback function for refreshing remote library.
 *
 * @return void
 */
function refresh_library() {
	delete_transient( 'analog_custom_library_info' );

	WP_CLI::success( 'Refresh Custom Library for Elementor.' );
}
