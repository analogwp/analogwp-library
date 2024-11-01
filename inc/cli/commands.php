<?php

namespace Analog\CLI;

use WP_CLI;
WP_CLI::add_command( 'analog custom-library refresh', __NAMESPACE__ . '\refresh_library' );
function refresh_library() {
	delete_transient( 'analogwp_template_info' );

	WP_CLI::success( 'Refresh Custom Library for Elementor.' );
}
