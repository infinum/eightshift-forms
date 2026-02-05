<?php

/**
 * Template for the globalMsg Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$globalMsgAttrs = [];

$globalMsgValue = Helpers::checkAttr('globalMsgValue', $attributes, $manifest);
$globalMsgTwSelectorsData = Helpers::checkAttr('globalMsgTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($globalMsgTwSelectorsData, ['global-msg']);

$globalMsgClass = Helpers::clsx([
	FormsHelper::getTwBase($twClasses, 'global-msg', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('globalMsg'),
]);

$headings = [
	'success' => '',
	'error' => '',
];

$filterName = HooksHelpers::getFilterName(['block', 'form', 'globalMsgHeadings']);

if (has_filter($filterName) && !is_admin()) {
	$headings = apply_filters($filterName, []);

	if (isset($headings['success'])) {
		$globalMsgAttrs[UtilsHelper::getStateAttribute('globalMsgHeadingSuccess')] = $headings['success'];
	}

	if (isset($headings['error'])) {
		$globalMsgAttrs[UtilsHelper::getStateAttribute('globalMsgHeadingError')] = $headings['error'];
	}
}

?>

<div
	class="<?php echo esc_attr($globalMsgClass); ?>"
	<?php echo Helpers::getAttrsOutput($globalMsgAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>>
	<?php echo esc_html($globalMsgValue); ?>
</div>
