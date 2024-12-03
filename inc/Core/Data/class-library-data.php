<?php
/**
 * Library Data handler.
 *
 * @package Custom Library for Elementor
 */

namespace Analog\Core\Data;

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

				if ( isset( $meta->is_live ) ) {
					$is_live = (bool) $meta->is_live;
					if ( ! $is_live ) {
						continue;
					}
				} else {
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
}
