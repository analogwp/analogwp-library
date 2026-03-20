<?php
/**
 * Settings onboarding.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings;

use AnalogWP\CustomLibrary\Core\Library_Manager;
use AnalogWP\CustomLibrary\Options;
use AnalogWP\CustomLibrary\Settings\Tabs\Design;

defined( 'ABSPATH' ) || exit;

/**
 * Handles onboarding for brand-new users.
 */
class Onboarding {
	const NONCE_ACTION      = 'analog_custom_library_onboarding';
	const DEBUG_QUERY_ARG   = 'onboarding';
	const DEBUG_QUERY_VALUE = 'debug';
	const TEMPLATE_LIMIT    = 5;

	/**
	 * Determine whether onboarding should be shown.
	 *
	 * @return bool
	 */
	public static function should_render() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( self::is_debug_request() ) {
			return true;
		}

		$options = get_option( Options::OPTION_KEY );

		return ! is_array( $options ) || empty( $options );
	}

	/**
	 * Check whether onboarding is being forced in debug mode.
	 *
	 * @return bool
	 */
	public static function is_debug_request() {
		if ( ! defined( 'ANALOGWP_DEBUG' ) || ! ANALOGWP_DEBUG ) {
			return false;
		}

		if ( ! isset( $_GET[ self::DEBUG_QUERY_ARG ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		$query_value = sanitize_key( wp_unslash( $_GET[ self::DEBUG_QUERY_ARG ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return self::DEBUG_QUERY_VALUE === $query_value;
	}

	/**
	 * Get the onboarding URL for debug usage.
	 *
	 * @return string
	 */
	public static function get_debug_url() {
		return admin_url(
			add_query_arg(
				array(
					'page'                => Register_Settings::MENU_SLUG,
					self::DEBUG_QUERY_ARG => self::DEBUG_QUERY_VALUE,
				),
				'admin.php'
			)
		);
	}

	/**
	 * Handle onboarding completion.
	 *
	 * @return void
	 */
	public static function maybe_handle_request() {
		if ( empty( $_POST['analog_custom_library_finish_onboarding'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		check_admin_referer( self::NONCE_ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to manage Custom Library onboarding.', 'analogwp-library' ) );
		}

		$submitted_preset              = isset( $_POST['library_style_preset'] ) ? sanitize_text_field( wp_unslash( $_POST['library_style_preset'] ) ) : 'default'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$submitted_template_ids        = isset( $_POST['onboarding_template_ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['onboarding_template_ids'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$submitted_popup_style         = isset( $_POST['library_popup_style'] ) ? sanitize_text_field( wp_unslash( $_POST['library_popup_style'] ) ) : 'compact'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$submitted_template_columns    = isset( $_POST['library_template_columns'] ) ? sanitize_text_field( wp_unslash( $_POST['library_template_columns'] ) ) : '3c'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$submitted_categories_location = isset( $_POST['library_categories_location'] ) ? sanitize_text_field( wp_unslash( $_POST['library_categories_location'] ) ) : 'horizontal'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$options                       = Options::get_instance();
		$preset                        = self::sanitize_style_preset( $submitted_preset );

		$options->set( 'library_style_mode', 'preset' );
		$options->set( 'library_style_preset', $preset );
		$options->set( 'library_popup_style', self::sanitize_allowed( $submitted_popup_style, array( 'compact', 'full-screen' ), 'compact' ) );
		$options->set( 'library_template_columns', self::sanitize_allowed( $submitted_template_columns, array( '2c', '3c', 'auto' ), '3c' ) );
		$options->set( 'library_categories_location', self::sanitize_allowed( $submitted_categories_location, array( 'vertical', 'horizontal', 'hide-categories' ), 'horizontal' ) );

		$library_manager = new Library_Manager();

		foreach ( self::sanitize_template_ids( $submitted_template_ids ) as $template_id ) {
			$library_manager->add_template_to_library( $template_id );
		}

		wp_safe_redirect(
			admin_url(
				add_query_arg(
					array(
						'page' => Register_Settings::MENU_SLUG,
						'tab'  => 'general',
					),
					'admin.php'
				)
			)
		);
		exit;
	}

	/**
	 * Build the onboarding view data.
	 *
	 * @return array
	 */
	public static function get_view_data() {
		$templates = self::get_available_templates();

		return array(
			'docs_url'       => 'https://analogwp.com/cl-docs/?utm_source=plugin&utm_medium=referral&utm_campaign=onboarding',
			'has_templates'  => ! empty( $templates ),
			'style_presets'  => Design::get_style_preset_options(),
			'template_limit' => self::TEMPLATE_LIMIT,
			'templates'      => $templates,
		);
	}

	/**
	 * Sanitize a value against an allowed list, falling back to a default.
	 *
	 * @param string $value   Value to validate.
	 * @param array  $allowed Allowed values.
	 * @param string $default Fallback default.
	 * @return string
	 */
	private static function sanitize_allowed( $value, $allowed, $default ) {
		$value = sanitize_key( $value );

		return in_array( $value, $allowed, true ) ? $value : $default;
	}

	/**
	 * Sanitize the submitted preset.
	 *
	 * @param string $preset Submitted preset.
	 * @return string
	 */
	private static function sanitize_style_preset( $preset ) {
		$preset    = sanitize_key( $preset );
		$allowed   = array_keys( Design::get_style_preset_options() );
		$validated = in_array( $preset, $allowed, true ) ? $preset : 'default';

		return $validated;
	}

	/**
	 * Sanitize selected template ids.
	 *
	 * @param array $template_ids Submitted template ids.
	 * @return array
	 */
	private static function sanitize_template_ids( $template_ids ) {
		$available_ids = wp_list_pluck( self::get_available_templates(), 'id' );
		$template_ids  = array_map( 'absint', (array) $template_ids );

		return array_values( array_intersect( $template_ids, $available_ids ) );
	}

	/**
	 * Get templates available for onboarding import.
	 *
	 * @return array
	 */
	private static function get_available_templates() {
		$template_ids = get_posts(
			array(
				'post_type'              => 'elementor_library',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( empty( $template_ids ) ) {
			return array();
		}

		$templates = array();

		foreach ( $template_ids as $template_id ) {
			if ( 1 === (int) get_post_meta( $template_id, 'analog_custom_library_sync_to_library', true ) ) {
				continue;
			}

			if ( ! current_user_can( 'edit_post', $template_id ) ) {
				continue;
			}

			if ( self::is_kit_template( $template_id ) ) {
				continue;
			}

			$templates[] = array(
				'id'          => $template_id,
				'title'       => get_the_title( $template_id ),
				'type'        => self::get_template_type_label( $template_id ),
				'categories'  => self::get_template_categories_label( $template_id ),
				'preview_url' => self::get_template_preview_url( $template_id ),
			);
		}

		return $templates;
	}

	/**
	 * Determine whether a template is a kit.
	 *
	 * @param int $template_id Template ID.
	 * @return bool
	 */
	private static function is_kit_template( $template_id ) {
		$type_slug = self::get_template_type_slug( $template_id );

		return 'kit' === $type_slug;
	}

	/**
	 * Get a readable template type label.
	 *
	 * @param int $template_id Template ID.
	 * @return string
	 */
	private static function get_template_type_label( $template_id ) {
		$terms = get_the_terms( $template_id, 'elementor_library_type' );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			return implode( ', ', wp_list_pluck( $terms, 'name' ) );
		}

		$type = get_post_meta( $template_id, '_elementor_template_type', true );

		if ( empty( $type ) ) {
			return esc_html__( 'Unknown', 'analogwp-library' );
		}

		return ucwords( str_replace( array( '-', '_' ), ' ', sanitize_key( $type ) ) );
	}

	/**
	 * Get template categories as a readable label.
	 *
	 * @param int $template_id Template ID.
	 * @return string
	 */
	private static function get_template_categories_label( $template_id ) {
		$terms = get_the_terms( $template_id, 'elementor_library_category' );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return esc_html__( 'Uncategorized', 'analogwp-library' );
		}

		return implode( ', ', wp_list_pluck( $terms, 'name' ) );
	}

	/**
	 * Get the normalized template type slug.
	 *
	 * @param int $template_id Template ID.
	 * @return string
	 */
	private static function get_template_type_slug( $template_id ) {
		$terms = get_the_terms( $template_id, 'elementor_library_type' );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$primary_term = reset( $terms );

			if ( ! empty( $primary_term->slug ) ) {
				return sanitize_key( $primary_term->slug );
			}
		}

		$type = get_post_meta( $template_id, '_elementor_template_type', true );

		return empty( $type ) ? '' : sanitize_key( $type );
	}

	/**
	 * Get the preview URL for a template.
	 *
	 * @param int $template_id Template ID.
	 * @return string
	 */
	private static function get_template_preview_url( $template_id ) {
		$preview_url = get_permalink( $template_id );

		if ( ! $preview_url ) {
			$preview_url = get_preview_post_link( $template_id );
		}

		return $preview_url ? $preview_url : '';
	}
}
