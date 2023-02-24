<?php

/**
 * Invalid component view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestUtils = Components::getComponent('utils');

$componentClass = $manifest['componentClass'] ?? '';
$heading = $attributes['heading'] ?? '';
$text = $attributes['text'] ?? '';
$icon = $attributes['icon'] ?? '';

?>

<div class="<?php echo esc_attr($componentClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
		<?php echo $icon ? $manifestUtils['icons'][$icon] : $manifestUtils['icons']['warning']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
	</div>

	<?php if ($heading) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__heading"); ?>">
			<?php echo esc_html($heading); ?>
		</div>
	<?php } ?>

	<?php if ($text) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__text"); ?>">
			<?php echo esc_html($text); ?>
		</div>
	<?php } ?>
</div>
