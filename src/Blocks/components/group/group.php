<?php

/**
 * Template for the group Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalGroupClass = $attributes['additionalGroupClass'] ?? '';

$groupLabel = Helpers::checkAttr('groupLabel', $attributes, $manifest);
$groupSublabel = Helpers::checkAttr('groupSublabel', $attributes, $manifest);
$groupContent = Helpers::checkAttr('groupContent', $attributes, $manifest);
$groupName = Helpers::checkAttr('groupName', $attributes, $manifest);
$groupSaveOneField = Helpers::checkAttr('groupSaveOneField', $attributes, $manifest);
$groupStyle = Helpers::checkAttr('groupStyle', $attributes, $manifest);
$groupBeforeContent = Helpers::checkAttr('groupBeforeContent', $attributes, $manifest);
$groupHelp = Helpers::checkAttr('groupHelp', $attributes, $manifest);

$groupClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalGroupClass, $additionalGroupClass),
	Helpers::selector($groupStyle, $componentClass, '', $groupStyle),
	UtilsHelper::getStateSelector('group'),
]);

?>

<div
	class="<?php echo esc_attr($groupClass); ?>"
	data-field-id="<?php echo esc_attr($groupName); ?>"
	data-group-save-as-one-field="<?php echo esc_attr($groupSaveOneField); ?>"
>

	<?php if ($groupLabel) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__label"); ?>">
			<div class="<?php echo esc_attr("{$componentClass}__label-inner"); ?>">
				<?php echo esc_html($groupLabel); ?>
			</div>

			<?php if ($groupSublabel) { ?>
				<div class="<?php echo esc_attr("{$componentClass}__sub-label"); ?>">
					<?php echo esc_html($groupSublabel); ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>

	<?php if ($groupBeforeContent) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__before-content"); ?>">
			<?php echo wp_kses_post($groupBeforeContent); ?>
		</div>
	<?php } ?>

	<?php if ($groupContent) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
			<?php echo $groupContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
		</div>
	<?php } ?>

	<?php if ($groupHelp) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__help"); ?>">
			<?php echo wp_kses_post($groupHelp); ?>
		</div>
	<?php } ?>
</div>
