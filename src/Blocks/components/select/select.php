<?php

/**
 * Template for the Select Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$selectName = Components::checkAttr('selectName', $attributes, $manifest);
$selectId = Components::checkAttr('selectId', $attributes, $manifest);
$selectIsDisabled = Components::checkAttr('selectIsDisabled', $attributes, $manifest);
$selectOptions = Components::checkAttr('selectOptions', $attributes, $manifest);

$selectClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<select
	class="<?php echo esc_attr($selectClass); ?>"
	name="<?php echo esc_attr($selectName); ?>"
	id="<?php echo esc_attr($selectId); ?>"
	<?php $selectIsDisabled ? 'disabled': ''; ?>
>
	<?php echo $selectOptions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</select>
