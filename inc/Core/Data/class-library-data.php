<?php
/**
 * Library Data handler.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Core\Data;

/**
 * Class Library_Data.
 */
final class Library_Data {

	/**
	 * Get templates from library.
	 *
	 * @return array
	 */
	public static function templates() {
		$templates_db   = new Templates_DB();
		$templates_data = $templates_db->get_templates();
		$templates      = array();

		if ( count( $templates_data ) ) {
			foreach ( $templates_data as $template ) {
				$meta = json_decode( $template->meta );

				$template_id = (int) $template->template_id;

				// If the original template doesn't exist,
				// delete from Custom Library and then continue from next template.
				if ( ! self::original_template_exists( $template_id ) ) {
					$exists = $templates_db->template_exists( $template_id );
					if ( $exists ) {
						$templates_db->delete( $exists->id, $template_id );
					}
					continue;
				}

				$thumbnail = false;
				if ( '0' !== $meta->thumbnail ) {
					$thumbnail = $meta->thumbnail;
				}

				$modified = isset( $meta->modified ) ? $meta->modified : $meta->published;

				$templates[] = array(
					'id'              => (int) $template->template_id,
					'siteID'          => (int) $template->site_id,
					'title'           => $template->title,
					'thumbnail'       => $thumbnail,
					'published'       => (int) $meta->published,
					'modified'        => (int) $modified,
					'popularityIndex' => (int) $template->installs,
					'is_pro'          => (bool) $meta->is_pro,
					'tags'            => (array) $meta->tags,
					'keywords'        => isset( $meta->keywords ) ? (array) $meta->keywords : array(),
					'requiredVersion' => $meta->version ?? false,
					'requiredPlugins' => isset( $meta->required_plugins ) ? (array) $meta->required_plugins : array(),
				);
			}
		}

		return $templates;
	}

	/**
	 * Get template data.
	 *
	 * @param int $template_id Template ID.
	 *
	 * @return array|\WP_Error
	 */
	public static function prepare_template_content( $template_id ) {
		if ( ! $template_id ) {
			return new \WP_Error( 'template_error', 'Invalid parameter(s).' );
		}

		$templates_db = new Templates_DB();

		$template = $templates_db->get_template_content( $template_id );

		if ( ! $template ) {
			return new \WP_Error( 'template_content_error', 'No content found for this template. This is most probably due to invalid ID.' );
		}

		return array( 'content' => json_decode( $template->content, true ) );
	}

	/**
	 * Determines if the template in Elementor library exists, identified by the specified ID, exist
	 * within the WordPress database.
	 *
	 * @param    int $id    The ID of the post to check
	 * @return   bool          True if the template exists; otherwise, false.
	 * @since    1.0.3
	 */
	public static function original_template_exists( $id ) {
		return is_string( get_post_status( $id ) );
	}
}
