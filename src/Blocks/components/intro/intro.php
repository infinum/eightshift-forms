<?php

/**
 * Template for the Intro Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$introTitle = Components::checkAttr('introTitle', $attributes, $manifest);
$introTitleSize = Components::checkAttr('introTitleSize', $attributes, $manifest);
$introSubtitle = Components::checkAttr('introSubtitle', $attributes, $manifest);

$introClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($introTitleSize, $componentClass, 'size', $introTitleSize),
]);

$titleClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'title'),
	Components::selector($introTitleSize, $componentClass, 'title', $introTitleSize),
]);

?>

<div class="<?php echo esc_attr($introClass); ?>">
	<div class="<?php echo esc_attr($titleClass); ?>">
		<?php echo esc_html($introTitle); ?>
	</div>

	<?php if ($introSubtitle) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__subtitle"); ?>">
			<?php echo $introSubtitle; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php } ?>
</div>
