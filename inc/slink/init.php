<?php

namespace Analog\Slink;

use Analog\Slink\Data\Templates_DB;
use Elementor\TemplateLibrary\Source_Local;

class Init {
	protected Templates_DB $db_patterns;

	public function __construct() {
		$this->db_patterns  = new Templates_DB();

		$this->hooks();

	}

	public function hooks() {
		add_action( 'save_post_elementor_library', array( $this, 'handle_container_save' ), 20, 3 );

		//Register Meta box
		add_action( 'add_meta_boxes', function() {

			add_meta_box(
				'slink-id',
				'Sync to library',
				array( $this, 'wpdocs_field_cb' ),
				Source_Local::CPT,
				'side'
			);

		} );

//save meta value with save post hook
		add_action( 'save_post', function( $post_id ) {
			if ( isset( $_POST['slink_sync_to_library'] ) ) {
				update_post_meta( $post_id, 'slink_sync_to_library', $_POST['slink_sync_to_library'] );
			} else {
				update_post_meta( $post_id, 'slink_sync_to_library', 0 );
			}
		} );
	}


//Meta callback function
	public function wpdocs_field_cb( $post ) {
		$wpdocs_meta_val = get_post_meta( $post->ID, 'slink_sync_to_library', true );
		?>
		<label for="slink_sync_to_library"><input type="checkbox" name="slink_sync_to_library" id="slink_sync_to_library" value="1" <?php checked( $wpdocs_meta_val, 1 ); ?>>
			&nbsp;Alright, moment of truth!</label>
		<?php
	}

	public function prepare_pattern_for_push( $post_id ) {

		$tags             = get_the_terms( $post_id, 'elementor_library_category' );
		$keywords         = get_the_terms( $post_id, 'slink_keyword' );
		$required_plugins = get_post_meta( $post_id, 'required_plugins', true );

		$pattern_data = array(
			'id'               => (int) $post_id,
			'site_id'          => 0,
			'title'            => get_post_field( 'post_title', $post_id ),
			'thumbnail'        => get_the_post_thumbnail_url( $post_id, 'medium_large' ),
			'published'        => get_the_date( 'U', $post_id ),
			'modified'         => get_the_modified_date( 'U', $post_id ),
			'tags'             => ( ! is_wp_error( $tags ) && $tags ) ? wp_list_pluck( $tags, 'name' ) : false,
			'keywords'         => ( ! is_wp_error( $keywords ) && $keywords ) ? wp_list_pluck( $keywords, 'name' ) : false,
			'is_live'          => (bool) get_post_meta( $post_id, 'is_live', true ),
			'uses_style_kit'   => (bool) get_post_meta( $post_id, 'uses_style_kit', true ),
			'is_pro'           => (bool) get_post_meta( $post_id, 'is_pro', true ),
			'version'          => get_post_meta( $post_id, 'required_version', true ),
			'uses_container'   => (bool) get_post_meta( $post_id, 'uses_container', true ),
			'data'             => array(
				'content' => json_decode( get_post_meta( $post_id, '_elementor_data', true ) ),
			),
			'required_plugins' => $required_plugins,
		);

		return apply_filters( 'analog_pattern_data', $pattern_data, $post_id );
	}

	public function sync_pattern( $required_data ) {
		$data = $required_data;

		$pattern_data = array(
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
					'is_live'          => $data['is_live'],
					'is_pro'           => $data['is_pro'],
					'version'          => $data['version'],
					'uses_style_kit'   => false,
					'uses_container'   => true,
					'required_plugins' => $data['required_plugins'],
				)
			),
		);

		$exists = $this->db_patterns->pattern_exists( $data['id'], $data['site_id'] );
		if ( $exists ) {
			$this->db_patterns->update( $exists->id, $pattern_data );
		} else {
			$pattern_data['created_at'] = current_time( 'mysql' );
			$this->db_patterns->insert( $pattern_data );
		}

//		Utils::delete_api_cache();
	}

	public function handle_container_save( int $post_ID, \WP_Post $post, bool $update ) {
		if ( $post->post_status !== 'publish' ) {
			return;
		}

		$template_type = get_post_meta( $post_ID, '_elementor_template_type', true );

		if ( 'container' !== $template_type ) {
			return;
		}

		$sync = (bool) isset( $_POST['slink_sync_to_library'] ) ? 1 : 0;

		if ( ! $sync ) {
			return;
		}

		$transient_key = 'slink_push_pattern_' . $post->ID;
		if ( ! get_transient( $transient_key ) ) {
//			$data = array( 'id' => $post->ID );
//			$this->pattern_request->data( $data )->dispatch();
//			if ( ! \ang_preview_dev() ) {
//				$this->pattern_screenshot->data( $data )->dispatch();
//			}

			// first we prepare.
			$data = $this->prepare_pattern_for_push( $post_ID );

			// then we save in our custom db table.
			$this->sync_pattern( $data );

			ray( $data );

			set_transient( $transient_key, true, 5 );
		}
	}
}
