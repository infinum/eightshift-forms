<?php

/**
 * Template for the Error Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalErrorClass = $attributes['additionalErrorClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';

$errorValue = Components::checkAttr('errorValue', $attributes, $manifest);
$errorId = Components::checkAttr('errorId', $attributes, $manifest);

$errorClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalErrorClass, $additionalErrorClass),
	Components::selector($componentJsClass, $componentJsClass),
]);

?>

<div
	class="<?php echo esc_attr($errorClass); ?>"
	data-id="<?php echo esc_attr($errorId); ?>"
>
	<span>
		<?php echo esc_html($errorValue); ?>
	</span>
</div>
