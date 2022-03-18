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
$adminSettingsFormEditLink = Components::checkAttr('adminSettingsFormEditLink', $attributes, $manifest);
$adminSettingsLink = Components::checkAttr('adminSettingsLink', $attributes, $manifest);
$adminSettingsSidebar = Components::checkAttr('adminSettingsSidebar', $attributes, $manifest);
$adminSettingsForm = Components::checkAttr('adminSettingsForm', $attributes, $manifest);
$adminSettingsType = Components::checkAttr('adminSettingsType', $attributes, $manifest);
$adminSettingsIsGlobal = Components::checkAttr('adminSettingsIsGlobal', $attributes, $manifest);

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
		<div class="<?php echo \esc_attr("{$sectionClass}__section {$sectionClass}__section--clean"); ?>">
			<a href="<?php echo esc_url($adminSettingsBackLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
				<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 10H4m4-5-5 5 5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
				<?php echo esc_html__('All forms', 'eightshift-forms'); ?>
			</a>
		</div>

		<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
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
								<span class="<?php echo \esc_attr("{$sectionClass}__menu-link-wrap"); ?>">
									<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php echo esc_html($label); ?>
								</span>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</div>
	<div class="<?php echo \esc_attr("{$sectionClass}__main"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__heading"); ?>">
				<div class="<?php echo \esc_attr("{$sectionClass}__heading-wrap"); ?>">
					<div class="<?php echo \esc_attr("{$sectionClass}__heading-title"); ?>">
						<?php echo esc_html($adminSettingsPageTitle); ?>
					</div>

					<?php if (!$adminSettingsIsGlobal) { ?>
						<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
							<a href="<?php echo esc_url($adminSettingsFormEditLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?> <?php echo \esc_attr("{$sectionClass}__link--cta"); ?>">
								<svg class="<?php echo \esc_attr("{$sectionClass}__link-icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m15.5 7.5-3-3-8.665 8.184a1.5 1.5 0 0 0-.435.765l-.708 3.189a.5.5 0 0 0 .646.583l3.326-1.109a1.5 1.5 0 0 0 .586-.362L15.5 7.5z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="m12.5 4.5 1.44-1.44a1.5 1.5 0 0 1 2.12 0l.88.88a1.5 1.5 0 0 1 0 2.12L15.5 7.5" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
								<?php echo esc_html__('Edit form', 'eightshift-forms'); ?>
							</a>
						</div>
					<?php } ?>
				</div>

				<div class="<?php echo \esc_attr("{$sectionClass}__description"); ?>">
					<?php echo esc_html($adminSettingsSubTitle); ?>
				</div>
			</div>
			<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
				<?php echo $adminSettingsForm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</div>
