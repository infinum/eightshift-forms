<?php

/**
 * Template for the Form Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Form\Form;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$blockSsr = $attributes['blockSsr'] ?? false;
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$attributes = apply_filters(
	Form::FILTER_FORM_COMPONENT_ATTRIBUTES_MODIFICATIONS,
	$attributes
);

$twClassesData = FormsHelper::getTwSelectorsData($attributes);
$twClasses = FormsHelper::getTwSelectors($twClassesData, ['form']);

$formName = Helpers::checkAttr('formName', $attributes, $manifest);
$formAction = Helpers::checkAttr('formAction', $attributes, $manifest);
$formActionExternal = Helpers::checkAttr('formActionExternal', $attributes, $manifest);
$formMethod = Helpers::checkAttr('formMethod', $attributes, $manifest);
$formId = Helpers::checkAttr('formId', $attributes, $manifest);
$formContent = Helpers::checkAttr('formContent', $attributes, $manifest);
$formPhoneDisablePicker = Helpers::checkAttr('formPhoneDisablePicker', $attributes, $manifest);
$formHasSteps = Helpers::checkAttr('formHasSteps', $attributes, $manifest);
$formUseSingleSubmit = Helpers::checkAttr('formUseSingleSubmit', $attributes, $manifest);
$formParentSettings = Helpers::checkAttr('formParentSettings', $attributes, $manifest);
$formSecureData = Helpers::checkAttr('formSecureData', $attributes, $manifest);

$formCustomName = $formParentSettings['customName'] ?? '';
$formPostId = $formParentSettings['postId'] ?? '';
$formConditionalTags = $formParentSettings['conditionalTags'] ?? '';
$formDisabledDefaultStyles = $formParentSettings['disabledDefaultStyles'] ?? false;
$formType = $formParentSettings['formType'] ?? '';
$formMultistepSkipScroll = $formParentSettings['multistepSkipScroll'] ?? false;

$formDataTypeSelectorFilterName = HooksHelpers::getFilterName(['block', 'form', 'dataTypeSelector']);
$formDataTypeSelector = apply_filters(
	$formDataTypeSelectorFilterName,
	$formParentSettings['dataTypeSelector'] ?? '',
	$attributes
);

$formAttrs = Helpers::checkAttr('formAttrs', $attributes, $manifest);

$customClassSelectorFilterName = HooksHelpers::getFilterName(['block', 'form', 'customClassSelector']);
$customClassSelector = apply_filters($customClassSelectorFilterName, '', $attributes, $formId);

$formClass = Helpers::clsx([
	FormsHelper::getTwBase($twClasses, 'form', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($customClassSelector, $customClassSelector),
	UtilsHelper::getStateSelector('form'),
]);

if ($formDataTypeSelector) {
	$formAttrs[UtilsHelper::getStateAttribute('typeSelector')] = esc_attr($formDataTypeSelector);
}

if ($formSecureData) {
	$formAttrs[UtilsHelper::getStateAttribute('formSecureData')] = $formSecureData; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
}

if ($formPhoneDisablePicker) {
	$formAttrs[UtilsHelper::getStateAttribute('phoneDisablePicker')] = esc_attr($formPhoneDisablePicker);
}

if ($formCustomName) {
	$formAttrs[UtilsHelper::getStateAttribute('formCustomName')] = esc_attr($formCustomName);
}

if ($formPostId) {
	$formAttrs[UtilsHelper::getStateAttribute('formFid')] = esc_attr($formPostId);
	// Generate a random form hash for unique form identification.
	$formAttrs[UtilsHelper::getStateAttribute('formId')] = esc_attr(FormsHelper::getFormUniqueHash());
}

$formAttrs[UtilsHelper::getStateAttribute('postId')] = esc_attr((string) get_the_ID());

if ($formType) {
	$formAttrs[UtilsHelper::getStateAttribute('formType')] = esc_html($formType);
}

if ($formUseSingleSubmit) {
	$formAttrs[UtilsHelper::getStateAttribute('singleSubmit')] = 'true';
}

if ($formConditionalTags) {
	// Extract just the field name from the given data, if needed.
	$rawConditionalTagData = $formConditionalTags;

	if (str_contains($formConditionalTags, 'subItems')) {
		$rawConditionalTagData = wp_json_encode(array_map(fn($item) => [$item[0]->value, $item[1], $item[2]], json_decode($formConditionalTags)));
	}

	$formAttrs[UtilsHelper::getStateAttribute('conditionalTags')] = esc_html($rawConditionalTagData);
}

if ($formId) {
	$formAttrs['id'] = esc_attr($formId);
}

if ($formName) {
	$formAttrs['name'] = esc_attr($formName);
}

if ($formAction) {
	$formAttrs['action'] = esc_attr($formAction);
}

if ($formActionExternal) {
	$formAttrs[UtilsHelper::getStateAttribute('actionExternal')] = esc_attr($formActionExternal);
}

if ($formMultistepSkipScroll) {
	$formAttrs[UtilsHelper::getStateAttribute('multistepSkipScroll')] = esc_attr($formMultistepSkipScroll);
}

if ($formMethod) {
	$formAttrs['method'] = esc_attr($formMethod);
}

$formAttrs[UtilsHelper::getStateAttribute('blockSsr')] = wp_json_encode($blockSsr);
$formAttrs[UtilsHelper::getStateAttribute('disabledDefaultStyles')] = wp_json_encode($formDisabledDefaultStyles);

?>

<form
	class="<?php echo esc_attr($formClass); ?>"
	<?php echo Helpers::getAttrsOutput($formAttrs); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>
	novalidate
	onsubmit="event.preventDefault();">
	<?php
	if (is_user_logged_in() && !is_admin()) {
		echo Helpers::render(
			'form-edit-actions',
			Helpers::props('formEditActions', $attributes, [
				'formPostId' => $formPostId,
				'formHasSteps' => $formHasSteps,
				'formEditActionsTwSelectorsData' => $twClassesData,
			])
		);
	}

	echo Helpers::render(
		'global-msg',
		Helpers::props('globalMsg', $attributes, [
			'globalMsgTwSelectorsData' => $twClassesData,
		])
	);

	echo Helpers::render(
		'progress-bar',
		Helpers::props('progressBar', $attributes, [
			'progressBarTwSelectorsData' => $twClassesData,
		])
	);
	?>

	<div class="<?php echo esc_attr(FormsHelper::getTwPart($twClasses, 'form', 'fields', "{$componentClass}__fields")); ?>">
		<?php echo $formContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
		?>

		<?php echo GeneralHelpers::getBlockAdditionalContentViaFilter('form', $attributes); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
		?>
	</div>

	<?php
	echo Helpers::render(
		'loader',
		Helpers::props('loader', $attributes, [
			'loaderTwSelectorsData' => $twClassesData,
		])
	);
	?>
</form>
