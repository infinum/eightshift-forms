<?php

/**
 * Template for the group Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalGroupClass = $attributes['additionalGroupClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';

$groupLabel = Components::checkAttr('groupLabel', $attributes, $manifest);
$groupSublabel = Components::checkAttr('groupSublabel', $attributes, $manifest);
$groupContent = Components::checkAttr('groupContent', $attributes, $manifest);
$groupName = Components::checkAttr('groupName', $attributes, $manifest);
$groupSaveOneField = Components::checkAttr('groupSaveOneField', $attributes, $manifest);
$groupStyle = Components::checkAttr('groupStyle', $attributes, $manifest);
$groupBeforeContent = Components::checkAttr('groupBeforeContent', $attributes, $manifest);
$groupHelp = Components::checkAttr('groupHelp', $attributes, $manifest);

$groupClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalGroupClass, $additionalGroupClass),
	Components::selector($groupStyle, $componentClass, '', $groupStyle),
	Components::selector($componentJsClass, $componentJsClass),
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
			<?php echo $groupContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
		</div>
	<?php } ?>

	<?php if ($groupHelp) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__help"); ?>">
			<?php echo wp_kses_post($groupHelp); ?>
		</div>
	<?php } ?>
</div>
