<?php
/**
 * Class for importing a template.
 *
 * @package AnalogWP
 */

namespace Elementor\TemplateLibrary;

use Analog\API\Remote;
use Analog\Core\Data\Library_Data;
use Analog\Formatter;
use Analog\Plugin;
use Elementor\TemplateLibrary\Classes\Images;
use Analog\Utils;

/**
 * Class Analog_Importer.
 *
 * @package Elementor\TemplateLibrary
 */
class Analog_Importer extends Source_Remote {
	/**
	 * Analog_Importer constructor.
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_crop_image' ) ) {
			include ABSPATH . 'wp-admin/includes/image.php';
		}
	}

	/**
	 * Get local template data.
	 *
	 * @inheritDoc
	 *
	 * @param array       $args    Custom template arguments.
	 * @param string      $context Optional. The context. Default is `display`.
	 * @param object|bool $data Template/block import data.
	 *
	 * @since 1.4.0 $data was added.
	 *
	 * @return array Remote Template data.
	 */
	public function get_local_data( array $args, $context = 'display', $data = false ) {
		if ( ! $data ) {
			$data = Library_Data::prepare_template_content( $args['template_id'] );
		}

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		Plugin::elementor()->editor->set_edit_mode( true );

		$data['content'] = $this->replace_elements_ids( $data['content'] );
		$data['content'] = $this->process_export_import_content( $data['content'], 'on_import' );

		$post_id  = $args['editor_post_id'];
		$document = Plugin::elementor()->documents->get( $post_id );
		if ( $document ) {
			$data['content'] = $document->get_elements_raw_data( $data['content'], true );
		}

		/**
		 * During json encode/decode between preview/demo, isInner is usually converted into string.
		 * This helper function converts it back to Boolean so Elementor doesn't change this control
		 * into an "Inner Section".
		 */
		$data['content'] = Utils::convert_string_to_boolean( $data['content'] );

		return $data;
	}
}
