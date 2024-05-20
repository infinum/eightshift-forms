<?php

/**
 * Invalid component view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$heading = $attributes['heading'] ?? '';
$text = $attributes['text'] ?? '';
$icon = $attributes['icon'] ?? '';

?>

<div class="<?php echo esc_attr($componentClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
		<?php echo $icon ? UtilsHelper::getUtilsIcons($icon) : UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
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
