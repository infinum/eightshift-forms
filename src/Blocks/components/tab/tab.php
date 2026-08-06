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
$tabWithBg = Helpers::checkAttr('tabWithBg', $attributes, $manifest);

if (!$tabLabel || !$tabContent) {
	return;
}

?>

<details
	class="<?php echo esc_attr(Helpers::clsx([
		UtilsHelper::getStateSelectorAdmin('tabsItem'),
		'esf:group',
		'esf:md:not-open:hidden',
		$tabWithBg ? 'esf:bg-white esf:border esf:border-mauve-100 esf:rounded-xl esf:inset-shadow-xs esf:inset-shadow-mauve-950/2' : '',
	])); ?>"
	data-hash="<?php echo rawurlencode((string) $tabLabel); ?>"
	data-btn-class="js-es-tabs-btn esf:select-none esf:inline-flex esf:items-center esf:px-10 esf:py-6 esf:transition esf:hover:bg-mauve-100 esf:aria-selected:border-mauve-600 esf:focus-ring esf:aria-selected:bg-mauve-600 esf:aria-selected:text-white esf:aria-selected:inset-shadow-sm esf:aria-selected:inset-shadow-mauve-100/10 esf:aria-selected:inset-ring esf:aria-selected:inset-ring-mauve-700/40 esf:aria-selected:bg-linear-to-b esf:from-mauve-900/0 esf:to-mauve-900/30 esf:rounded-lg"> <?php // phpcs:ignore Generic.Files.LineLength.TooLong ?>

	<summary class="esf:text-xs! esf:flex esf:items-center esf:justify-between esf:p-20 esf:cursor-pointer esf:select-none esf:md:hidden esf:[&::-webkit-details-marker]:hidden">
		<?php echo esc_html($tabLabel); ?>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="esf:w-15 esf:h-15 esf:shrink-0 esf:transition-transform esf:duration-300 esf:group-open:rotate-180">
			<path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
		</svg>
	</summary>
	<div class="<?php echo esc_attr(Helpers::clsx([
		'esf:flex esf:flex-col esf:gap-16',
		$tabWithBg ? 'esf:p-16' : '',
	])); ?>">
		<?php
			echo $tabContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
		?>
	</div>
</details>
