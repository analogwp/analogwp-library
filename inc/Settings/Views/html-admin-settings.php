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
	wp_safe_redirect( admin_url( 'admin.php?page=analog-custom-library-settings' ) );
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
					echo '<a href="' . esc_html( admin_url( 'admin.php?page=analog-custom-library-settings&tab=' . esc_attr( $slug ) ) ) . '" class="analog-custom-library-nav-tab ' . ( $current_tab === $slug ? 'analog-custom-library-nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
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
					<li><a href="https://analogwp.com/all-access-pass/?utm_source=plugin&utm_medium=referral&utm_campaign=settings-sidebar" target="_blank">[LTD] All Access Pass</a></li>
					<?php if ( ! Plugin::instance()->has_pro_active() ) : ?>
						<li><a href="edit.php?post_type=elementor_library&page=analog-custom-library-settings-addons">Get Custom Library Pro</a></li>
					<?php endif; ?>
				</ul>
			</div>

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
