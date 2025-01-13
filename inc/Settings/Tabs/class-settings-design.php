<?php
/**
 * Analog Design Settings.
 *
 * @package AnalogWP/CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Settings\Admin_Settings;
use AnalogWP\CustomLibrary\Settings\Settings_Page;

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
		$this->label = __( 'Design', 'analogwp-library' );
		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = apply_filters(
			'analog_custom_library_experiments_settings',
			array(
				array(
					'title' => esc_html__( 'Library popup style', 'analogwp-library' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_popup_style',
				),
				array(
					'id'      => 'library_popup_style',
					'type'    => 'radio',
					'options' => array(
						'compact'     => __( 'Compact (popup)', 'analogwp-library' ),
						'full-screen' => __( 'Fullscreen', 'analogwp-library' ),
					),
					'default' => 'compact',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_popup_style',
				),
				array(
					'title' => esc_html__( 'Template columns', 'analogwp-library' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_template_columns',
				),
				array(
					'id'      => 'library_template_columns',
					'type'    => 'radio',
					'options' => array(
						'2c'   => __( '2 Columns', 'analogwp-library' ),
						'3c'   => __( '3 Columns', 'analogwp-library' ),
						'auto' => __( 'Auto', 'analogwp-library' ),
					),
					'default' => '3c',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_template_columns',
				),
				array(
					'title' => esc_html__( 'Categories location', 'analogwp-library' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_categories_location',
				),
				array(
					'id'      => 'library_categories_location',
					'type'    => 'radio',
					'options' => array(
						'vertical'        => __( 'Sidebar', 'analogwp-library' ),
						'horizontal'      => __( 'Horizontal', 'analogwp-library' ),
						'hide-categories' => __( 'None', 'analogwp-library' ),
					),
					'default' => 'horizontal',
				),
				array(
					'id'      => 'show_library_categories_template_count',
					'desc'	  => esc_html__( 'Show categories template count', 'analogwp-library' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_categories_location',
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

return new Design();
