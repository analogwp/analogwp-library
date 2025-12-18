<?php
/**
 * Admin View: Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Views;

use AnalogWP\CustomLibrary\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_exists        = isset( $tabs[ $current_tab ] ) || has_action( 'analog_custom_library_sections_' . $current_tab ) || has_action( 'analog_custom_library_settings_' . $current_tab ) || has_action( 'analog_custom_library_settings_tabs_' . $current_tab );
$current_tab_label = $tabs[ $current_tab ] ?? '';

global $current_user;

if ( ! $tab_exists ) {
	wp_safe_redirect( admin_url( 'edit.php?post_type=elementor_library&page=analog-custom-library-settings' ) );
	exit;
}
?>
<div class="wrap ang-custom-library <?php echo esc_attr( $current_tab ); ?>">
	<h1 class="menu-title"><?php esc_html_e( 'Library Settings', 'analogwp-library' ); ?></h1>
	<div class="analog-custom-library-wrapper">
		<form method="<?php echo esc_attr( apply_filters( 'analog_custom_library_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
			<nav class="nav-tab-wrapper analog-custom-library-nav-tab-wrapper">
				<?php

				foreach ( $tabs as $slug => $label ) {
					echo '<a href="' . esc_html( admin_url( 'edit.php?post_type=elementor_library&page=analog-custom-library-settings&tab=' . esc_attr( $slug ) ) ) . '" class="analog-custom-library-nav-tab ' . ( $current_tab === $slug ? 'analog-custom-library-nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
				}

				do_action( 'analog_custom_library_settings_tabs' );

				?>
			</nav>
			<div class="tab-content">
				<h1 class="screen-reader-text"><?php echo esc_html( $current_tab_label ); ?></h1>
				<?php
					do_action( 'analog_custom_library_sections_' . $current_tab );

					self::show_messages();

					do_action( 'analog_custom_library_settings_' . $current_tab );
				?>
				<p class="submit">
					<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
						<button name="save" class="button-primary analog-custom-library-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'analogwp-library' ); ?>"><?php esc_html_e( 'Save changes', 'analogwp-library' ); ?></button>
					<?php endif; ?>
					<?php wp_nonce_field( 'analog-custom-library-settings' ); ?>
				</p>
			</div>
		</form>
		<div class="sidebar">
			<?php do_action( 'analog_custom_library_sidebar_start' ); ?>

			<div class="plugin-banner">
				<div class="header">
					<div class="brand">
						<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect width="48" height="48" rx="24" fill="#4D45BD"/>
							<path fill-rule="evenodd" clip-rule="evenodd" d="M31.1282 14.0869H14.0869V31.1282H16.1219V16.1217H31.1282V14.0869Z" fill="white"/>
							<path fill-rule="evenodd" clip-rule="evenodd" d="M34.4349 17.3933H17.3936V34.4346H34.4349V17.3933Z" fill="white"/>
						</svg>
						<div>
							<h4>Custom Library for Elementor</h4>
							<p class="version"><?php echo esc_html( AGWP_LIBRARY_VERSION ); ?></p>
							<?php do_action( 'analog_custom_library_sidebar_plugin_info_section' ); ?>
						</div>
					</div>
				</div>
				<ul class="feature-list">
					<li><a href="https://analogwp.com/custom-library-for-elementor/?utm_source=plugin&utm_medium=referral&utm_campaign=settings-sidebar" target="_blank">Explore Custom Library Features</a></li>
					<!-- <li><a href="https://analogwp.com/all-access-pass/?utm_source=plugin&utm_medium=referral&utm_campaign=settings-sidebar" target="_blank">[LTD] All Access Pass</a></li> -->
				</ul>
			</div>

			<?php if ( ! Plugin::instance()->has_pro_active() ) : ?>
				<div class="upgrade-box special">
					<h3>🔥 Upgrade to Custom Library PRO with a Special Discount</h3>

					<p>Get additional features like <strong>Custom Branding/White-Label, Import/Export Templates, Role-Based Access Controls, Priority support and so much more</strong> while helping us support its development and maintenance.</p>

					<form id="js-ang-custom-library-request-discount" method="post">
						<input required type="email" class="regular-text" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php esc_attr_e( 'Your Email', 'analogwp-library' ); ?>">
						<input required type="text" class="regular-text" name="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>" placeholder="<?php esc_attr_e( 'First Name', 'analogwp-library' ); ?>">
						<input type="submit" class="button" style="width:100%" value="<?php esc_attr_e( 'Send me the coupon', 'analogwp-library' ); ?>" data-default-label="<?php esc_attr_e( 'Send me the coupon', 'analogwp-library' ); ?>">
						<p class="ang-discount-response"><span></span></p>
					</form>

					<p>
						<?php
						printf(
								/* translators: %s: Link to AnalogWP privacy policy. */
							esc_html__( 'By submitting your details, you agree to our %s.', 'analogwp-library' ),
							'<a target="_blank" href="https://analogwp.com/privacy-policy/">' . esc_html__( 'privacy policy', 'analogwp-library' ) . '</a>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<div class="help-box">
					<div>
						<h3>🙋 Looking for help or a feature to request?</h3>
					</div>

					<div>
						<?php if ( Plugin::instance()->has_pro_active() ) : ?>
							<a class="button button-secondary" href="<?php echo admin_url( 'edit.php?post_type=elementor_library&page=analog-custom-library-settings-account' ); ?>">Account</a>
						<?php endif; ?>
						<a class="button button-secondary" href="<?php echo admin_url( 'edit.php?post_type=elementor_library&page=analog-custom-library-settings-contact' ); ?>">Create a Support Request</a>
					</div>
				</div>

			<?php do_action( 'analog_custom_library_sidebar_end' ); ?>
		</div>
	</div>
</div>
