<?php
/**
 * Analog General Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Settings\Admin_Settings;
use AnalogWP\CustomLibrary\Settings\Settings_Page;

defined( 'ABSPATH' ) || exit;

/**
 * General.
 */
class General extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'custom-library-for-elementor' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		global $current_section;

		$settings = array();

		if ( '' === $current_section ) {

			$settings = array(
				array(
					'title' => esc_html__( 'General Settings', 'custom-library-for-elementor' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_general_settings',
				),
				array(
					'id'      => 'hide_elementor_template_library',
					'desc'    => __( 'Hide default Elementor Template library icon from editor.', 'custom-library-for-elementor' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'id'      => 'allow_svg_uploads',
					'desc'    => esc_html_x( 'Enable SVG Uploads', 'settings title', 'custom-library-for-elementor' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_general_settings',
				),
				array(
					'type'  => 'title',
					'title' => esc_html__( 'Placeholder image', 'custom-library-for-elementor' ),
					'id'    => 'analog_custom_library_change_default_placeholder_thumb',
				),
				array(
					'desc'    => __( 'Replace the default placeholder image.', 'custom-library-for-elementor' ),
					'id'      => 'default-placeholder-thumb',
					'default' => AGWP_LIBRARY_PLUGIN_URL . 'assets/img/placeholder.svg',
					'type'    => 'media-image',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_change_default_placeholder_thumb',
				),
			);
			$settings = apply_filters( 'analog_custom_library_' . $this->id . '_settings', $settings );
		}

		return apply_filters( 'analog_custom_library_get_settings_' . $this->id, $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		Admin_Settings::save_fields( $settings );
		if ( $current_section ) {
			do_action( 'analog_custom_library_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

return new General();
