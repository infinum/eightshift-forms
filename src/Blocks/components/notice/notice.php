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
		$noticeTypeClass = 'esf:bg-green-500/5 esf:border-green-300/50 esf:text-green-950';
		$iconColorClass = 'esf:[&>svg]:text-green-600';
		$noticeIcon = UtilsHelper::getUtilsIcons('check');
		break;
	case 'error':
		$noticeTypeClass = 'esf:bg-red-500/5 esf:border-red-300/50 esf:text-red-950';
		$iconColorClass = 'esf:[&>svg]:text-red-600';
		$noticeIcon = UtilsHelper::getUtilsIcons('error');
		break;
	case 'info':
		$noticeTypeClass = 'esf:bg-sky-500/5 esf:border-sky-300/50 esf:text-sky-950';
		$iconColorClass = 'esf:[&>svg]:text-sky-600';
		$noticeIcon = UtilsHelper::getUtilsIcons('info');
		break;
	default:
		$noticeTypeClass = 'esf:bg-amber-500/5 esf:border-amber-300/50 esf:text-amber-950';
		$iconColorClass = 'esf:[&>svg]:text-amber-500';
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

<div class="esf:flex esf:items-center esf:gap-10 <?php echo esc_attr($noticeTypeClass); ?> esf:border esf:p-12 esf:text-lg esf:rounded-xl esf:inset-shadow-sm esf:inset-shadow-white/30">
	<div class="<?php echo esc_attr($svgClasses); ?>">
		<?php echo wp_kses_post($noticeIcon);
		?>
	</div>
	<div class="esf:flex esf:flex-col esf:gap-5">
		<?php if ($noticeTitle) { ?>
			<div class="esf:text-sm">
				<?php echo esc_html($noticeTitle); ?>
			</div>
		<?php } ?>

		<div class="esf:text-xs">
			<?php echo wp_kses_post($noticeContent); ?>
		</div>
	</div>
</div>
