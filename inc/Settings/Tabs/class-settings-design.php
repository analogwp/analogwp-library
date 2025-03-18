<?php
/**
 * Analog Design Settings.
 *
 * @package AnalogWP/CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Plugin;
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
					'desc'    => esc_html__( 'Show categories template count', 'analogwp-library' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_categories_location',
				),
			)
		);

		if ( ! Plugin::instance()->has_pro_active() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => __( 'Library title', 'analogwp-library' ),
						'type'  => 'promo-title',
						'id'    => 'analog_custom_library_title_text',
					),
					array(
						'title'   => '',
						'id'      => 'promo_library_title_text',
						'default' => __( 'Library', 'analogwp-library' ),
						'type'    => 'promo-text',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'analog_custom_library_title_text',
					),
					array(
						'title' => __( 'Show Buttons on hover', 'analogwp-library' ),
						'type'  => 'promo-title',
						'id'    => 'analog_custom_library_show_buttons',
					),
					array(
						'title'   => '',
						'id'      => 'show_buttons_on_hover',
						'default' => array(
							'show_preview' => true,
							'show_edit'    => true,
						),
						'type'    => 'promo-multi-checkbox',
						'options' => array(
							'show_preview' => __( 'Show Preview Button', 'analogwp-library' ),
							'show_edit'    => __( 'Show Edit Button', 'analogwp-library' ),
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'analog_custom_library_show_buttons',
					),
					array(
						'title' => __( 'Header colors', 'analogwp-library' ),
						'type'  => 'promo-title',
						'id'    => 'analog_custom_library_header_colors',
					),
					array(
						'title'   => __( 'Header Background', 'analogwp-library' ),
						'id'      => 'header_bg_color',
						'default' => '#4D45BD',
						'type'    => 'promo-color',
					),
					array(
						'title'   => __( 'Header Text', 'analogwp-library' ),
						'id'      => 'header_txt_color',
						'default' => '#ffffff',
						'type'    => 'promo-color',
					),
					array(
						'title'   => __( 'Header Border Bottom', 'analogwp-library' ),
						'id'      => 'header_border_color',
						'default' => '#DFDFDF',
						'type'    => 'promo-color',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'analog_custom_library_header_colors',
					),
					array(
						'title' => __( 'Categories colors', 'analogwp-library' ),
						'type'  => 'promo-title',
						'id'    => 'analog_custom_library_categories_colors',
					),
					array(
						'title'   => __( 'Categories Background', 'analogwp-library' ),
						'id'      => 'categories_bg_color',
						'default' => '#FFFFFF',
						'type'    => 'promo-color',
					),
					array(
						'title'   => __( 'Categories Text', 'analogwp-library' ),
						'id'      => 'categories_txt_color',
						'default' => '#252525',
						'type'    => 'promo-color',
					),
					array(
						'title'   => __( 'Active Category Text', 'analogwp-library' ),
						'id'      => 'categories_active_txt_color',
						'default' => '#4D45BD',
						'type'    => 'promo-color',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'analog_custom_library_categories_colors',
					),
					array(
						'title' => __( 'Button styles', 'analogwp-library' ),
						'type'  => 'promo-title',
						'id'    => 'analog_custom_library_button_colors',
					),
					array(
						'title'   => __( 'Button Background Color', 'analogwp-library' ),
						'id'      => 'button_bg_color',
						'default' => '#4D45BD',
						'type'    => 'promo-color',
					),
					array(
						'title'   => __( 'Button Text Color', 'analogwp-library' ),
						'id'      => 'button_txt_color',
						'default' => '#ffffff',
						'type'    => 'promo-color',
					),
					array(
						'title'   => __( 'Button Border Color', 'analogwp-library' ),
						'id'      => 'button_border_color',
						'default' => '#4D45BD',
						'type'    => 'promo-color',
					),
					array(
						'title'   => __( 'Button Border Radius', 'analogwp-library' ),
						'id'      => 'button_border_radius',
						'default' => 5,
						'type'    => 'promo-number',
					),
					array(
						'title'   => __( 'Button Border Width', 'analogwp-library' ),
						'id'      => 'button_border_width',
						'default' => 1,
						'type'    => 'promo-number',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'analog_custom_library_button_colors',
					),
				)
			);
		}

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
