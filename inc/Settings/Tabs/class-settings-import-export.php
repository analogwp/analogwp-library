<?php
/**
 * Analog Import and Export Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Plugin;
use AnalogWP\CustomLibrary\Settings\Admin_Settings;
use AnalogWP\CustomLibrary\Settings\Settings_Page;

defined( 'ABSPATH' ) || exit;

/**
 * Import and Export.
 */
class Import_Export extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'import-export';
		$this->label = __( 'Importer / Exporter', 'analogwp-library' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters(
			'analog_custom_library_import_export_settings',
			array()
		);

		if ( ! Plugin::instance()->has_pro_active() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'type'  => 'promo-title',
						'title' => esc_html__( 'Templates Importer', 'analogwp-library-pro' ),
						'id'    => 'analog_custom_library_pro_import_templates_title',
					),
					array(
						'type' => 'promo-import-templates',
						'desc' => esc_html__( 'Imports .json or .zip files exported only via the Custom Library Pro templates exporter.', 'analogwp-library' ),
						'id'   => 'analog_custom_library_pro_import_templates',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'analog_custom_library_pro_import_templates_title',
					),
					array(
						'type'  => 'promo-title',
						'title' => esc_html__( 'Templates Exporter', 'analogwp-library' ),
						'id'    => 'analog_custom_library_pro_export_templates_title',
					),
					array(
						'type' => 'promo-export-templates',
						'desc' => esc_html__( 'Exports all the templates published and available in the Custom Library.', 'analogwp-library' ),
						'id'   => 'analog_custom_library_pro_export_templates',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'analog_custom_library_pro_export_templates_title',
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

return new Import_Export();
