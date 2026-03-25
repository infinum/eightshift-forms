<?php

/**
 * Template for the group Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalGroupClass = $attributes['additionalGroupClass'] ?? '';

$groupLabel = Helpers::checkAttr('groupLabel', $attributes, $manifest);
$groupSubLabel = Helpers::checkAttr('groupSubLabel', $attributes, $manifest);
$groupContent = Helpers::checkAttr('groupContent', $attributes, $manifest);
$groupName = Helpers::checkAttr('groupName', $attributes, $manifest);
$groupSaveOneField = Helpers::checkAttr('groupSaveOneField', $attributes, $manifest);
$groupStyle = Helpers::checkAttr('groupStyle', $attributes, $manifest);
$groupBeforeContent = Helpers::checkAttr('groupBeforeContent', $attributes, $manifest);
$groupHelp = Helpers::checkAttr('groupHelp', $attributes, $manifest);

$groupClass = Helpers::clsx([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalGroupClass, $additionalGroupClass),
	Helpers::selector($groupStyle, $componentClass, '', $groupStyle),
	UtilsHelper::getStateSelector('group'),
	'esf:flex esf:flex-col esf:gap-10',
]);

?>

<div
	class="<?php echo esc_attr($groupClass); ?>"
	data-field-id="<?php echo esc_attr($groupName); ?>"
	data-group-save-as-one-field="<?php echo esc_attr($groupSaveOneField); ?>">

	<?php if ($groupLabel) { ?>
		<div class="esf:flex esf:flex-col esf:gap-2 esf:max-w-[850px]">
			<div class="esf:text-sm esf:font-medium esf:text-secondary-900">
				<?php echo esc_html($groupLabel); ?>
			</div>

			<?php if ($groupSubLabel) { ?>
				<div class="esf:text-xs esf:text-secondary-500">
					<?php echo esc_html($groupSubLabel); ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>

	<?php if ($groupBeforeContent) { ?>
		<div class="esf:mb-24 esf:[&>*]:m-0">
			<?php echo wp_kses_post($groupBeforeContent); ?>
		</div>
	<?php } ?>

	<?php if ($groupContent) { ?>
		<div class="esf:max-w-[850px]">
			<?php echo $groupContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
			?>
		</div>
	<?php } ?>

	<?php if ($groupHelp) { ?>
		<div>
			<?php echo wp_kses_post($groupHelp); ?>
		</div>
	<?php } ?>
</div>
