<?php

/**
 * Template for the radio Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$radioLabel = Components::checkAttr('radioLabel', $attributes, $manifest);
$radioName = Components::checkAttr('radioName', $attributes, $manifest);
$radioValue = Components::checkAttr('radioValue', $attributes, $manifest);
$radioIsChecked = Components::checkAttr('radioIsChecked', $attributes, $manifest);
$radioIsDisabled = Components::checkAttr('radioIsDisabled', $attributes, $manifest);
$radioIsReadOnly = Components::checkAttr('radioIsReadOnly', $attributes, $manifest);
$radioIsRequired = Components::checkAttr('radioIsRequired', $attributes, $manifest);
$radioTracking = Components::checkAttr('radioTracking', $attributes, $manifest);

if (empty($radioName)) {
	$radioName = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, $radioLabel);
}

$radioId = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, $radioName);

$radioClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($radioClass); ?>">
	<label
		for="<?php echo esc_attr($radioName); ?>"
		class="<?php echo esc_attr("{$componentClass}__label"); ?>"
	>
		<?php echo esc_attr($radioLabel); ?>
	</label>
	<input
		class="<?php echo esc_attr("{$componentClass}__input"); ?>"
		type="radio"
		name="<?php echo esc_attr($radioName); ?>"
		id="<?php echo esc_attr($radioId); ?>"
		value="<?php echo esc_attr($radioValue); ?>"
		data-validation-required="<?php echo esc_attr($radioIsRequired); ?>"
		data-tracking="<?php echo esc_attr($radioTracking); ?>"
		<?php echo $radioIsChecked ? 'checked': ''; ?>
		<?php echo $radioIsDisabled ? 'disabled': ''; ?>
		<?php echo $radioIsReadOnly ? 'readonly': ''; ?>
	/>
</div>
