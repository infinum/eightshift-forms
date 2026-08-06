<?php

/**
 * Invalid component view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;

$componentClass = $manifest['componentClass'] ?? '';
$heading = $attributes['heading'] ?? '';
$text = $attributes['text'] ?? '';
$icon = $attributes['icon'] ?? '';

?>

<div class="<?php echo esc_attr($componentClass); ?> esf:bg-red-500 esf:p-50 esf:flex esf:items-center esf:justify-center esf:flex-wrap esf:flex-col esf:text-white esf:border-2 esf:border-red-500">
	<div class="<?php echo esc_attr("{$componentClass}__icon"); ?> esf:mb-15 esf:[&>svg]:w-36 esf:[&>svg]:h-36 esf:[&>svg]:text-white">
		<?php echo $icon ? UtilsHelper::getUtilsIcons($icon) : UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
		?>
	</div>

	<?php if ($heading) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__heading"); ?> esf:mb-5 esf:font-bold">
			<?php echo esc_html($heading); ?>
		</div>
	<?php } ?>

	<?php if ($text) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__text"); ?>">
			<?php echo esc_html($text); ?>
		</div>
	<?php } ?>
</div>
