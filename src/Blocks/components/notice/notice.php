<?php

/**
 * Template for the Notice Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalNoticeClass = $attributes['additionalNoticeClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$noticeContent = Components::checkAttr('noticeContent', $attributes, $manifest);

$noticeClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($selectorClass, $selectorClass, $componentClass),
	Components::selector($additionalNoticeClass, $additionalNoticeClass),
]);

?>

<div class="<?php echo esc_attr($noticeClass); ?>">
	<span class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
		<?php echo UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
	</span>
	<span class="<?php echo esc_attr("{$componentClass}__text"); ?>">
		<?php echo wp_kses_post($noticeContent); ?>
	</span>
</div>
