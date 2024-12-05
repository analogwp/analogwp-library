<?php
/**
 * Elementor Finder shortcuts for Analog.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary;

use Elementor\Core\Common\Modules\Finder\Base_Category;

/**
 * Finder_Shortcuts class.
 */
class Finder_Shortcuts extends Base_Category {
	/**
	 * Get ID.
	 *
	 * @access public
	 * @return string
	 */
	public function get_id() {
		return 'analog-custom-library-shortcuts';
	}

	/**
	 * Get title.
	 *
	 * @access public
	 * @return string
	 */
	public function get_title() {
		return __( 'Custom Library for Elementor Shortcuts', 'custom-library-for-elementor' );
	}

	/**
	 * Get category items.
	 *
	 * @access public
	 * @param array $options Old options.
	 * @return array
	 */
	public function get_category_items( array $options = array() ) {
		return array(
			'library'    => array(
				'title'    => __( 'Templates Library', 'custom-library-for-elementor' ),
				'url'      => admin_url( 'admin.php?page=analog_custom_library' ),
				'icon'     => 'library-download',
				'keywords' => array( 'analog', 'library', 'settings' ),
			),
			'settings'   => array(
				'title'    => __( 'Settings', 'custom-library-for-elementor' ),
				'url'      => admin_url( 'admin.php?page=analog-custom-library-settings' ),
				'icon'     => 'settings',
				'keywords' => array( 'analog', 'settings' ),
			),
			'style-kits' => array(
				'title'    => __( 'Theme Custom Library for Elementor', 'custom-library-for-elementor' ),
				'url'      => admin_url( 'admin.php?page=style-kits' ),
				'icon'     => 'settings',
				'keywords' => array( 'analog', 'style', 'kits' ),
			),
		);
	}
}
