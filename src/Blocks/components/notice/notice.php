<?php

/**
 * Template for the Notice Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$noticeTitle = Helpers::checkAttr('noticeTitle', $attributes, $manifest);
$noticeContent = Helpers::checkAttr('noticeContent', $attributes, $manifest);
$noticeType = Helpers::checkAttr('noticeType', $attributes, $manifest);

switch ($noticeType) {
	case 'success':
		$noticeTypeClass = 'esf:bg-green-100 esf:border-green-500 esf:text-green-950';
		$iconColorClass = 'esf:[&>svg]:text-green-500';
		$noticeIcon = UtilsHelper::getUtilsIcons('check');
		break;
	case 'error':
		$noticeTypeClass = 'esf:bg-red-100 esf:border-red-500 esf:text-red-950';
		$iconColorClass = 'esf:[&>svg]:text-red-500';
		$noticeIcon = UtilsHelper::getUtilsIcons('error');
		break;
	case 'info':
		$noticeTypeClass = 'esf:bg-sky-100 esf:border-sky-500 esf:text-sky-950';
		$iconColorClass = 'esf:[&>svg]:text-sky-500';
		$noticeIcon = UtilsHelper::getUtilsIcons('info');
		break;
	default:
		$noticeTypeClass = 'esf:bg-yellow-100 esf:border-yellow-500 esf:text-yellow-950';
		$iconColorClass = 'esf:[&>svg]:text-yellow-500';
		$noticeIcon = UtilsHelper::getUtilsIcons('warning');
		break;
}

if (!$noticeContent) {
	return;
}

$svgClasses = Helpers::clsx([
	'esf:flex esf:items-center esf:justify-center esf:shrink-0 esf:[&>svg]:w-30 esf:[&>svg]:h-30',
	$iconColorClass,
]);

?>

<div class="esf:flex esf:items-center esf:gap-10 <?php echo esc_attr($noticeTypeClass); ?> esf:px-20 esf:py-15 esf:text-lg esf:rounded-md">
	<div class="<?php echo esc_attr($svgClasses); ?>">
		<?php echo wp_kses_post($noticeIcon);
		?>
	</div>
	<div class="esf:flex esf:flex-col esf:gap-5">
		<?php if ($noticeTitle) { ?>
			<div class="esf:text-base">
				<?php echo esc_html($noticeTitle); ?>
			</div>
		<?php } ?>

		<div class="esf:text-xs">
			<?php echo wp_kses_post($noticeContent); ?>
		</div>
	</div>
</div>
