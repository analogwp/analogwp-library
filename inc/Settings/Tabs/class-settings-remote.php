<?php
/**
 * PROMO Remote Library Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Tabs;

use AnalogWP\CustomLibrary\Settings\Settings_Page;

defined( 'ABSPATH' ) || exit;

if ( class_exists( '\AnalogWP\CustomLibraryPro\Plugin' ) ) {
	return;
}

/**
 * Remote.
 */
class Remote extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'remote-promo';
		$this->label = __( 'Remote Library', 'analogwp-library' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters(
			'analog_custom_library_remote_settings',
			array(
				array(
					'title' => esc_html__( 'Remote Library Settings', 'analogwp-library' ),
					'desc'  => sprintf(
						'%s <a href="%s" target="_blank">%s</a>',
						esc_html__( 'Configure your library to share templates with other sites (Server mode) or connect to remote libraries (Client mode).', 'analogwp-library' ),
						'https://analogwp.com/cl-docs/remote-library/?utm_source=plugin&utm_medium=link&utm_campaign=cl_remote_settings_learn_more',
						esc_html__( 'Learn more', 'analogwp-library' )
					),
					'type'  => 'promo-title',
					'id'    => 'analog_custom_library_promo_remote_settings',
				),
				array(
					'id'      => 'remote_mode',
					'title'   => esc_html__( 'Operating Mode', 'analogwp-library' ),
					'type'    => 'promo-radio',
					'default' => 'standalone',
					'options' => array(
						'standalone' => sprintf(
							'<b>%s</b><p class="option-label">%s</p>',
							esc_html__( 'Standalone', 'analogwp-library' ),
							esc_html__( 'Local library only. Templates are stored and used on this site only.', 'analogwp-library' )
						),
						'server'     => sprintf(
							'<b>%s</b><p class="option-label">%s</p>',
							esc_html__( 'Server', 'analogwp-library' ),
							esc_html__( 'Share your library with other sites. Generate a connection-code for clients to connect to this remote library server.', 'analogwp-library' )
						),
						'client'     => sprintf(
							'<b>%s</b><p class="option-label">%s</p>',
							esc_html__( 'Client', 'analogwp-library' ),
							esc_html__( 'Connect to remote library servers to access templates from another site.', 'analogwp-library' )
						),
					),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_promo_remote_more_settings',
				),
				array(
					'title' => esc_html__( 'More controls', 'analogwp-library' ),
					'desc'  => esc_html__( 'A plethora of controls to serve your own template library remotely or access remote libraries, such as Secure Connection-code, Connected Sites, Reports and more. ', 'analogwp-library' ),
					'type'  => 'promo-title',
					'id'    => 'analog_custom_library_promo_remote_more_settings',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'analog_custom_library_promo_remote_more_settings',
				),
			)
		);

		return apply_filters( 'analog_custom_library_get_settings_' . $this->id, $settings );
	}
}

return new Remote();
