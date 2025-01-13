<?php
/**
 * Elementor core integration.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary;

use AnalogWP\CustomLibrary\Core\Library_Init;
use Elementor\Core\Common\Modules\Finder\Categories_Manager;

/**
 * Intializes scripts/styles needed for AnalogWP modal on Elementor editing page.
 */
class Elementor {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Initiate Library.
		new Library_Init();

		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_editor_scripts' ) );

		add_action(
			'elementor/finder/register',
			static function ( Categories_Manager $categories_manager ) {
				include_once AGWP_LIBRARY_PLUGIN_DIR . 'inc/Elementor/class-finder-shortcuts.php';
				$categories_manager->register( new Finder_Shortcuts() );
			}
		);

	}

	/**
	 * Load styles and scripts for Elementor modal.
	 *
	 * @return void
	 */
	public function enqueue_editor_scripts() {

		// Independent components.
		wp_enqueue_style( 'analog-custom-library-components-css', AGWP_LIBRARY_PLUGIN_URL . 'assets/css/library-components.css', array(), filemtime( AGWP_LIBRARY_PLUGIN_DIR . 'assets/css/library-components.css' ) );

		do_action( 'analog_custom_library_loaded_templates' );

		wp_enqueue_script( 'analog-custom-library-elementor-modal', AGWP_LIBRARY_PLUGIN_URL . 'assets/js/elementor-modal.js', array( 'jquery' ), filemtime( AGWP_LIBRARY_PLUGIN_DIR . 'assets/js/elementor-modal.js' ), false );
		wp_enqueue_style( 'analog-custom-library-elementor-modal', AGWP_LIBRARY_PLUGIN_URL . 'assets/css/elementor-modal.css', array( 'dashicons' ), filemtime( AGWP_LIBRARY_PLUGIN_DIR . 'assets/css/elementor-modal.css' ) );

		wp_enqueue_script(
			'analog-custom-library-app',
			AGWP_LIBRARY_PLUGIN_URL . 'assets/js/app/index.js',
			array(
				'react',
				'react-dom',
				'jquery',
				'wp-components',
				'wp-hooks',
				'wp-i18n',
				'wp-api-fetch',
				'wp-html-entities',
			),
			filemtime( AGWP_LIBRARY_PLUGIN_DIR . 'assets/js/app/index.js' ),
			true
		);
		wp_set_script_translations( 'analog-custom-library-app', 'analogwp-library', AGWP_LIBRARY_PLUGIN_DIR . 'languages' );

		wp_enqueue_style( 'wp-components' );

		wp_enqueue_style( 'analog-custom-library-google-fonts', 'https://fonts.googleapis.com/css?family=Inter:400,500,600,700&display=swap', array(), '20221016' );

		$l10n = apply_filters( // phpcs:ignore
			'analog/library/app/strings',
			array(
				'is_settings_page' => false,
			)
		);

		wp_localize_script( 'analog-custom-library-app', 'AGWP_LIBRARY', $l10n );

		Utils::enqueue_settings_toggle_css();
	}
}

new Elementor();
