<?php

/**
 * Template for the Tab Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$tabLabel = Helpers::checkAttr('tabLabel', $attributes, $manifest);
$tabContent = Helpers::checkAttr('tabContent', $attributes, $manifest);
$tabNoBg = Helpers::checkAttr('tabNoBg', $attributes, $manifest);

if (!$tabLabel || !$tabContent) {
	return;
}

?>

<details
	class="<?php echo esc_attr(Helpers::clsx([
						UtilsHelper::getStateSelectorAdmin('tabsItem'),
						'esf:bg-white esf:border esf:border-border esf:rounded-md esf:overflow-hidden esf:group',
						'esf:md:[&:not([open])]:hidden',
					])); ?>"
	data-hash="<?php echo rawurlencode($tabLabel); ?>"
	data-btn-class="js-es-tabs-btn esf:cursor-pointer esf:border-b-2 esf:border-transparent esf:inline-flex esf:items-center esf:py-5 esf:text-sm! esf:transition-colors esf:duration-300 esf:hover:border-border esf:aria-selected:border-accent">

	<summary class="esf:text-sm! esf:flex esf:items-center esf:justify-between esf:p-20 esf:cursor-pointer esf:select-none esf:md:hidden esf:[&::-webkit-details-marker]:hidden">
		<?php echo esc_html($tabLabel); ?>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="esf:w-15 esf:h-15 esf:shrink-0 esf:transition-transform esf:duration-200 esf:group-open:rotate-180">
			<path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
		</svg>
	</summary>
	<div class="<?php echo esc_attr(Helpers::clsx([
								'esf:flex esf:flex-col esf:gap-15',
								!$tabNoBg ? 'esf:p-20 esf:border-t esf:border-border esf:md:border-t-0' : '',
							])); ?>">
		<?php echo $tabContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
		?>
	</div>
</details>
