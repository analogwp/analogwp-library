<?php
/**
 * Analog General Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Plugin;
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
		$this->label = __( 'General', 'analogwp-library' );

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
					'title' => esc_html__( 'General Settings', 'analogwp-library' ),
					'type'  => 'title',
					'id'    => 'analog_custom_library_general_settings',
				),
				array(
					'id'      => 'hide_elementor_template_library',
					'desc'    => __( 'Hide Default library icon', 'analogwp-library' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'id'      => 'hide_elementor_library_popup',
					'desc'    => __( 'Disable Default auto-popup', 'analogwp-library' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'id'      => 'allow_svg_uploads',
					'desc'    => esc_html_x( 'Enable SVG Uploads', 'settings title', 'analogwp-library' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_general_settings',
				),
				array(
					'type'  => 'title',
					'title' => esc_html__( 'Placeholder image', 'analogwp-library' ),
					'id'    => 'analog_custom_library_change_default_placeholder_thumb',
				),
				array(
					'desc'    => __( 'Replace the default placeholder image.', 'analogwp-library' ),
					'id'      => 'default-placeholder-thumb',
					'default' => AGWP_LIBRARY_PLUGIN_URL . 'assets/img/placeholder.svg',
					'type'    => 'media-image',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_change_default_placeholder_thumb',
				),
			);

			if ( ! Plugin::instance()->has_pro_active() ) {
				$settings = array_merge(
					$settings,
					array(
						array(
							'type'  => 'promo-title',
							'title' => esc_html__( 'Trigger icon', 'analogwp-library' ),
							'id'    => 'analog_custom_library_pro_trigger_icon',
						),
						array(
							'desc'    => __( 'Replace the trigger icon.', 'analogwp-library' ),
							'id'      => 'trigger_icon',
							'default' => AGWP_LIBRARY_PLUGIN_URL . 'assets/img/icon.svg',
							'type'    => 'promo-media-image',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'analog_custom_library_pro_trigger_icon',
						),
						array(
							'type'  => 'promo-title',
							'title' => esc_html__( 'Library Access', 'analogwp-library' ),
							'id'    => 'analog_custom_library_pro_library_access',
						),
						array(
							'id'      => 'library_visibility',
							'type'    => 'promo-radio',
							'default' => 'show_to_all',
							'options' => array(
								'show_to_all'   => esc_html__( 'Show to All', 'analogwp-library' ),
								'show_to_roles' => esc_html__( 'Show only to specific roles', 'analogwp-library' ),
								'show_to_users' => esc_html__( 'Show only to specific users', 'analogwp-library' ),
							),
						),
						array(
							'type' => 'sectionend',
							'id'   => 'analog_custom_library_pro_library_access',
						),
						array(
							'type'  => 'promo-title',
							'title' => esc_html__( 'Settings Access', 'analogwp-library' ),
							'desc'  => __( 'Please note, by default all administrators have settings access even if it is not explicitly set here to avoid accidental lockouts.', 'analogwp-library' ),
							'id'    => 'analog_custom_library_pro_settings_access',
						),
						array(
							'id'      => 'settings_visibility',
							'type'    => 'promo-radio',
							'default' => 'show_to_all',
							'options' => array(
								'show_to_all'   => esc_html__( 'Show to All', 'analogwp-library' ),
								'show_to_roles' => esc_html__( 'Show only to specific roles', 'analogwp-library' ),
								'show_to_users' => esc_html__( 'Show only to specific users', 'analogwp-library' ),
							),
						),
						array(
							'type' => 'sectionend',
							'id'   => 'analog_custom_library_pro_settings_access',
						),
					)
				);
			}
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
