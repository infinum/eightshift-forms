<?php

/**
 * Template for the loader Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$blockJsClass = $manifest['blockJsClass'] ?? '';

$loaderClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($blockJsClass, $blockJsClass),
]);

?>

<div class="<?php echo esc_attr($loaderClass); ?>">
	<?php echo $manifest['resources']['loader']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
