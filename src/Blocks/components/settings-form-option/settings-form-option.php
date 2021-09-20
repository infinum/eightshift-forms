<?php

/**
 * Template for settings form option page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Settings\AbstractFormBuilder;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';

$settingsFormOptionPageTitle = Components::checkAttr('settingsFormOptionPageTitle', $attributes, $manifest);
$settingsFormOptionSubTitle = Components::checkAttr('settingsFormOptionSubTitle', $attributes, $manifest);
$settingsFormOptionForm = Components::checkAttr('settingsFormOptionForm', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
]);

$formId = $_GET['formId'];

$form = \apply_filters(AbstractFormBuilder::SETTINGS_PAGE_FORM_BUILDER, $settingsFormOptionForm, $formId);

?>

<div class="<?php echo \esc_attr($layoutClass); ?>">
	<div class="<?php echo \esc_attr("{$componentClass}__section"); ?>">
		<div class="<?php echo \esc_attr("{$componentClass}__heading"); ?>">
			<?php echo sprintf('%s - %s"', esc_html($settingsFormOptionPageTitle), esc_html($formId)); ?>
		</div>
		<div class="<?php echo \esc_attr("{$componentClass}__content"); ?>">
			<div class="<?php echo \esc_attr("{$componentClass}__desciption"); ?>">
				<?php echo esc_html($settingsFormOptionSubTitle); ?>
			</div>

			<div class="<?php echo \esc_attr("{$componentClass}__form"); ?>">
				<?php echo $form; ?>
			</div>
		</div>
	</div>
</div>
