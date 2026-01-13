<?php
/**
 * Analog Library Setting Tools
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Plugin;
use AnalogWP\CustomLibrary\Settings\Admin_Settings;
use AnalogWP\CustomLibrary\Settings\Settings_Page;
use AnalogWP\CustomLibrary\Core\Data\Library_Data;
use AnalogWP\CustomLibrary\Core\Library_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Tools.
 */
class Tools extends Settings_Page {
	/**
	 * Update outdated templates action name.
	 */
	const UPDATE_OUTDATED_TEMPLATES_ACTION = 'analog_custom_library_update_outdated_templates';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'tools';
		$this->label = __( 'Tools', 'analogwp-library' );

		parent::__construct();

		// Add global data for settings page.
		add_filter( 'analog_custom_library_settings_data', array( $this, 'update_settings_globals' ) );
		// Hook at update outdated templates ajax action only logged-in.
		add_action( 'wp_ajax_' . self::UPDATE_OUTDATED_TEMPLATES_ACTION, array( $this, 'handle_update_outdated_templates_action' ) );
	}

	/**
	 * Updates script globals available in the settings.
	 *
	 * @param array $data Script globals.
	 *
	 * @return array
	 */
	public function update_settings_globals( $data ) {
		$data['update_outdated_templates_action']      = self::UPDATE_OUTDATED_TEMPLATES_ACTION;
		$data['update_outdated_templates_url']         = admin_url( 'admin-ajax.php' );
		$data['update_outdated_templates_nonce']       = wp_create_nonce( self::UPDATE_OUTDATED_TEMPLATES_ACTION );
		$data['update_outdated_templates_success_txt'] = __( 'Updated successfully', 'analogwp-library' );
		$data['update_outdated_templates_error_txt']   = __( 'Update failed! Please try again', 'analogwp-library' );

		return $data;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$outdated_templates_count = $this->get_outdated_templates_count();

		$button_label = __( 'Run Update', 'analogwp-library' );
		if ( $outdated_templates_count ) {
			$button_label = sprintf(
				/* translators: %s: Number of templates to update. */
				__( '%1$s → %2$s template(s)', 'analogwp-library' ),
				$button_label,
				$outdated_templates_count
			);
		}

		$settings = apply_filters(
			'analog_custom_library_tools_settings',
			array(
				array(
					'id'    => 'analog_custom_library_tools_title',
					'type'  => 'title',
					'title' => __( 'Update Library Templates', 'analogwp-library' ),
				),
				array(
					'id'                 => 'update_outdated_templates',
					'type'               => 'action-button',
					'title'              => __( 'This action will update any templates found to be outdated in the Custom Library from their source template in Elementor template library.', 'analogwp-library' ),
					'desc'               => __( 'Note: Outdated templates are templates that were created before the current version of this plugin. You only need to run this when you think there is a problem in the Custom Library.', 'analogwp-library' ),
					'button_label'       => $button_label,
					'button_reset_label' => __( 'Run Update', 'analogwp-library' ),
				),
				array(
					'id'   => 'analog_custom_library_tools_title',
					'type' => 'sectionend',
				),
			)
		);

		if ( ! Plugin::instance()->has_pro_active() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'type'  => 'promo-title',
						'title' => esc_html__( 'Templates Importer', 'analogwp-library' ),
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
	 * Get outdated templates count.
	 *
	 * @param bool $force Force refresh.
	 *
	 * @return int
	 */
	public function get_outdated_templates_count( $force = false ) {
		$outdated_templates = Library_Data::get_outdated_templates( $force );
		if ( ! is_array( $outdated_templates ) ) {
			return 0;
		}
		return count( $outdated_templates );
	}

	/**
	 * Handles the custom library update templates action.
	 *
	 * @since 1.4.0
	 * @access public
	 */
	public function handle_update_outdated_templates_action() {
		check_ajax_referer( self::UPDATE_OUTDATED_TEMPLATES_ACTION, '_wp_nonce' );

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

		if ( self::UPDATE_OUTDATED_TEMPLATES_ACTION === $action ) {

			// TODO: Update custom library templates based on Elementor template library.
			$outdated_templates = Library_Data::get_outdated_templates( true );

			if ( ! is_array( $outdated_templates ) ) {
				wp_send_json_success(
					array(
						'message' => __( 'No outdated templates found.', 'analogwp-library' ),
					)
				);
			}

			$library_manager = Library_Manager::get_instance();

			$updated_templates = array();

			foreach ( $outdated_templates as $template_id ) {
				if ( $library_manager->add_template_to_library( $template_id ) ) {
					$updated_templates[] = $template_id;
				}
			}

			wp_send_json_success( array( 'updated_templates' => $updated_templates ) );
		}

		wp_send_json_error(
			array(
				'message' => __( 'Failed to update outdated templates.', 'analogwp-library' ),
			)
		);
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		// Only runs at Tools page and is to refresh the count.
		$this->get_outdated_templates_count( true );

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

return new Tools();
