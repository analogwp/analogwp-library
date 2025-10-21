<?php
/**
 * Analog Version Control Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Settings\Admin_Settings;
use AnalogWP\CustomLibrary\Settings\Settings_Page;
use AnalogWP\CustomLibrary\Featuresets\Rollback\Init as Rollback;

defined( 'ABSPATH' ) || exit;

/**
 * Version Control.
 */
class Version_Control extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'version-control';
		$this->label = __( 'Version Control', 'analogwp-library' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$rollback_controls = array();

		if ( current_user_can( 'update_plugins' ) ) {
			array_push(
				$rollback_controls,
				array(
					'title' => __( 'Rollback Versions', 'analogwp-library' ),
					'desc'  => __( 'If you are having issues with current version of Custom Library, you can rollback to a previous stable version.', 'analogwp-library' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_plugin_rollback_version',
				),
				array(
					'title'     => __( 'Rollback Custom Library', 'analogwp-library' ),
					'id'        => 'analog_custom_library_rollback_version_select_option',
					'type'      => 'select',
					'class'     => 'analog-enhanced-select',
					'desc_tip'  => true,
					'options'   => $this->get_rollback_versions(),
					'is_option' => false,
				),
				array(
					'id'    => 'analog_custom_library_rollback_version_button',
					'type'  => 'button',
					'class' => 'analog-custom-library-rollback-version-button analog-custom-library-button button-secondary',
					'value' => __( 'Reinstall this version', 'analogwp-library' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_plugin_rollback',
				)
			);
		}

		$settings = apply_filters(
			'analog_custom_library_version_control_settings',
			$rollback_controls
		);

		return apply_filters( 'analog_custom_library_get_settings_' . $this->id, $settings );
	}

	/**
	 * Get recent rollback versions in key/value pair.
	 *
	 * @return array
	 */
	public function get_rollback_versions() {
		$keys = Rollback::get_rollback_versions();
		$data = array();
		foreach ( $keys as $key => $value ) {
			$data[ $value ] = $value;
		}

		return $data;
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

return new Version_Control();
