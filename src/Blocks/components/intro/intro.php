<?php

/**
 * Template for the Intro Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$introTitle = Components::checkAttr('introTitle', $attributes, $manifest);
$introSubtitle = Components::checkAttr('introSubtitle', $attributes, $manifest);

$introClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($introClass); ?>">
	<div class="<?php echo esc_attr("{$introClass}__title"); ?>">
		<?php echo $introTitle; ?>
	</div>

	<?php if ($introSubtitle) { ?>
		<div class="<?php echo esc_attr("{$introClass}__subtitle"); ?>">
			<?php echo $introSubtitle; ?>
		</div>
	<?php } ?>
</div>
