<?php

/**
 * Template for the globalMsg Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$globalMsgAttrs = [];

$globalMsgValue = Components::checkAttr('globalMsgValue', $attributes, $manifest);

$globalMsgClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Helper::getStateSelector('globalMsg'),
]);

$headings = [
	'success' => '',
	'error' => '',
];

$filterName = Filters::getFilterName(['block', 'form', 'globalMsgHeadings']);

if (has_filter($filterName) && !is_admin()) {
	$headings = apply_filters($filterName, []);

	if (isset($headings['success'])) {
		$globalMsgAttrs[Helper::getStateAttribute('globalMsgHeadingSuccess')] = $headings['success'];
	}

	if (isset($headings['error'])) {
		$globalMsgAttrs[Helper::getStateAttribute('globalMsgHeadingError')] = $headings['error'];
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
	<?php echo $globalMsgAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
>
	<?php echo esc_html($globalMsgValue); ?>
</div>
