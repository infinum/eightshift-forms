<?php

/**
 * Template for the Notice Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalNoticeClass = $attributes['additionalNoticeClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$noticeContent = Helpers::checkAttr('noticeContent', $attributes, $manifest);

$noticeClass = Helpers::clsx([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($selectorClass, $selectorClass, $componentClass),
	Helpers::selector($additionalNoticeClass, $additionalNoticeClass),
]);

?>

<div class="<?php echo esc_attr($noticeClass); ?>">
	<span class="<?php echo esc_attr("{$componentClass}__icon"); ?>">
		<?php echo UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
		?>
	</span>
	<span class="<?php echo esc_attr("{$componentClass}__text"); ?>">
		<?php echo wp_kses_post($noticeContent); ?>
	</span>
</div>
