<?php
/**
 * Library initialization.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Core;

use AnalogWP\CustomLibrary\Core\Data\Templates_DB;
use Elementor\TemplateLibrary\Source_Local;

/**
 * Class Library_Init.
 */
class Library_Init {
	/**
	 * Holds Template DB instance.
	 *
	 * @var Templates_DB $templates_db
	 */
	protected Templates_DB $templates_db;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->templates_db = new Templates_DB();

		$this->hooks();
	}

	/**
	 * Registered hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'save_post_elementor_library', array( $this, 'handle_template_sync' ), 30, 3 );

		// Register Meta box.
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );

		// Save meta value with save post hook.
		add_action( 'save_post_elementor_library', array( $this, 'handle_save_meta_boxes' ), 20 );
	}

	/**
	 * Registers metaboxes.
	 *
	 * @return void
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'custom-library-for-elementor-id',
			esc_html__( 'Custom Library', 'custom-library-for-elementor' ),
			array( $this, 'render_library_metabox' ),
			Source_Local::CPT,
			'side'
		);
	}

	/**
	 * Handles meta box data saving.
	 *
	 * @param int $post_ID Post ID.
	 * @return void
	 */
	public function handle_save_meta_boxes( int $post_ID ) {
		if ( ! isset( $_POST['analog_custom_library_meta_nonce'] ) ) {
			return;
		}

		check_admin_referer( 'custom-library-for-elementor-meta', 'analog_custom_library_meta_nonce' );

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_ID ) ) {
			return;
		}

		$keys = array(
			'analog_custom_library_sync_to_library',
		);

		foreach ( $keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_ID, $key, absint( $_POST[ $key ] ) );
			} else {
				update_post_meta( $post_ID, $key, 0 );
			}
		}
	}

	/**
	 * Renders library metabox.
	 *
	 * @param int $post Post ID.
	 * @return void
	 */
	public function render_library_metabox( $post ) {
		$sync_to_library  = get_post_meta( $post->ID, 'analog_custom_library_sync_to_library', true );

		ob_start();
		wp_nonce_field( 'custom-library-for-elementor-meta', 'analog_custom_library_meta_nonce' );
		?>
		<div>
			<label for="analog_custom_library_sync_to_library"><input type="checkbox" name="analog_custom_library_sync_to_library" id="analog_custom_library_sync_to_library" value="1" <?php checked( $sync_to_library, 1 ); ?>>
				&nbsp;Add to library</label>
		</div>
		<?php
		// HTML is included. Ignoring!
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Prepare template data for saving in Library DB.
	 *
	 * @param int $post_id Post ID.
	 * @return mixed|null
	 */
	public function prepare_template_for_save( $post_id ) {

		$tags             = get_the_terms( $post_id, 'elementor_library_category' );
		$keywords         = get_the_terms( $post_id, 'analog_custom_library_keyword' );
		$required_plugins = get_post_meta( $post_id, 'required_plugins', true );

		$template_data = array(
			'id'               => (int) $post_id,
			'site_id'          => 0,
			'title'            => get_post_field( 'post_title', $post_id ),
			'thumbnail'        => get_the_post_thumbnail_url( $post_id, 'medium_large' ),
			'published'        => get_the_date( 'U', $post_id ),
			'modified'         => get_the_modified_date( 'U', $post_id ),
			'tags'             => ( ! is_wp_error( $tags ) && $tags ) ? wp_list_pluck( $tags, 'name' ) : false,
			'keywords'         => ( ! is_wp_error( $keywords ) && $keywords ) ? wp_list_pluck( $keywords, 'name' ) : false,
			'is_pro'           => (bool) get_post_meta( $post_id, 'is_pro', true ),
			'version'          => get_post_meta( $post_id, 'required_version', true ),
			'uses_container'   => (bool) get_post_meta( $post_id, 'uses_container', true ),
			'data'             => array(
				'content' => json_decode( get_post_meta( $post_id, '_elementor_data', true ) ),
			),
			'required_plugins' => $required_plugins,
		);

		return apply_filters( 'analog_custom_library_template_data', $template_data, $post_id );
	}

	/**
	 * Sync template data with library db.
	 *
	 * @param array $required_data Template data.
	 * @return void
	 */
	public function sync_template( $required_data ) {
		$data = $required_data;

		$template_data = array(
			'template_id' => $data['id'],
			'site_id'     => $data['site_id'],
			'title'       => $data['title'],
			'content'     => isset( $data['data'] ) ? wp_json_encode( $data['data']['content'] ) : false,
			'updated_at'  => current_time( 'mysql' ),
			'meta'        => wp_json_encode(
				array(
					'thumbnail'        => $data['thumbnail'],
					'published'        => $data['published'],
					'modified'         => $data['modified'],
					'tags'             => $data['tags'],
					'keywords'         => $data['keywords'],
					'is_pro'           => $data['is_pro'],
					'version'          => $data['version'],
					'uses_container'   => true,
					'required_plugins' => $data['required_plugins'],
				)
			),
		);

		$exists = $this->templates_db->template_exists( $data['id'], $data['site_id'] );
		if ( $exists ) {
			$this->templates_db->update( $exists->id, $template_data );
		} else {
			$template_data['created_at'] = current_time( 'mysql' );
			$this->templates_db->insert( $template_data );
		}
	}

	/**
	 * Remove template from library.
	 *
	 * @param int $template_id Post ID of the template.
	 * @return bool
	 */
	public function remove_template_from_library( $template_id ) {
		$exists = $this->templates_db->template_exists( $template_id );
		if ( $exists ) {
			return $this->templates_db->delete( $exists->id );
		}
		return false;
	}

	/**
	 * Handles syncing templates.
	 *
	 * @param int      $post_ID
	 * @param \WP_Post $post
	 * @param bool     $update
	 * @return void
	 */
	public function handle_template_sync( int $post_ID, \WP_Post $post, bool $update ) {
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		$sync = (bool) isset( $_POST['analog_custom_library_sync_to_library'] ) ? 1 : 0;

		if ( ! $sync ) {
			// Delete if template exists in library.
			$this->remove_template_from_library( $post_ID );
			return;
		}

		$transient_key = 'analog_custom_library_push_template_' . $post->ID;
		if ( ! get_transient( $transient_key ) ) {
			// First we prepare.
			$data = $this->prepare_template_for_save( $post_ID );

			// Save in our Database table.
			$this->sync_template( $data );

			set_transient( $transient_key, true, 5 );
		}
	}
}
