<?php

/**
 * Template for the Submit Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$submitName = Components::checkAttr('submitName', $attributes, $manifest);
$submitValue = Components::checkAttr('submitValue', $attributes, $manifest);
$submitId = Components::checkAttr('submitId', $attributes, $manifest);
$submitType = Components::checkAttr('submitType', $attributes, $manifest);
$submitIsDisabled = Components::checkAttr('submitIsDisabled', $attributes, $manifest);

$submitClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<?php if ($submitType === 'button') { ?>
	<button
		class="<?php echo esc_attr($submitClass); ?>"
		name="<?php echo esc_attr($submitName); ?>"
		id="<?php echo esc_attr($submitId); ?>"
		<?php $submitIsDisabled ? 'disabled': ''; ?>
	>
		<?php echo esc_attr($submitValue); ?>
	</button>
<?php } else { ?>
	<input
		class="<?php echo esc_attr($submitClass); ?>"
		value="<?php echo esc_attr($submitValue); ?>"
		name="<?php echo esc_attr($submitName); ?>"
		id="<?php echo esc_attr($submitId); ?>"
		<?php $submitIsDisabled ? 'disabled': ''; ?>
	/>
<?php } ?>
