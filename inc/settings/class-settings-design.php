<?php
/**
 * Analog Design Settings.
 *
 * @package Analog/Admin
 * @since 1.9.0
 */

namespace Analog\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Design Control.
 */
class Design extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'design';
		$this->label = __( 'Design', 'ang' );
		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = apply_filters(
			'ang_experiments_settings',
			array()
		);

		return apply_filters( 'ang_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();

		Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		Admin_Settings::save_fields( $settings );
	}
}

return new Design();
