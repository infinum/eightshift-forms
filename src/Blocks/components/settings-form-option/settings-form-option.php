<?php

/**
 * Template for settings form option page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Settings\AbstractFormBuilder;

$globalManifest = Components::getManifest(dirname(__DIR__, 2));
$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getManifest(dirname(__DIR__, 1) . '/settings-section');

echo Components::outputCssVariablesGlobal($globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$settingsFormOptionPageTitle = Components::checkAttr('settingsFormOptionPageTitle', $attributes, $manifest);
$settingsFormOptionSubTitle = Components::checkAttr('settingsFormOptionSubTitle', $attributes, $manifest);
$settingsFormOptionForm = Components::checkAttr('settingsFormOptionForm', $attributes, $manifest);
$settingsFormOptionBackLink = Components::checkAttr('settingsFormOptionBackLink', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
]);

$formId = $_GET['formId'];

$form = \apply_filters(AbstractFormBuilder::SETTINGS_PAGE_FORM_BUILDER, $settingsFormOptionForm, $formId);

?>

<div class="<?php echo \esc_attr($layoutClass); ?>">
	<div class="<?php echo \esc_attr("{$sectionClass}__section"); ?>">
		<div class="<?php echo \esc_attr("{$sectionClass}__heading"); ?>">
			<div class="<?php echo \esc_attr("{$sectionClass}__heading-title"); ?>">
				<?php echo sprintf('%s - %s', esc_html($settingsFormOptionPageTitle), esc_html($formId)); ?>
			</div>

			<div class="<?php echo \esc_attr("{$sectionClass}__actions"); ?>">
				<a href="<?php echo esc_html($settingsFormOptionBackLink); ?>" class="<?php echo \esc_attr("{$sectionClass}__link"); ?>">
					<span class="<?php echo \esc_attr("{$sectionClass}__link-icon dashicons dashicons-arrow-left"); ?> "></span>
					<?php echo esc_html('Back to forms', 'eightshift-forms'); ?>
				</a>
			</div>
		</div>
		<div class="<?php echo \esc_attr("{$sectionClass}__description {$sectionClass}__description--with-spacing"); ?>">
			<?php echo esc_html($settingsFormOptionSubTitle); ?>
		</div>
		<div class="<?php echo \esc_attr("{$sectionClass}__content"); ?>">
			<?php echo $form; ?>
		</div>
	</div>
</div>
