<?php
/**
 * APIs.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\API;

use AnalogWP\CustomLibrary\Plugin;
use AnalogWP\CustomLibrary\Base;
use AnalogWP\CustomLibrary\Options;
use AnalogWP\CustomLibrary\Utils;
use Elementor\TemplateLibrary\AnalogWP_Custom_Library_Importer;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use AnalogWP\CustomLibrary\Core\Data\Library_Data;

defined( 'ABSPATH' ) || exit;

/**
 * Local APIs.
 *
 * @package AnalogWP\CustomLibrary\API
 */
class Local extends Base {
	/**
	 * Local constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Register API endpoints.
	 *
	 * @return void
	 */
	public function register_endpoints() {
		$endpoints = array(
			'/import/elementor'        => array(
				WP_REST_Server::CREATABLE => 'handle_import',
			),
			'/import/elementor/direct' => array(
				WP_REST_Server::CREATABLE => 'handle_direct_local_import',
			),
			'/templates'               => array(
				WP_REST_Server::READABLE => 'library_templates_list',
			),
			'/mark_favorite/'          => array(
				WP_REST_Server::CREATABLE => 'mark_as_favorite',
			),
			'/get/settings/'           => array(
				WP_REST_Server::READABLE => 'get_settings',
			),
			'/update/settings/'        => array(
				WP_REST_Server::CREATABLE => 'update_setting',
			),
			'/blocks/insert'           => array(
				WP_REST_Server::CREATABLE => 'get_template_content',
			),
		);

		foreach ( $endpoints as $endpoint => $details ) {
			foreach ( $details as $method => $callback ) {
				register_rest_route(
					'agwp-library/v1',
					$endpoint,
					array(
						'methods'             => $method,
						'callback'            => array( $this, $callback ),
						'permission_callback' => array( $this, 'rest_permission_check' ),
						'args'                => array(),
					)
				);
			}
		}
	}

	/**
	 * Check if a given request has access to update a setting
	 *
	 * @return WP_Error|bool
	 */
	public function rest_permission_check() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Handle template import.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_import( WP_REST_Request $request ) {
		$template_id = $request->get_param( 'template_id' );
		$editor_id   = $request->get_param( 'editor_post_id' );
		$is_pro      = (bool) $request->get_param( 'is_pro' );
		$site_id     = $request->get_param( 'site_id' );

		if ( ! $template_id ) {
			return new WP_REST_Response( array( 'error' => 'Invalid Template ID.' ), 500 );
		}

		\update_post_meta( $editor_id, '_analog_custom_library_import_type', 'elementor' );
		\update_post_meta( $editor_id, '_analog_custom_library_template_id', $template_id );

		$obj  = new AnalogWP_Custom_Library_Importer();
		$data = $obj->get_local_data(
			array(
				'template_id'    => $template_id,
				'editor_post_id' => $editor_id,
				'method'         => 'elementor',
			)
		);

		return new WP_REST_Response( wp_json_encode( maybe_unserialize( $data ) ), 200 );
	}

	/**
	 * Mark a template or block as favorite.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function mark_as_favorite( WP_REST_Request $request ) {
		$type = Plugin::$user_meta_prefix;
		if ( ! empty( $request->get_param( 'type' ) ) && 'block' === $request->get_param( 'type' ) ) {
			$type = Plugin::$user_meta_block_prefix;
		}
		$id        = $request->get_param( 'id' );
		$favorite  = $request->get_param( 'favorite' );
		$favorites = get_user_meta( get_current_user_id(), $type, true );

		if ( ! $favorites ) {
			$favorites = array();
		}

		if ( $favorite ) {
			$favorites[ $id ] = $favorite;
		} elseif ( isset( $favorites[ $id ] ) ) {
			unset( $favorites[ $id ] );
		}

		$data                  = array();
		$data['id']            = $id;
		$data['action']        = $favorite;
		$data['update_status'] = update_user_meta( get_current_user_id(), $type, $favorites );
		$data['favorites']     = get_user_meta( get_current_user_id(), $type, true );

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Create page during import if opted.
	 *
	 * @param array $template Template data object.
	 * @param bool  $with_page Whether to install in Elementor library or Page CPT.
	 *
	 * @return int|WP_Error
	 */
	private function create_page( $template, $with_page = false ) {
		if ( ! $template ) {
			return new WP_Error( 'import_error', 'Invalid Template ID.' );
		}

		$args = array(
			'post_type'    => $with_page ? 'page' : 'elementor_library',
			'post_status'  => $with_page ? 'draft' : 'publish',
			'post_title'   => $with_page ? $with_page : 'AnalogWP: ' . $template['title'],
			'post_content' => '',
		);

		$new_post_id = wp_insert_post( $args );

		/**
		 * Small hack to later avoid loading default values in Elementor.
		 */
		if ( is_array( $template['tokens'] ) ) {
			$template['tokens']['analog_custom_library_recently_imported'] = 'yes';
		}
		\update_post_meta( $new_post_id, '_elementor_data', $template['content'] );
		\update_post_meta( $new_post_id, '_elementor_page_settings', wp_slash( $template['tokens'] ) );
		\update_post_meta( $new_post_id, '_elementor_template_type', $template['type'] );
		\update_post_meta( $new_post_id, '_elementor_edit_mode', 'builder' );

		if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {
			\update_post_meta( $new_post_id, '_analog_custom_library_import_type', $with_page ? 'page' : 'library' );
			\update_post_meta( $new_post_id, '_analog_custom_library_template_id', $template['id'] );
			\update_post_meta( $new_post_id, '_wp_page_template', ! empty( $template['page_template'] ) ? $template['page_template'] : 'elementor_canvas' );

			if ( ! $with_page ) {
				wp_set_object_terms( $new_post_id, ! empty( $template['elementor_library_type'] ) ? $template['elementor_library_type'] : 'page', 'elementor_library_type' );
			}

			return $new_post_id;
		}

		return new WP_Error( 'import_error', 'Unable to create page.' );
	}

	/**
	 * Creates a 'Section' for Elementor.
	 *
	 * @uses wp_insert_post()
	 *
	 * @param array  $block Block details.
	 * @param array  $data Block content.
	 * @param string $method Import method.
	 *
	 * @return int Post ID.
	 */
	private function create_section( array $block, $data, $method ) {
		$args = array(
			'post_title'   => 'AnalogWP: ' . $block['title'],
			'post_type'    => 'elementor_library',
			'post_status'  => 'publish',
			'post_content' => '',
		);

		$type = Utils::is_container() ? 'container' : 'section';

		$post_id = wp_insert_post( $args );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			\update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data['content'] ) ) );
			\update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
			\update_post_meta( $post_id, '_elementor_template_type', $type );
			\update_post_meta( $post_id, '_wp_page_template', 'default' );

			\update_post_meta( $post_id, '_analog_custom_library_import_type', $method );
			\update_post_meta(
				$post_id,
				'_analog_custom_library_template_id',
				array(
					'site_id' => $block['siteID'],
					'id'      => $block['id'],
				)
			);

			\wp_set_object_terms( $post_id, $type, 'elementor_library_type' );
		}

		return (int) $post_id;
	}

	/**
	 * Handle template imports from settings page.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @uses \Elementor\TemplateLibrary\AnalogWP_Custom_Library_Importer
	 * @uses Utils::convert_string_to_boolean()
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_direct_local_import( WP_REST_Request $request ) {
		$template  = $request->get_param( 'template' );
		$with_page = $request->get_param( 'with_page' );
		$site_id   = $request->get_param( 'site_id' );

		$method = $with_page ? 'page' : 'library';

		// Initiate template import.
		$obj = new AnalogWP_Custom_Library_Importer();

		$data = $obj->get_data(
			array(
				'template_id'    => $template['id'],
				'editor_post_id' => false,
				'method'         => $method,
			)
		);

		if ( ! is_array( $data ) ) {
			return new WP_Error( 'import_error', 'Error fetching template content.', $data );
		}

		// Attach template content to template array for later use.
		$template['content'] = wp_slash( wp_json_encode( $data['content'] ) );

		// Finally create the page.
		$page = $this->create_page( $template, $with_page );

		$data = array(
			'page' => $page,
		);

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Handle local template import.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_template_content( WP_REST_Request $request ) {
		$block  = $request->get_param( 'block' );
		$method = $request->get_param( 'method' );

		if ( ! $block ) {
			return new WP_Error( 'template_import_error', __( 'Invalid Template ID.', 'analogwp-library' ) );
		}

		$data = $this->process_block_import( $block, $method );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Process block import functionaliities.
	 *  1. Imports the remote template.
	 *  2. Then with retrieved content, creates a page.
	 *
	 * @uses \Elementor\TemplateLibrary\AnalogWP_Custom_Library_Importer
	 *
	 * @param array  $block Block data.
	 * @param string $method Import method.
	 *
	 * @return array|WP_Error
	 */
	protected function process_block_import( $block, $method = 'library' ) {

		$raw_data = Library_Data::prepare_template_content( $block['id'], $method );
		$importer = new AnalogWP_Custom_Library_Importer();

		$data = $importer->get_local_data(
			array(
				'editor_post_id' => false,
			),
			'display',
			$raw_data
		);

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( 'library' === $method ) {
			$page_id = $this->create_section( $block, $data, $method );

			$payload = array( 'id' => $page_id );
		} else {
			$payload = array( 'data' => $data );
		}

		return $payload;
	}

	/**
	 * Get plugin settings.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings() {
		$options = Options::get_instance()->get();

		return new WP_REST_Response( $options, 200 );
	}

	/**
	 * Update plugin settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_setting( WP_REST_Request $request ) {
		$key   = $request->get_param( 'key' );
		$value = $request->get_param( 'value' );

		if ( ! $key ) {
			return new WP_Error( 'settings_error', __( 'No options key provided.', 'analogwp-library' ) );
		}

		Options::get_instance()->set( $key, $value );

		return new WP_REST_Response(
			array( 'message' => __( 'Setting updated.', 'analogwp-library' ) ),
			200
		);
	}

	/**
	 * Get templates library.
	 *
	 * @param \WP_REST_Request $request WP REST request instance.
	 * @return array
	 */
	public function library_templates_list( \WP_REST_Request $request ) {
		return array(
			'library' => array(
				'blocks'    => Library_Data::templates(),
				'templates' => array(),
			),
		);
	}
}

new Local();
