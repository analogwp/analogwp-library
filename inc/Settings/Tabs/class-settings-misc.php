<?php
/**
 * Analog Miscelleneous Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Settings\Admin_Settings;
use AnalogWP\CustomLibrary\Settings\Settings_Page;

defined( 'ABSPATH' ) || exit;

/**
 * Misc.
 */
class Misc extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'misc';
		$this->label = __( 'Misc', 'analogwp-library' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters(
			'analog_custom_library_misc_settings',
			array(
				array(
					'type' => 'title',
					'id'   => 'analog_custom_library_misc',
				),
				array(
					'title'         => __( 'Remove Data on Uninstall', 'analogwp-library' ),
					'desc'          => __( 'Check this box to remove all data stored by Custom Library for Elementor plugin, including license info, user settings, import history etc.', 'analogwp-library' ),
					'id'            => 'remove_on_uninstall',
					'default'       => false,
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_misc',
				),
			)
		);

		return apply_filters( 'analog_custom_library_get_settings_' . $this->id, $settings );
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

return new Misc();
