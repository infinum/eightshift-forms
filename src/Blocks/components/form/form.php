<?php

/**
 * Template for the Form Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Form\Form;
use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$blockSsr = $attributes['blockSsr'] ?? false;
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentJsClass = $manifest['componentJsClass'] ?? '';


$attributes = apply_filters(
	Form::FILTER_FORM_SETTINGS_OPTIONS_NAME,
	$attributes
);

$formName = Components::checkAttr('formName', $attributes, $manifest);
$formAction = Components::checkAttr('formAction', $attributes, $manifest);
$formActionExternal = Components::checkAttr('formActionExternal', $attributes, $manifest);
$formMethod = Components::checkAttr('formMethod', $attributes, $manifest);
$formId = Components::checkAttr('formId', $attributes, $manifest);
$formPostId = Components::checkAttr('formPostId', $attributes, $manifest);
$formContent = Components::checkAttr('formContent', $attributes, $manifest);
$formSuccessRedirect = Components::checkAttr('formSuccessRedirect', $attributes, $manifest);
$formSuccessRedirectVariation = Components::checkAttr('formSuccessRedirectVariation', $attributes, $manifest);
$formTrackingEventName = Components::checkAttr('formTrackingEventName', $attributes, $manifest);
$formTrackingAdditionalData = Components::checkAttr('formTrackingAdditionalData', $attributes, $manifest);
$formPhoneSync = Components::checkAttr('formPhoneSync', $attributes, $manifest);
$formType = Components::checkAttr('formType', $attributes, $manifest);
$formServerSideRender = Components::checkAttr('formServerSideRender', $attributes, $manifest);
$formConditionalTags = Components::checkAttr('formConditionalTags', $attributes, $manifest);
$formDownloads = Components::checkAttr('formDownloads', $attributes, $manifest);
$formDisabledDefaultStyles = Components::checkAttr('formDisabledDefaultStyles', $attributes, $manifest);

$formDataTypeSelectorFilterName = Filters::getFilterName(['block', 'form', 'dataTypeSelector']);
$formDataTypeSelector = apply_filters(
	$formDataTypeSelectorFilterName,
	Components::checkAttr('formDataTypeSelector', $attributes, $manifest),
	$attributes
);

$formAttrs = Components::checkAttr('formAttrs', $attributes, $manifest);

$formClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($componentJsClass, $componentJsClass),
]);

if ($formDataTypeSelector) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['typeSelector']] = esc_attr($formDataTypeSelector);
}

if ($formSuccessRedirect) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['successRedirect']] = esc_attr($formSuccessRedirect);
}

if ($formSuccessRedirectVariation) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['successRedirectVariation']] = Helper::encryptor($formSuccessRedirectVariation);
}

if ($formTrackingEventName) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['trackingEventName']] = esc_attr($formTrackingEventName);
}

if ($formTrackingAdditionalData) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['trackingAdditionalData']] = esc_attr($formTrackingAdditionalData);
}

if ($formPhoneSync) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['phoneSync']] = esc_attr($formPhoneSync);
}

if ($formPostId) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['formPostId']] = esc_attr($formPostId);
}

if ($formType) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['formType']] = esc_html($formType);
}

if ($formConditionalTags) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['conditionalTags']] = esc_html($formConditionalTags);
}

if ($formDownloads) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['downloads']] = Helper::encryptor(wp_json_encode(array_map(fn ($item) => $item['id'], $formDownloads)));
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
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['actionExternal']] = esc_attr($formActionExternal);
}

if ($formMethod) {
	$formAttrs['method'] = esc_attr($formMethod);
}

$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['blockSsr']] = wp_json_encode($blockSsr);
$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['disabledDefaultStyles']] = wp_json_encode($formDisabledDefaultStyles);

$formAttrsOutput = '';
if ($formAttrs) {
	foreach ($formAttrs as $key => $value) {
		$formAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<<?php echo $formServerSideRender ? 'div' : 'form'; ?>
	class="<?php echo esc_attr($formClass); ?>"
	<?php echo $formAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
>
	<?php
	echo Components::render(
		'global-msg',
		Components::props('globalMsg', $attributes)
	);
	?>

	<div class="<?php echo esc_attr("{$componentClass}__fields"); ?>">
		<?php echo $formContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
	</div>

	<?php
	echo Components::render(
		'loader',
		Components::props('loader', $attributes)
	);
	?>
</<?php echo $formServerSideRender ? 'div' : 'form'; ?>>
