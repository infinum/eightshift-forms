<?php

/**
 * Template for the globalMsg Component.
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

$globalMsgValue = Components::checkAttr('globalMsgValue', $attributes, $manifest);

$globalMsgClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($blockJsClass, $blockJsClass),
]);

?>

<div class="<?php echo esc_attr($globalMsgClass); ?>">
	<?php echo esc_html($globalMsgValue); ?>
</div>
