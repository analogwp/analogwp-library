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
					'requiredVersion' => false,
					'requiredPlugins' => isset( $meta->required_plugins ) ? (array) $meta->required_plugins : array(),
					'version'         => $meta->version ?? false,
				);
			}
		}

		/**
		 * Filter the templates list.
		 *
		 * Used by Client mode to merge remote templates with local templates.
		 *
		 * @param array $templates List of templates.
		 */
		return apply_filters( 'analog_library/templates', $templates );
	}

	/**
	 * Get outdated templates.
	 *
	 * @param bool $force Force refresh.
	 *
	 * @return array
	 */
	public static function get_outdated_templates( $force = false ) {
		$transient = get_transient( 'agwp_custom_library_outdated_templates' );

		if ( ! $force && isset( $transient ) ) {
			return $transient;
		}

		$all_templates = self::templates();

		$outdated_templates = array();

		$current_version = AGWP_LIBRARY_VERSION;

		foreach ( $all_templates as $template ) {
			if ( version_compare( $template['version'], $current_version, '<' ) ) {
				$outdated_templates[] = $template['id'];
			}
		}

		set_transient( 'agwp_custom_library_outdated_templates', $outdated_templates, DAY_IN_SECONDS * 7 );

		return $outdated_templates;
	}

	/**
	 * Get template data.
	 *
	 * @param int|string $template_id Template ID (or remote_{connection_id}_{template_id} for remote templates).
	 *
	 * @return array|\WP_Error
	 */
	public static function prepare_template_content( $template_id ) {
		if ( ! $template_id ) {
			return new \WP_Error( 'template_error', 'Invalid parameter(s).' );
		}

		// Check if this is a remote template (format: remote_{connection_id}_{remote_template_id}).
		// Remote templates are handled by Pro plugin via filter.
		if ( is_string( $template_id ) && 0 === strpos( $template_id, 'remote_' ) ) {
			/**
			 * Filter to handle remote template content.
			 *
			 * Pro plugin hooks into this to fetch remote template content.
			 *
			 * @param array|\WP_Error|null $content Template content or null if not handled.
			 * @param string $template_id Remote template ID.
			 */
			$remote_content = apply_filters( 'analog_library/remote_template_content', null, $template_id );

			if ( null !== $remote_content ) {
				return $remote_content;
			}

			// Pro plugin not active or not handling remote templates.
			return new \WP_Error( 'template_error', 'Remote Library requires the Pro plugin.' );
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

	/**
	 * Get all library categories with hierarchy information.
	 *
	 * Returns every term in the `elementor_library_category` taxonomy with its
	 * parent term ID so the client can reconstruct a nested tree.  Existing
	 * installations that haven't set up parent/child categories will receive a
	 * flat list (all `parent` values will be 0), which the front-end treats
	 * exactly like the previous flat-tab behaviour.
	 *
	 * @return array  Array of associative arrays: { id, name, slug, parent }.
	 */
	public static function get_categories() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'elementor_library_category',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		$categories = array();
		foreach ( $terms as $term ) {
			$categories[] = array(
				'id'     => $term->term_id,
				'name'   => $term->name,
				'slug'   => $term->slug,
				'parent' => $term->parent,
			);
		}

		/**
		 * Filter the category tree.
		 *
		 * Pro plugin hooks into this to merge remote server category hierarchies
		 * with the local categories.  Each entry is { id, name, slug, parent }.
		 *
		 * @param array $categories Array of category entries.
		 */
		return apply_filters( 'analog_library/categories', $categories );
	}

	/**
	 * Get all template ids.
	 *
	 * @return array
	 */
	public static function get_template_ids() {
		$templates_db   = new Templates_DB();
		$templates_data = $templates_db->get_templates();

		$templates = array();

		if ( count( $templates_data ) ) {
			foreach ( $templates_data as $template ) {
				$templates[] = (int) $template->template_id;
			}
		}

		return $templates;
	}
}
