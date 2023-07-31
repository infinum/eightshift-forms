<?php

/**
 * Template for the Form Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Form\Form;
use EightshiftForms\Helpers\Encryption;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$blockSsr = $attributes['blockSsr'] ?? false;
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentFormJsClass = $manifest['componentFormJsClass'] ?? '';

$attributes = apply_filters(
	Form::FILTER_FORM_COMPONENT_ATTRIBUTES_MODIFICATIONS,
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
$formPhoneDisablePicker = Components::checkAttr('formPhoneDisablePicker', $attributes, $manifest);
$formType = Components::checkAttr('formType', $attributes, $manifest);
$formServerSideRender = Components::checkAttr('formServerSideRender', $attributes, $manifest);
$formConditionalTags = Components::checkAttr('formConditionalTags', $attributes, $manifest);
$formDownloads = Components::checkAttr('formDownloads', $attributes, $manifest);
$formSuccessRedirectVariationUrl = Components::checkAttr('formSuccessRedirectVariationUrl', $attributes, $manifest);
$formDisabledDefaultStyles = Components::checkAttr('formDisabledDefaultStyles', $attributes, $manifest);
$formHasSteps = Components::checkAttr('formHasSteps', $attributes, $manifest);

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
	Components::selector($componentFormJsClass, $componentFormJsClass),
]);

if ($formDataTypeSelector) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['typeSelector']] = esc_attr($formDataTypeSelector);
}

if ($formSuccessRedirect) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['successRedirect']] = esc_attr($formSuccessRedirect);
}

if ($formSuccessRedirectVariation) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['successRedirectVariation']] = Encryption::encryptor($formSuccessRedirectVariation);
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

if ($formPhoneDisablePicker) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['phoneDisablePicker']] = esc_attr($formPhoneDisablePicker);
}

if ($formPostId) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['formId']] = esc_attr($formPostId);
}

$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['postId']] = esc_attr((string) get_the_ID());

if ($formType) {
	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['formType']] = esc_html($formType);
}

if ($formConditionalTags) {
	// Extract just the field name from the given data, if needed.
	$rawConditionalTagData = $formConditionalTags;

	if (str_contains($formConditionalTags, 'subItems')) {
		$rawConditionalTagData = wp_json_encode(array_map(fn ($item) => [$item[0]->value, $item[1], $item[2]], json_decode($formConditionalTags)));
	}

	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['conditionalTags']] = esc_html($rawConditionalTagData);
}

if ($formDownloads || $formSuccessRedirectVariationUrl) {
	$downloadsOutput = [];

	foreach ($formDownloads as $file) {
		$condition = isset($file['condition']) ? $file['condition'] : 'all';
		$fileId = $file['id'] ?? '';

		if (!$fileId) {
			continue;
		}

		$downloadsOutput[$condition]['files'][] = $fileId;
	}

	if (!$downloadsOutput) {
		if ($formSuccessRedirectVariationUrl) {
			$downloadsOutput['all'] = Encryption::encryptor(wp_json_encode(['url' => $formSuccessRedirectVariationUrl]));
		}
	} else {
		foreach ($downloadsOutput as $key => $item) {
			if ($formSuccessRedirectVariationUrl) {
				$downloadsOutput[$key]['url'] = $formSuccessRedirectVariationUrl;
			}

			$downloadsOutput[$key] = Encryption::encryptor(wp_json_encode($downloadsOutput[$key]));
		}
	}

	$formAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['downloads']] = wp_json_encode($downloadsOutput);
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

	echo Components::render(
		'progress-bar',
		Components::props('progressBar', $attributes)
	);
	?>

	<div class="<?php echo esc_attr("{$componentClass}__fields"); ?>">
		<?php echo $formContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		?>
	</div>

	<?php
	echo Components::render(
		'loader',
		Components::props('loader', $attributes)
	);
	?>
</<?php echo $formServerSideRender ? 'div' : 'form'; ?>>
