<?php
/**
 * Analog Elementor Tools.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Elementor;

use AnalogWP\CustomLibrary\Base;
use AnalogWP\CustomLibrary\Utils;
use Elementor\Rollback;

/**
 * Analog Elementor Tools.
 *
 * @package AnalogWP\CustomLibrary\Elementor
 */
class Tools extends Base {
	/**
	 * Fetch documents.
	 *
	 * Holds the list of all documents fetched currently.
	 *
	 * @var array
	 */
	protected $documents;

	/**
	 * Tools constructor.
	 */
	public function __construct() {
	}

	/**
	 * Handle WP_Error message.
	 *
	 * @access private
	 *
	 * @param string $message Error message.
	 */
	private function handle_wp_error( $message ) {
		_default_wp_die_handler( $message, 'Custom Library for Elementor' );
	}

	/**
	 * Checks if current screen is Custom Library for Elementor CPT screen.
	 *
	 * @deprecated 1.6.0
	 *
	 * @return bool
	 */
	public static function is_tokens_screen() {
		global $current_screen;

		if ( ! $current_screen ) {
			return false;
		}

		return 'edit' === $current_screen->base && 'analog_custom_library_tokens' === $current_screen->post_type;
	}

	/**
	 * Returns a link to make a Style Kit Global.
	 *
	 * @access private
	 * @return string
	 */
	private function get_stylekit_global_link() {
		return add_query_arg(
			array(
				'action'  => 'analog_custom_library_make_global',
				'post_id' => get_the_ID(),
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Fetch a post.
	 *
	 * @param int|string $id Post ID.
	 *
	 * @return mixed
	 */
	protected function get_post( $id ) {
		if ( ! isset( $this->documents[ $id ] ) ) {
			$this->documents[ $id ] = get_post( $id );
		}

		return $this->documents[ $id ];
	}
}

Tools::get_instance();
