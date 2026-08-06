<?php

/**
 * Template for the globalMsg Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Settings\SettingsSettings;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$globalMsgAttrs = [];

$globalMsgValue = Helpers::checkAttr('globalMsgValue', $attributes, $manifest);
$globalMsgTwSelectorsData = FormsHelper::getTwSelectorsData($attributes);

$twClasses = FormsHelper::getTwSelectors($globalMsgTwSelectorsData, ['global-msg']);

$globalMsgClass = Helpers::clsx([
	FormsHelper::getTwBase($twClasses, 'global-msg', $componentClass),
	$additionalClass,
	UtilsHelper::getStateSelector('globalMsg'),
]);

$headings = apply_filters(
	HooksHelpers::getFilterName(['block', 'form', 'globalMsgHeadings']),
	[
		'success' => SettingsHelpers::getOptionValue(SettingsSettings::SETTINGS_GENERAL_GLOBAL_MSG_HEADING_SUCCESS),
		'error' => SettingsHelpers::getOptionValue(SettingsSettings::SETTINGS_GENERAL_GLOBAL_MSG_HEADING_ERROR),
	]
);

$globalMsgHeadingSuccess = $headings['success'] ?? '';
$globalMsgHeadingError = $headings['error'] ?? '';

if ($globalMsgHeadingSuccess) {
	$globalMsgAttrs[UtilsHelper::getStateAttribute('globalMsgHeadingSuccess')] = $globalMsgHeadingSuccess;
}

if ($globalMsgHeadingError) {
	$globalMsgAttrs[UtilsHelper::getStateAttribute('globalMsgHeadingError')] = $globalMsgHeadingError;
}

?>

<div
	class="<?php echo esc_attr($globalMsgClass); ?>"
	role="status"
	aria-live="polite"
	<?php echo wp_kses_post(Helpers::getAttrsOutput($globalMsgAttrs)); ?>>
	<?php echo esc_html($globalMsgValue); ?>
</div>
