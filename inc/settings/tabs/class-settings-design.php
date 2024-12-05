<?php
/**
 * Analog Design Settings.
 *
 * @package AnalogWP/CustomLibrary/Admin
 */

namespace AnalogWP\CustomLibrary\Settings;

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
		$this->label = __( 'Design', 'custom-library-for-elementor' );
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
					'title' => esc_html__( 'Library popup style', 'custom-library-for-elementor' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_popup_style',
				),
				array(
					'id'      => 'library_popup_style',
					'type'    => 'radio',
					'options' => array(
						'compact'     => __( 'Compact (popup)', 'custom-library-for-elementor' ),
						'full-screen' => __( 'Fullscreen', 'custom-library-for-elementor' ),
					),
					'default' => 'compact',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_popup_style',
				),
				array(
					'title' => esc_html__( 'Template columns', 'custom-library-for-elementor' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_template_columns',
				),
				array(
					'id'      => 'library_template_columns',
					'type'    => 'radio',
					'options' => array(
						'2c'   => __( '2 Columns', 'custom-library-for-elementor' ),
						'3c'   => __( '3 Columns', 'custom-library-for-elementor' ),
						'auto' => __( 'Auto', 'custom-library-for-elementor' ),
					),
					'default' => '3c',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_template_columns',
				),
				array(
					'title' => esc_html__( 'Categories location', 'custom-library-for-elementor' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_categories_location',
				),
				array(
					'id'      => 'library_categories_location',
					'type'    => 'radio',
					'options' => array(
						'vertical'        => __( 'Sidebar', 'custom-library-for-elementor' ),
						'horizontal'      => __( 'Horizontal', 'custom-library-for-elementor' ),
						'hide-categories' => __( 'None', 'custom-library-for-elementor' ),
					),
					'default' => 'horizontal',
				),
				array(
					'id'      => 'show_library_categories_template_count',
					'desc'	  => esc_html__( 'Show categories template count', 'custom-library-for-elementor' ),
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
