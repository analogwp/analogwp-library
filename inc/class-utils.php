<?php
/**
 * Utility class.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary;

use AnalogWP\CustomLibrary\Core\Storage\Transients;

/**
 * Helper functions.
 *
 * @package AnalogWP\CustomLibrary
 */
class Utils extends Base {

	/**
	 * Transients object.
	 *
	 * @var Transients
	 */
	private $transients;

	/**
	 * Utils constructor.
	 */
	public function __construct() {
		if ( ! $this->transients ) {
			$this->transients = new Transients();
		}
	}

	/**
	 * Debugging log.
	 *
	 * @param mixed $log Log data.
	 * @return void
	 */
	public static function log( $log ) {
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			return;
		}

		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) ); // @codingStandardsIgnoreLine
		} else {
			error_log( $log ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Clear cache.
	 *
	 * Delete all meta containing files data. And delete the actual
	 * files from the upload directory.
	 *
	 * @access public
	 */
	public static function clear_elementor_cache() {
		Plugin::elementor()->files_manager->clear_cache();
	}

	/**
	 * Convert string to boolean.
	 *
	 * @param array $data Array object.
	 * @return array
	 */
	public static function convert_string_to_boolean( $data ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		array_walk_recursive(
			$data,
			function ( &$value, $key ) {
				if ( 'isInner' === $key || 'isLinked' === $key ) {
					$value = (bool) $value;
				}
			}
		);

		return $data;
	}

	/**
	 * Check if the installed version of Elementor is older than a specified version.
	 *
	 * @param string $version Version number.
	 *
	 * @return bool
	 */
	public static function is_elementor_pre( $version ) {
		if ( ! defined( 'ELEMENTOR_VERSION' ) || version_compare( ELEMENTOR_VERSION, $version, '<' ) ) {
			$elementor_is_pre_version = true;
		} else {
			$elementor_is_pre_version = false;
		}

		return $elementor_is_pre_version;
	}

	/**
	 * Returns true if Elementor Container experiment is on.
	 *
	 * @return bool
	 */
	public static function is_elementor_container() {
		$flexbox_container           = get_option( 'elementor_experiment-container' );
		$is_flexbox_container_active = \Elementor\Core\Experiments\Manager::STATE_ACTIVE === $flexbox_container;

		if ( 'default' === $flexbox_container ) {
			$experiments                 = new \Elementor\Core\Experiments\Manager();
			$is_flexbox_container_active = $experiments->is_feature_active( 'container' );
		}

		if ( ! $is_flexbox_container_active ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns true if Container experiment is on.
	 *
	 * @return bool
	 */
	public static function is_container() {
		return self::is_elementor_container();
	}

	/**
	 * Returns true if the Pro plugin is active.
	 *
	 * @return bool
	 */
	public static function has_pro() {
		return defined( 'ANG_PRO_PLUGIN_BASE' );
	}

	/**
	 * Returns true if Pro license is active.
	 *
	 * @return bool
	 */
	public static function is_pro() {
		$status = Options::get_instance()->get( 'analog_custom_library_license_key_status' );
		return self::has_pro() && 'valid' === $status;
	}

	/**
	 * Enqueues settings toggle via inline css.
	 *
	 * @return void
	 */
	public static function enqueue_settings_toggle_css() {
		$options = Options::get_instance();
		$css     = '';

		// Hide Elementor Library icon.
		$hide_elementor_library = $options->get( 'hide_elementor_template_library' );
		$hide_elementor_library = $hide_elementor_library ? 'none' : 'inherit';

		$library_popup_style = $options->get( 'library_popup_style' );

		$css .= ".elementor-add-template-button {
					display: {$hide_elementor_library};
				}";

		if ( isset( $library_popup_style ) && 'full-screen' === $library_popup_style ) {
			$css .= '#analog-custom-library-modal .dialog-widget-content {
				width: 100vw !important;
				height: 100vh !important;
			}';
		}

		wp_add_inline_style(
			'analog-custom-library-components-css',
			$css
		);
	}
}

new Utils();
