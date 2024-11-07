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
			array(
				array(
					'title' => esc_html__( 'Library popup style', 'ang' ),
					'type'  => 'title',
					'id'    => 'ang_library_popup_style',
				),
				array(
					'id'      => 'library_popup_style',
					'type'    => 'radio',
					'options' => array(
						'compact'     => __( 'Compact (popup)', 'ang' ),
						'full-screen' => __( 'Fullscreen', 'ang' ),
					),
					'default' => 'compact',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'ang_library_popup_style',
				),
				array(
					'title' => esc_html__( 'Template columns', 'ang' ),
					'type'  => 'title',
					'id'    => 'ang_library_template_columns',
				),
				array(
					'id'      => 'library_template_columns',
					'type'    => 'radio',
					'options' => array(
						'2c'   => __( '2 Columns', 'ang' ),
						'3c'   => __( '3 Columns', 'ang' ),
						'auto' => __( 'Auto', 'ang' ),
					),
					'default' => '3c',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'ang_library_template_columns',
				),
				array(
					'title' => esc_html__( 'Categories location', 'ang' ),
					'type'  => 'title',
					'id'    => 'ang_library_categories_location',
				),
				array(
					'id'      => 'library_categories_location',
					'type'    => 'radio',
					'options' => array(
						'vertical'        => __( 'Sidebar', 'ang' ),
						'horizontal'      => __( 'Horizontal', 'ang' ),
						'hide-categories' => __( 'None', 'ang' ),
					),
					'default' => 'horizontal',
				),
				array(
					'id'      => 'show_library_categories_template_count',
					'desc'	  => esc_html__( 'Show categories template count', 'ang' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'ang_library_categories_location',
				),
			)
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
