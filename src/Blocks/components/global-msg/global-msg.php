<?php

/**
 * Template for the globalMsg Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$globalMsgAttrs = [];

$globalMsgValue = Helpers::checkAttr('globalMsgValue', $attributes, $manifest);
$globalMsgTwSelectorsData = Helpers::checkAttr('globalMsgTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($globalMsgTwSelectorsData, ['global-msg'], $attributes);

$globalMsgClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'global-msg', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('globalMsg'),
]);

$headings = [
	'success' => '',
	'error' => '',
];

$filterName = UtilsHooksHelper::getFilterName(['block', 'form', 'globalMsgHeadings']);

if (has_filter($filterName) && !is_admin()) {
	$headings = apply_filters($filterName, []);

	if (isset($headings['success'])) {
		$globalMsgAttrs[UtilsHelper::getStateAttribute('globalMsgHeadingSuccess')] = $headings['success'];
	}

	if (isset($headings['error'])) {
		$globalMsgAttrs[UtilsHelper::getStateAttribute('globalMsgHeadingError')] = $headings['error'];
	}
}

$globalMsgAttrsOutput = '';
if ($globalMsgAttrs) {
	foreach ($globalMsgAttrs as $key => $value) {
		$globalMsgAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<div
	class="<?php echo esc_attr($globalMsgClass); ?>"
	<?php echo $globalMsgAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
>
	<?php echo esc_html($globalMsgValue); ?>
</div>
