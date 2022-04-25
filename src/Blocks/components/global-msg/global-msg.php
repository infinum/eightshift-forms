<?php

/**
 * Template for the globalMsg Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';

$globalMsgValue = Components::checkAttr('globalMsgValue', $attributes, $manifest);

$globalMsgClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($componentJsClass, $componentJsClass),
]);

?>

<div class="<?php echo esc_attr($globalMsgClass); ?>">
	<?php echo esc_html($globalMsgValue); ?>
</div>
