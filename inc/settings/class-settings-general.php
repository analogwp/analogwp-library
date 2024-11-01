<?php
/**
 * Analog General Settings
 *
 * @package Analog/Admin
 * @since 1.3.8
 */

namespace Analog\Settings;

use Analog\Utils;
use Analog\API\Remote;

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
		$this->label = __( 'General', 'ang' );

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
					'title' => esc_html__( 'General Settings', 'ang' ),
					'type'  => 'title',
					'id'    => 'ang_general_settings',
				),
				array(
					'id'      => 'allow_svg_uploads',
					'title'   => esc_html_x( 'Enable SVG Uploads', 'settings title', 'ang' ),
					'desc'    => sprintf(
					/* translators: %s: Global Style Kit Documentation link */
						__( 'Helps importing SVGs in templates. %s', 'ang' ),
						'<a href="https://analogwp.com/docs/enable-svg-imports-in-patterns" target="_blank">' . __( 'Read more', 'ang' ) . '</a>'
					),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'    => 'onboarding_link',
					'title' => esc_html_x( 'Setup', 'settings title', 'ang' ),
					'desc'  => __( 'Trigger the setup wizard manually', 'ang' ),
					'to'    => admin_url( 'admin.php?page=analog_onboarding' ),
					'type'  => 'button',
					'class' => 'ang-button button-secondary',
					'value' => __( 'Restart wizard', 'ang' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'ang_general_settings',
				),
			);
			$settings = apply_filters( 'ang_' . $this->id . '_settings', $settings );
		}

		return apply_filters( 'ang_get_settings_' . $this->id, $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		Admin_Settings::save_fields( $settings );
		if ( $current_section ) {
			do_action( 'ang_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

return new General();
