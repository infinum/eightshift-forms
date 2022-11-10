<?php

/**
 * Template for the Notice Component.
 *
 * @package EightshiftForms
 */

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
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.157 16.801 8.673 2.522c.562-1.068 2.092-1.068 2.654 0l7.516 14.28A1.5 1.5 0 0 1 17.515 19H2.486a1.5 1.5 0 0 1-1.328-2.199z" stroke="currentColor" stroke-width="1.5" fill="none"></path><path d="M10 7.5v5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"></path><circle cx="10" cy="15.25" r="1" fill="currentColor"></circle></svg>
	</span>
	<span class="<?php echo esc_attr("{$componentClass}__text"); ?>">
		<?php echo wp_kses_post($noticeContent); ?>
	</span>
</div>
