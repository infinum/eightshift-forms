<?php

/**
 * Template for the progress bar component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';

$progressBarSteps = Components::checkAttr('progressBarSteps', $attributes, $manifest);

if (!$progressBarSteps) {
	return;
}

$progressBarClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

$progressBarItemClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'item'),
	Components::selector($componentJsClass, $componentJsClass),
]);

?>

<div class="<?php echo esc_attr($progressBarClass); ?>">
	<?php foreach ($progressBarSteps as $step) { ?>
		<?php
		$name = $step['name'] ?? '';
		$label = $step['label'] ?? '';

		if (!$name || !$label) {
			continue;
		}

		$progressBarAttrs = [];

		$progressBarAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['stepId']] = esc_attr($name);

		$progressBarAttrsOutput = '';
		if ($progressBarAttrs) {
			foreach ($progressBarAttrs as $key => $value) {
				$progressBarAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
			}
		}
		?>
		<div class="<?php echo esc_attr($progressBarItemClass); ?>" <?php echo $progressBarAttrsOutput ?>>
			<div class="<?php echo esc_attr("{$componentClass}__item-inner"); ?>">
				<?php echo esc_html($label); ?>
			</div>
		</div>
	<?php } ?>
</div>
