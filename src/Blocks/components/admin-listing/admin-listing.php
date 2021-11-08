<?php

/**
 * Template for admin listing page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;

$globalManifest = Components::getManifest(dirname(__DIR__, 2));
$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getManifest(dirname(__DIR__, 1) . '/admin-settings-section');

echo Components::outputCssVariablesGlobal($globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminListingPageTitle = Components::checkAttr('adminListingPageTitle', $attributes, $manifest);
$adminListingSubTitle = Components::checkAttr('adminListingSubTitle', $attributes, $manifest);
$adminListingNewFormLink = Components::checkAttr('adminListingNewFormLink', $attributes, $manifest);
$adminListingTrashLink = Components::checkAttr('adminListingTrashLink', $attributes, $manifest);
$adminListingForms = Components::checkAttr('adminListingForms', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
]);

?>

<div class="<?php echo \esc_attr($layoutClass); ?>">
	<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__heading"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__heading-title"); ?>">
				<?php echo esc_html($adminListingPageTitle); ?>
			</div>

			<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
				<a href="<?php echo esc_url($adminListingTrashLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
					<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-trash"); ?> "></span>
					<?php echo \esc_html__('View Trash', 'eightshift-forms'); ?>
				</a>
				<a href="<?php echo esc_url($adminListingNewFormLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
					<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-plus-alt"); ?> "></span>
					<?php echo \esc_html__('Add new form', 'eightshift-forms'); ?>
				</a>
			</div>
		</div>
		<div class="<?php echo \esc_attr("{$sectionClass}__description"); ?>">
			<?php echo esc_html($adminListingSubTitle); ?>
		</div>
		<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
			<?php if ($adminListingForms) { ?>
				<ul class="<?php echo \esc_attr("{$componentClass}__list {$sectionClass}--reset-spacing"); ?>">
					<?php foreach ($adminListingForms as $form) { ?>
						<?php
						$id = $form['id']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$editLink = $form['editLink'];
						$settingsLink = $form['settingsLink'];
						$slug = $form['slug'];
						$title = $form['title']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$status = $form['status']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						?>
						<li class="<?php echo \esc_attr("{$componentClass}__list-item"); ?>">
							<div class="<?php echo esc_attr("{$componentClass}__item-intro"); ?>">
								<a href="<?php echo esc_url($editLink); ?>" class="<?php echo \esc_attr("{$componentClass}__label"); ?>">
									<span class="dashicons dashicons-feedback <?php echo \esc_attr("{$componentClass}__label-icon"); ?>"></span>
									<?php echo esc_html($title); ?>
								</a>

								<?php if ($status !== 'publish') { ?>
									<span class="<?php echo esc_attr("{$componentClass}__item-status"); ?>">
										<?php echo esc_html($status); ?>
									</span>
								<?php } ?>
							</div>
							<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
								<a href="<?php echo esc_url($editLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
									<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-edit"); ?> "></span>
									<?php echo esc_html__('Edit', 'eightshift-forms'); ?>
								</a>
								<a href="<?php echo esc_url($settingsLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
									<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-admin-settings"); ?> "></span>
									<?php echo esc_html__('Settings', 'eightshift-forms'); ?>
								</a>
							</div>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	</div>
</div>
