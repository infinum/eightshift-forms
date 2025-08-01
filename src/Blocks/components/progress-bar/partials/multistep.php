<?php

/**
 * Template for the progress bar component - multistep.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$steps = $attributes['steps'] ?? [];
$twClasses = $attributes['twClasses'] ?? [];

if (!$steps) {
	return;
}

$componentClass = $attributes['componentClass'] ?? '';
$jsClass = $attributes['jsClass'] ?? '';

$hideLabels = $attributes['hideLabels'] ?? false;

$progressBarItemClass = Helpers::classnames([
	FormsHelper::getTwPart($twClasses, 'progress-bar', 'item', "{$componentClass}__item"),
	Helpers::selector($jsClass, $jsClass),
]);

foreach ($steps as $step) { ?>
	<?php
	$name = $step['name'] ?? '';
	$label = $step['label'] ?? '';

	if (!$name || !$label) {
		continue;
	}

	$progressBarAttrs[UtilsHelper::getStateAttribute('stepId')] = esc_attr($name);

	?>
	<div
		class="<?php echo esc_attr($progressBarItemClass); ?>"
		<?php echo Helpers::getAttrsOutput($progressBarAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
		?>>
		<div class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'progress-bar', 'item-inner', "{$componentClass}__item-inner")); ?>">
			<?php
			if (!$hideLabels) {
				echo esc_html($label);
			}
			?>
		</div>
	</div>
<?php }
