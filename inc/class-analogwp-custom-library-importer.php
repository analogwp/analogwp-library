<?php
/**
 * Class for importing a template.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace Elementor\TemplateLibrary;

use AnalogWP\CustomLibrary\Core\Data\Library_Data;
use AnalogWP\CustomLibrary\Plugin;
use Elementor\TemplateLibrary\Classes\Images;
use AnalogWP\CustomLibrary\Utils;

/**
 * Class AnalogWP_Custom_Library_Importer.
 *
 * @package Elementor\TemplateLibrary
 */
class AnalogWP_Custom_Library_Importer extends Source_Remote {
	/**
	 * Get local template data.
	 *
	 * @inheritDoc
	 *
	 * @param array       $args    Custom template arguments.
	 * @param string      $context Optional. The context. Default is `display`.
	 * @param object|bool $data Template/block import data.
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
