<?php

/**
 * Template for the progress bar component - multistep.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(dirname(__DIR__, 2));

$steps = $attributes['steps'] ?? [];

if (!$steps) {
	return;
}

$componentClass = $attributes['componentClass'] ?? '';
$componentJsClass = $attributes['componentJsClass'] ?? '';

$hideLabels = $attributes['hideLabels'] ?? false;

$progressBarItemClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'item'),
	Components::selector($componentJsClass, $componentJsClass),
]);

foreach ($steps as $step) {
	$name = $step['name'] ?? '';
	$label = $step['label'] ?? '';

	if (!$name || !$label) {
		continue;
	}

	$progressBarAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['stepId']] = esc_attr($name);

	$progressBarAttrsOutput = '';
	foreach ($progressBarAttrs as $key => $value) {
		$progressBarAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
	?>
	<div class="<?php echo esc_attr($progressBarItemClass); ?>" <?php echo $progressBarAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
		<div class="<?php echo esc_attr("{$componentClass}__item-inner"); ?>">
			<?php
			if (!$hideLabels) {
				echo esc_html($label);
			}
			?>
		</div>
	</div>
	<?php
}
