<?php
/**
 * Admin View: Settings
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\views;

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
<div class="wrap ang <?php echo esc_attr( $current_tab ); ?>">
	<h1 class="menu-title"><?php esc_html_e( 'Library Settings', 'custom-library-for-elementor' ); ?></h1>
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
						<button name="save" class="button-primary analog-custom-library-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'custom-library-for-elementor' ); ?>"><?php esc_html_e( 'Save changes', 'custom-library-for-elementor' ); ?></button>
					<?php endif; ?>
					<?php wp_nonce_field( 'analog-custom-library-settings' ); ?>
				</p>
			</div>
		</form>
		<div class="sidebar">
			<?php do_action( 'analog_custom_library_sidebar_start' ); ?>
			<?php do_action( 'analog_custom_library_sidebar_end' ); ?>
		</div>
	</div>
</div>
