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


$tabLabelClass = Helpers::clsx([
	UtilsHelper::getStateSelectorAdmin('tabsItem'),
	'esf:order-1 esf:cursor-pointer esf:border-none esf:inline-flex esf:items-center esf:px-12 esf:py-8 esf:text-sm esf:font-medium esf:text-secondary-600 esf:rounded-lg esf:transition-[color,background-color,box-shadow] esf:duration-300',
	'esf:hover:bg-secondary-100 esf:hover:text-secondary-900',
	'[&:only-of-type]:esf:hidden',
	'esf:aria-selected:text-white esf:aria-selected:bg-accent-600 ',
]);

$tabContentClass = Helpers::clsx([
	'esf:order-[99] esf:basis-full esf:flex-col esf:gap-10 esf:text-sm esf:text-secondary-900 esf:flex',
	!$tabNoBg ? 'esf:bg-white esf:border esf:border-secondary-200 esf:p-20 esf:rounded-md' : '',
]);


?>

<button type="button" class="<?php echo esc_attr($tabLabelClass); ?>" data-hash="<?php echo rawurlencode($tabLabel); ?>">
	<?php echo esc_html($tabLabel); ?>
</button>

<div class="<?php echo esc_attr($tabContentClass); ?>">
	<?php echo $tabContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	?>
</div>
