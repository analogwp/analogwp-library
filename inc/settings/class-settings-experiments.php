<?php
/**
 * Analog Experiments Settings.
 *
 * @package Analog/Admin
 * @since 1.9.0
 */

namespace Analog\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Experiments Control.
 */
class Experiments extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'experiments';
		$this->label = __( 'Experiments', 'ang' );
		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$options = array(
			'default'  => __( 'Default', 'ang' ),
			'active'   => __( 'Active', 'ang' ),
			'inactive' => __( 'Inactive', 'ang' ),
		);

		$settings = apply_filters(
			'ang_experiments_settings',
			array(
				array(
					'title' => __( 'Style Kits Experiments', 'ang' ),
					'type'  => 'title',
					'id'    => 'ang_experiments',
				),
				array(
					'title'   => __( 'Container-based Library', 'ang' ),
					'desc'    => __( 'Get access to the container-based library of Patterns. You need to have the Containers Feature activated in Elementor, to test the new library.', 'ang' ),
					'id'      => 'container_library_experiment',
					'default' => 'default',
					'type'    => 'select',
					'options' => $options,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'ang_beta',
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

return new Experiments();
