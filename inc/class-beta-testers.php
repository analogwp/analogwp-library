<?php
/**
 * Beta Testers.
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary;

/**
 * Class BetaTesters

 * @package AnalogWP\CustomLibrary
 */
class Beta_Testers {
	/**
	 * Transient key.
	 *
	 * Holds the "Custom Library for Elementor" beta testers transient key.
	 *
	 * @access private
	 * @static
	 *
	 * @var string Transient key.
	 */
	private $transient_key;

	/**
	 * Beta_Testers constructor.
	 */
	public function __construct() {
		if ( true !== Options::get_instance()->get( 'beta_tester' ) ) {
			return;
		}

		$this->transient_key = md5( 'analog_custom_library_beta_testers_response_key' );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_version' ) );
	}

	/**
	 * Get beta version.
	 *
	 * Retrieve beta version from wp.org plugin repository.
	 *
	 * @access private
	 *
	 * @return string|false Beta version or false.
	 */
	public function get_beta_version() {
		$beta_version = get_site_transient( $this->transient_key );

		if ( false === $beta_version ) {
			$beta_version = 'false';

			$response = wp_remote_get( 'https://plugins.svn.wordpress.org/custom-library-for-elementor/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				preg_match( '/Beta tag: (.*)/i', $response['body'], $matches );
				if ( isset( $matches[1] ) ) {
					$beta_version = $matches[1];
				}
			}

			set_site_transient( $this->transient_key, $beta_version, 6 * HOUR_IN_SECONDS );
		}

		return $beta_version;
	}

	/**
	 * Check version.
	 *
	 * Checks whether a beta version exist, and retrieve the version data.
	 *
	 * Fired by `pre_set_site_transient_update_plugins` filter, before WordPress
	 * runs the plugin update checker.
	 *
	 * @access public
	 *
	 * @param array $transient Plugin version data.
	 *
	 * @return array Plugin version data.
	 */
	public function check_version( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		delete_site_transient( $this->transient_key );

		$plugin_slug  = basename( AGWP_LIBRARY_PLUGIN_FILE, '.php' );
		$beta_version = $this->get_beta_version();

		if ( 'false' !== $beta_version && version_compare( $beta_version, AGWP_LIBRARY_VERSION, '>' ) ) {
			$response              = new \stdClass();
			$response->plugin      = $plugin_slug;
			$response->slug        = $plugin_slug;
			$response->new_version = $beta_version;
			$response->url         = 'https://analogwp.com/';
			$response->package     = sprintf( 'https://downloads.wordpress.org/plugin/custom-library-for-elementor.%s.zip', $beta_version );

			$transient->response[ AGWP_LIBRARY_PLUGIN_BASE ] = $response;
		}

		return $transient;
	}
}

new Beta_Testers();
