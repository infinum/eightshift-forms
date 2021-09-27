<?php

/**
 * Template for admin settings page.
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

$adminSettingsPageTitle = Components::checkAttr('adminSettingsPageTitle', $attributes, $manifest);
$adminSettingsSubTitle = Components::checkAttr('adminSettingsSubTitle', $attributes, $manifest);
$adminSettingsBackLink = Components::checkAttr('adminSettingsBackLink', $attributes, $manifest);
$adminSettingsLink = Components::checkAttr('adminSettingsLink', $attributes, $manifest);
$adminSettingsSidebar = Components::checkAttr('adminSettingsSidebar', $attributes, $manifest);
$adminSettingsForm = Components::checkAttr('adminSettingsForm', $attributes, $manifest);
$adminSettingsType = Components::checkAttr('adminSettingsType', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
	Components::selector($sectionClass, $sectionClass, '', 'with-sidebar'),
]);

if (!$adminSettingsSidebar || !$adminSettingsForm) {
	return;
}

?>

<div class="<?php echo \esc_attr($layoutClass); ?>">
	<div class="<?php echo \esc_attr("{$sectionClass}__sidebar"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__content {$sectionClass}--reset-spacing"); ?>">
				<?php if ($adminSettingsSidebar) { ?>
					<ul class="<?php echo \esc_attr("{$sectionClass}__menu"); ?>">
						<?php foreach ($adminSettingsSidebar as $item) { ?>
							<?php
							$label = $item['label'] ?? '';
							$value = $item['value'] ?? '';
							$icon = $item['icon'] ?? '';
							?>
							<li class="<?php echo \esc_attr("{$sectionClass}__menu-item"); ?>">
								<a
									href="<?php echo esc_url("{$adminSettingsLink}&type={$value}"); ?>"
									class="<?php echo \esc_attr("{$sectionClass}__menu-link " . Components::selector($value === $adminSettingsType, $sectionClass, 'menu-link', 'active')); ?>"
								>
									<span class="<?php echo \esc_attr("{$sectionClass}__menu-link-icon dashicons {$icon}"); ?> "></span>
									<?php echo esc_html($label); ?>
								</a>
							</li>
						<?php } ?>
					</ul>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="<?php echo \esc_attr("{$sectionClass}__main"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__heading"); ?>">
				<div class="<?php echo \esc_attr("{$sectionClass}__heading-title"); ?>">
					<?php echo esc_html($adminSettingsPageTitle); ?>
				</div>

				<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
					<a href="<?php echo esc_url($adminSettingsBackLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
						<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-arrow-left"); ?> "></span>
						<?php echo esc_html__('Back to forms', 'eightshift-forms'); ?>
					</a>
				</div>
			</div>
			<div class="<?php echo \esc_attr("{$sectionClass}__description {$sectionClass}__description--with-spacing"); ?>">
				<?php echo esc_html($adminSettingsSubTitle); ?>
			</div>
			<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
				<?php echo $adminSettingsForm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</div>
