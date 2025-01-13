<?php
/**
 * Class AnalogWP\CustomLibrary\Admin\Notices.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Admin;

/**
 * Class managing admin Notices.
 *
 * @package AnalogWP\CustomLibrary\Admin
 * @access private
 * @ignore
 */
final class Notices {
	/**
	 * Registers functionality through WordPress hooks.
	 *
	 */
	public function register() {
		$callback = function() {
			global $hook_suffix;

			if ( empty( $hook_suffix ) ) {
				return;
			}

			$this->render_notices( $hook_suffix );
		};

		add_action( 'admin_notices', $callback );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Renders admin notices.
	 *
	 *
	 * @param string $hook_suffix The current admin screen hook suffix.
	 */
	private function render_notices( $hook_suffix ) {
		$notices = $this->get_notices();
		if ( empty( $notices ) ) {
			return;
		}

		/**
		 * Notice object.
		 *
		 * @var Notice $notice Notice object.
		 */
		foreach ( $notices as $notice ) {
			if ( ! $notice->is_active( $hook_suffix ) ) {
				continue;
			}

			$notice->render();
		}
	}

	/**
	 * Gets available admin notices.
	 *
	 *
	 * @return array List of Notice instances.
	 */
	private function get_notices() {
		/**
		 * Filters the list of available admin notices.
		 *
		 *
		 * @param array $notices List of Notice instances.
		 */
		$notices = apply_filters( 'analog_custom_library_admin_notices', array() );

		return array_filter(
			$notices,
			static function( $notice ) {
				return $notice instanceof Notice;
			}
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * Registers all the admin scripts and enqueues them.
	 * Fired by `admin_enqueue_scripts` action.
	 *
	 * @access public
	 */
	public function enqueue_scripts() {
		wp_register_script(
			'analog-admin',
			AGWP_LIBRARY_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			filemtime( AGWP_LIBRARY_PLUGIN_DIR . 'assets/js/admin.js' ),
			true
		);

		wp_localize_script(
			'analog-admin',
			'AnalogAdmin',
			array(
				'nonce' => wp_create_nonce( Notice::$nonce_action ),
			)
		);

		wp_enqueue_script( 'analog-admin' );

		wp_register_style(
			'analog-admin',
			AGWP_LIBRARY_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			filemtime( AGWP_LIBRARY_PLUGIN_DIR . 'assets/css/admin.css' ),
		);

		wp_enqueue_style( 'analog-admin' );
	}

}
