<?php

/**
 * Template for admin settings global page.
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

$adminSettingsGlobalPageTitle = Components::checkAttr('adminSettingsGlobalPageTitle', $attributes, $manifest);
$adminSettingsGlobalSubTitle = Components::checkAttr('adminSettingsGlobalSubTitle', $attributes, $manifest);
$adminSettingsGlobalBackLink = Components::checkAttr('adminSettingsGlobalBackLink', $attributes, $manifest);
$adminSettingsGlobalData = Components::checkAttr('adminSettingsGlobalData', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
]);

?>

<div class="<?php echo \esc_attr($layoutClass); ?>">
	<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__heading"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__heading-title"); ?>">
				<?php echo esc_html($adminSettingsGlobalPageTitle); ?>
			</div>

			<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
				<a href="<?php echo esc_url($adminSettingsGlobalBackLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
					<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-arrow-left"); ?> "></span>
					<?php echo esc_html__('Back to forms', 'eightshift-forms'); ?>
				</a>
			</div>
		</div>
		<div class="<?php echo \esc_attr("{$sectionClass}__description {$sectionClass}__description--with-spacing"); ?>">
			<?php echo esc_html($adminSettingsGlobalSubTitle); ?>
		</div>
		<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
			<?php echo $adminSettingsGlobalData; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</div>
