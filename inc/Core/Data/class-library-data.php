<?php
/**
 * Library Data handler.
 *
 * @package Analog Library
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
		$db_blocks      = new Templates_DB();
		$templates_data = $db_blocks->get_templates();
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
}
