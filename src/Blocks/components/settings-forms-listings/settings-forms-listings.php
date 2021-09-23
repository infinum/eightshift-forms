<?php

/**
 * Template for settings form optiosn page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;

$globalManifest = Components::getManifest(dirname(__DIR__, 2));
$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getManifest(dirname(__DIR__, 1) . '/settings-section');

echo Components::outputCssVariablesGlobal($globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$settingsFormsListingsPageTitle = Components::checkAttr('settingsFormsListingsPageTitle', $attributes, $manifest);
$settingsFormsListingsSubTitle = Components::checkAttr('settingsFormsListingsSubTitle', $attributes, $manifest);
$settingsFormsListingsForms = Components::checkAttr('settingsFormsListingsForms', $attributes, $manifest);
$settingsFormsListingsPostType = Components::checkAttr('settingsFormsListingsPostType', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
]);

?>

<div class="<?php echo \esc_attr($layoutClass); ?>">
	<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__heading"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__heading-title"); ?>">
				<?php echo esc_html($settingsFormsListingsPageTitle); ?>
			</div>

			<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
				<a href="<?php echo esc_html("/wp-admin/post-new.php?post_type={$settingsFormsListingsPostType}"); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
					<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-plus-alt"); ?> "></span>
					<?php echo esc_html('Add new form', 'eightshift-forms'); ?>
				</a>
			</div>
		</div>
		<div class="<?php echo \esc_attr("{$sectionClass}__description"); ?>">
			<?php echo esc_html($settingsFormsListingsSubTitle); ?>
		</div>
		<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
			<?php if ($settingsFormsListingsForms) { ?>
				<ul class="<?php echo \esc_attr("{$componentClass}__list {$sectionClass}--reset-spacing"); ?>">
					<?php foreach ($settingsFormsListingsForms as $form) { ?>
						<?php
						$id = $form['id'];
						$editLink = $form['editLink'];
						$settingsLink = $form['settingsLink'];
						$slug = $form['slug'];
						$title = $form['title'];
						$status = $form['status'];
						?>
						<li class="<?php echo \esc_attr("{$componentClass}__list-item"); ?>">
							<div class="<?php echo esc_attr("{$componentClass}__item-intro"); ?>">
								<a href="<?php echo esc_html($editLink); ?>" class="<?php echo \esc_attr("{$componentClass}__label"); ?>">
									<?php echo esc_html($title); ?>
								</a>

								<?php if ($status !== 'publish') { ?>
									<span class="<?php echo esc_attr("{$componentClass}__item-status"); ?>">
										<?php echo esc_html($status); ?>
									</span>
								<?php } ?>
							</div>
							<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
								<a href="<?php echo esc_html($editLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
									<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-edit"); ?> "></span>
									<?php echo esc_html('Edit', 'eightshift-forms'); ?>
								</a>
								<a href="<?php echo esc_html($settingsLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
									<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-admin-settings"); ?> "></span>
									<?php echo esc_html('Settings', 'eightshift-forms'); ?>
								</a>
							</div>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	</div>
</div>
