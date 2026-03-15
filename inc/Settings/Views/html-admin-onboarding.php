<?php
/**
 * Admin View: Onboarding
 *
 * @package AnalogWP\CustomLibrary
 */

namespace AnalogWP\CustomLibrary\Settings\Views;

use AnalogWP\CustomLibrary\Settings\Onboarding;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="analog-custom-library-onboarding" id="analog-custom-library-onboarding-form" data-has-templates="<?php echo esc_attr( $onboarding_data['has_templates'] ? '1' : '0' ); ?>">
	<div class="analog-custom-library-onboarding__intro">
		<span class="analog-custom-library-onboarding__eyebrow"><?php esc_html_e( 'Welcome to Custom Library', 'analogwp-library' ); ?></span>
		<h2><?php esc_html_e( 'Set up your library in a few steps', 'analogwp-library' ); ?></h2>
		<p><?php esc_html_e( 'Choose a style preset and optionally pull in your existing Elementor templates.', 'analogwp-library' ); ?></p>
	</div>

	<ol class="analog-custom-library-onboarding__steps">
		<li class="analog-custom-library-onboarding__step is-active" data-step-indicator="1">
			<span>1</span>
			<strong><?php esc_html_e( 'Choose style', 'analogwp-library' ); ?></strong>
		</li>
		<?php if ( $onboarding_data['has_templates'] ) : ?>
			<li class="analog-custom-library-onboarding__step" data-step-indicator="2">
				<span>2</span>
				<strong><?php esc_html_e( 'Import templates', 'analogwp-library' ); ?></strong>
			</li>
		<?php endif; ?>
		<li class="analog-custom-library-onboarding__step" data-step-indicator="3">
			<span><?php echo esc_html( $onboarding_data['has_templates'] ? '3' : '2' ); ?></span>
			<strong><?php esc_html_e( 'Finish', 'analogwp-library' ); ?></strong>
		</li>
	</ol>

	<div class="analog-custom-library-onboarding__panel is-active" data-onboarding-step="1">
		<div class="analog-custom-library-onboarding__panel-copy">
			<h3><?php esc_html_e( 'Choose your library style', 'analogwp-library' ); ?></h3>
			<p><?php esc_html_e( 'Pick the preset that best matches how you want Custom Library to look from day one.', 'analogwp-library' ); ?></p>
		</div>
		<div class="image-radio-options analog-custom-library-onboarding__preset-options">
			<?php foreach ( $onboarding_data['style_presets'] as $key => $option ) : ?>
				<label class="image-radio-option<?php echo 'default' === $key ? ' selected' : ''; ?>">
					<input type="radio" name="library_style_preset" value="<?php echo esc_attr( $key ); ?>" <?php checked( 'default', $key ); ?> />
					<span class="image-radio-preview">
						<img src="<?php echo esc_url( $option['image'] ); ?>" alt="<?php echo esc_attr( $option['label'] ); ?>" />
					</span>
					<span class="image-radio-label"><?php echo esc_html( $option['label'] ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
		<div class="analog-custom-library-onboarding__actions analog-custom-library-onboarding__actions--end">
			<button type="button" class="button button-primary" data-onboarding-next="<?php echo esc_attr( $onboarding_data['has_templates'] ? '2' : '3' ); ?>"><?php esc_html_e( 'Continue', 'analogwp-library' ); ?></button>
		</div>
	</div>

	<?php if ( $onboarding_data['has_templates'] ) : ?>
		<div class="analog-custom-library-onboarding__panel" data-onboarding-step="2" hidden>
			<div class="analog-custom-library-onboarding__panel-copy">
				<h3><?php esc_html_e( 'Add your existing Elementor templates', 'analogwp-library' ); ?></h3>
				<p><?php esc_html_e( 'Select any templates you want to sync into Custom Library now. You can always add more later.', 'analogwp-library' ); ?></p>
			</div>
			<div class="analog-custom-library-onboarding__table-toolbar">
				<label class="analog-custom-library-onboarding__search">
					<span class="screen-reader-text"><?php esc_html_e( 'Search templates', 'analogwp-library' ); ?></span>
					<input type="search" class="regular-text" data-onboarding-search placeholder="<?php esc_attr_e( 'Search templates', 'analogwp-library' ); ?>" />
				</label>
				<p class="analog-custom-library-onboarding__table-summary">
					<?php
					printf(
						/* translators: 1: Default visible template count, 2: Total available template count. */
						esc_html__( 'Showing the first %1$d templates by default. Search to narrow the list from %2$d available templates.', 'analogwp-library' ),
						(int) $onboarding_data['template_limit'],
						count( $onboarding_data['templates'] )
					);
					?>
				</p>
			</div>
			<div class="analog-custom-library-onboarding__table-wrap">
				<table class="widefat fixed striped analog-custom-library-onboarding__table">
					<thead>
						<tr>
							<th class="check-column analog-custom-library-onboarding__checkbox-column">
								<input type="checkbox" data-onboarding-select-all aria-label="<?php esc_attr_e( 'Select all templates', 'analogwp-library' ); ?>" />
							</th>
							<th><?php esc_html_e( 'Title', 'analogwp-library' ); ?></th>
							<th><?php esc_html_e( 'Type', 'analogwp-library' ); ?></th>
							<th><?php esc_html_e( 'Categories', 'analogwp-library' ); ?></th>
							<th class="analog-custom-library-onboarding__preview-column" aria-label="<?php esc_attr_e( 'Preview', 'analogwp-library' ); ?>"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $onboarding_data['templates'] as $index => $template ) : ?>
							<tr
								data-onboarding-template-row
								data-template-index="<?php echo esc_attr( $index ); ?>"
								data-search-text="<?php echo esc_attr( strtolower( implode( ' ', array( $template['title'], $template['type'], $template['categories'] ) ) ) ); ?>"
							>
								<td class="check-column analog-custom-library-onboarding__checkbox-column">
									<input type="checkbox" name="onboarding_template_ids[]" value="<?php echo esc_attr( $template['id'] ); ?>" />
								</td>
								<td><?php echo esc_html( $template['title'] ); ?></td>
								<td><?php echo esc_html( $template['type'] ); ?></td>
								<td><?php echo esc_html( $template['categories'] ); ?></td>
								<td>
									<?php if ( ! empty( $template['preview_url'] ) ) : ?>
										<a href="<?php echo esc_url( $template['preview_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Preview', 'analogwp-library' ); ?></a>
									<?php else : ?>
										<?php esc_html_e( 'Unavailable', 'analogwp-library' ); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						<tr class="analog-custom-library-onboarding__empty-row" data-onboarding-empty-state hidden>
							<td colspan="5"><?php esc_html_e( 'No templates match your search.', 'analogwp-library' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="analog-custom-library-onboarding__actions">
				<button type="button" class="button button-secondary" data-onboarding-back="1"><?php esc_html_e( 'Back', 'analogwp-library' ); ?></button>
				<button type="button" class="button button-primary" data-onboarding-next="3"><?php esc_html_e( 'Continue', 'analogwp-library' ); ?></button>
			</div>
		</div>
	<?php endif; ?>

	<div class="analog-custom-library-onboarding__panel" data-onboarding-step="3" hidden>
		<div class="analog-custom-library-onboarding__panel-copy analog-custom-library-onboarding__panel-copy--narrow">
			<h3><?php esc_html_e( 'You’re all set', 'analogwp-library' ); ?></h3>
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: %s: Documentation URL. */
						__( 'Your custom library is ready to go! You can customize it further at any point. If you need any help, feel free to visit the <a href="%s" target="_blank" rel="noopener noreferrer">documentation</a>. Happy building!', 'analogwp-library' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
						)
					),
					esc_url( $onboarding_data['docs_url'] )
				);
				?>
			</p>
		</div>
		<div class="analog-custom-library-onboarding__video-placeholder">
			<span><?php esc_html_e( 'Video tutorial space', 'analogwp-library' ); ?></span>
		</div>
		<div class="analog-custom-library-onboarding__actions">
			<button type="button" class="button button-secondary" data-onboarding-back="<?php echo esc_attr( $onboarding_data['has_templates'] ? '2' : '1' ); ?>"><?php esc_html_e( 'Back', 'analogwp-library' ); ?></button>
			<button type="submit" name="analog_custom_library_finish_onboarding" value="1" class="button button-primary"><?php esc_html_e( 'Finish', 'analogwp-library' ); ?></button>
		</div>
	</div>

	<?php wp_nonce_field( Onboarding::NONCE_ACTION ); ?>
</div>