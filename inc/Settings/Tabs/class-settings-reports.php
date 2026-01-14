<?php
/**
 * PROMO Reports Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Settings\Settings_Page;

defined( 'ABSPATH' ) || exit;

if ( class_exists( '\AnalogWP\CustomLibraryPro\Plugin' ) ) {
	return;
}

/**
 * Reports.
 */
class Reports extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'reports-promo';
		$this->label = __( 'Reports', 'analogwp-library' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters(
			'analog_custom_library_reports_settings',
			array(
				array(
					'title' => esc_html__( 'Template Import Reports', 'analogwp-library' ),
					'desc'  => esc_html__( 'Track and analyze template imports across your sites. Monitor usage patterns and gain insights into your template library.', 'analogwp-library' ),
					'type'  => 'promo-title',
					'id'    => 'analog_custom_library_promo_reports_settings',
				),
				array(
					'id'      => 'reports_tracking',
					'title'   => esc_html__( 'Import Tracking', 'analogwp-library' ),
					'type'    => 'promo-radio',
					'default' => 'enabled',
					'options' => array(
						'enabled'  => sprintf(
							'<b>%s</b><p class="option-label">%s</p>',
							esc_html__( 'Enabled', 'analogwp-library' ),
							esc_html__( 'Track all template imports including template name, import time, and client site details.', 'analogwp-library' )
						),
						'disabled' => sprintf(
							'<b>%s</b><p class="option-label">%s</p>',
							esc_html__( 'Disabled', 'analogwp-library' ),
							esc_html__( 'Do not track template imports.', 'analogwp-library' )
						),
					),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_promo_reports_features',
				),
				array(
					'title' => esc_html__( 'More controls', 'analogwp-library' ),
					'desc'  => esc_html__( 'Unlock powerful reporting features with Pro including detailed import logs, client site tracking, export capabilities, and automatic data cleanup options.', 'analogwp-library' ),
					'type'  => 'promo-title',
					'id'    => 'analog_custom_library_promo_reports_features',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_promo_reports_features_end',
				),
			)
		);

		return apply_filters( 'analog_custom_library_get_settings_' . $this->id, $settings );
	}
}

return new Reports();
