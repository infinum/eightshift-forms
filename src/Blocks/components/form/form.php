<?php

/**
 * Template for the Form Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Dashboard\SettingsDashboard;
use EightshiftForms\Form\Form;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$blockSsr = $attributes['blockSsr'] ?? false;
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$attributes = apply_filters(
	Form::FILTER_FORM_COMPONENT_ATTRIBUTES_MODIFICATIONS,
	$attributes
);

$formName = Helpers::checkAttr('formName', $attributes, $manifest);
$formAction = Helpers::checkAttr('formAction', $attributes, $manifest);
$formActionExternal = Helpers::checkAttr('formActionExternal', $attributes, $manifest);
$formMethod = Helpers::checkAttr('formMethod', $attributes, $manifest);
$formId = Helpers::checkAttr('formId', $attributes, $manifest);
$formPostId = Helpers::checkAttr('formPostId', $attributes, $manifest);
$formContent = Helpers::checkAttr('formContent', $attributes, $manifest);
$formSuccessRedirect = Helpers::checkAttr('formSuccessRedirect', $attributes, $manifest);
$formSuccessRedirectVariation = Helpers::checkAttr('formSuccessRedirectVariation', $attributes, $manifest);
$formTrackingEventName = Helpers::checkAttr('formTrackingEventName', $attributes, $manifest);
$formTrackingAdditionalData = Helpers::checkAttr('formTrackingAdditionalData', $attributes, $manifest);
$formPhoneSync = Helpers::checkAttr('formPhoneSync', $attributes, $manifest);
$formPhoneDisablePicker = Helpers::checkAttr('formPhoneDisablePicker', $attributes, $manifest);
$formType = Helpers::checkAttr('formType', $attributes, $manifest);
$formServerSideRender = Helpers::checkAttr('formServerSideRender', $attributes, $manifest);
$formConditionalTags = Helpers::checkAttr('formConditionalTags', $attributes, $manifest);
$formDownloads = Helpers::checkAttr('formDownloads', $attributes, $manifest);
$formSuccessRedirectVariationUrl = Helpers::checkAttr('formSuccessRedirectVariationUrl', $attributes, $manifest);
$formSuccessRedirectVariationUrlTitle = Helpers::checkAttr('formSuccessRedirectVariationUrlTitle', $attributes, $manifest);
$formDisabledDefaultStyles = Helpers::checkAttr('formDisabledDefaultStyles', $attributes, $manifest);
$formHasSteps = Helpers::checkAttr('formHasSteps', $attributes, $manifest);
$formCustomName = Helpers::checkAttr('formCustomName', $attributes, $manifest);
$formHideGlobalMsgOnSuccess = Helpers::checkAttr('formHideGlobalMsgOnSuccess', $attributes, $manifest);
$formUseSingleSubmit = Helpers::checkAttr('formUseSingleSubmit', $attributes, $manifest);

$formDataTypeSelectorFilterName = UtilsHooksHelper::getFilterName(['block', 'form', 'dataTypeSelector']);
$formDataTypeSelector = apply_filters(
	$formDataTypeSelectorFilterName,
	Helpers::checkAttr('formDataTypeSelector', $attributes, $manifest),
	$attributes
);

$formAttrs = Helpers::checkAttr('formAttrs', $attributes, $manifest);

$customClassSelectorFilterName = UtilsHooksHelper::getFilterName(['block', 'form', 'customClassSelector']);
$customClassSelector = $formDataTypeSelector = apply_filters($customClassSelectorFilterName, '', $attributes, $formId);

$formClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($customClassSelector, $customClassSelector),
	UtilsHelper::getStateSelector('form'),
]);

if ($formDataTypeSelector) {
	$formAttrs[UtilsHelper::getStateAttribute('typeSelector')] = esc_attr($formDataTypeSelector);
}

if ($formSuccessRedirect) {
	$formAttrs[UtilsHelper::getStateAttribute('successRedirect')] = esc_attr($formSuccessRedirect);
}

if ($formSuccessRedirectVariation) {
	$formAttrs[UtilsHelper::getStateAttribute('successRedirectVariation')] = UtilsEncryption::encryptor($formSuccessRedirectVariation);
}

if ($formTrackingEventName) {
	$formAttrs[UtilsHelper::getStateAttribute('trackingEventName')] = esc_attr($formTrackingEventName);
}

if ($formTrackingAdditionalData) {
	$formAttrs[UtilsHelper::getStateAttribute('trackingAdditionalData')] = esc_attr($formTrackingAdditionalData);
}

if ($formPhoneSync) {
	$formAttrs[UtilsHelper::getStateAttribute('phoneSync')] = esc_attr($formPhoneSync);
}

if ($formPhoneDisablePicker) {
	$formAttrs[UtilsHelper::getStateAttribute('phoneDisablePicker')] = esc_attr($formPhoneDisablePicker);
}

if ($formCustomName) {
	$formAttrs[UtilsHelper::getStateAttribute('formCustomName')] = esc_attr($formCustomName);
}

if ($formPostId) {
	$formAttrs[UtilsHelper::getStateAttribute('formId')] = esc_attr($formPostId);
}

$formAttrs[UtilsHelper::getStateAttribute('postId')] = esc_attr((string) get_the_ID());

if ($formType) {
	$formAttrs[UtilsHelper::getStateAttribute('formType')] = esc_html($formType);
}

if ($formHideGlobalMsgOnSuccess) {
	$formAttrs[UtilsHelper::getStateAttribute('globalMsgHideOnSuccess')] = 'true';
}

if ($formUseSingleSubmit) {
	$formAttrs[UtilsHelper::getStateAttribute('singleSubmit')] = 'true';
}

if ($formConditionalTags) {
	// Extract just the field name from the given data, if needed.
	$rawConditionalTagData = $formConditionalTags;

	if (str_contains($formConditionalTags, 'subItems')) {
		$rawConditionalTagData = wp_json_encode(array_map(fn ($item) => [$item[0]->value, $item[1], $item[2]], json_decode($formConditionalTags)));
	}

	$formAttrs[UtilsHelper::getStateAttribute('conditionalTags')] = esc_html($rawConditionalTagData);
}

if ($formDownloads || $formSuccessRedirectVariationUrl) {
	$downloadsOutput = [];

	foreach ($formDownloads as $file) {
		$condition = isset($file['condition']) && !empty($file['condition']) ? $file['condition'] : 'all';
		$fileId = $file['id'] ?? '';
		$fileTitle = $file['fileTitle'] ?? '';

		if (!$fileId) {
			continue;
		}

		$downloadsOutput[$condition]['files'][] = [
			$fileId,
			$fileTitle,
		];
	}

	if (!$downloadsOutput) {
		if ($formSuccessRedirectVariationUrl) {
			$downloadsOutput['all'] = UtilsEncryption::encryptor(wp_json_encode([
				'main' => [
					$formSuccessRedirectVariationUrl,
					$formSuccessRedirectVariationUrlTitle,
				],
			]));
		}
	} else {
		foreach ($downloadsOutput as $key => $item) {
			if ($formSuccessRedirectVariationUrl) {
				$downloadsOutput[$key]['main'] = [
					$formSuccessRedirectVariationUrl,
					$formSuccessRedirectVariationUrlTitle,
				];
			}

			$downloadsOutput[$key] = UtilsEncryption::encryptor(wp_json_encode($downloadsOutput[$key]));
		}
	}

	$formAttrs[UtilsHelper::getStateAttribute('successRedirectDownloads')] = wp_json_encode($downloadsOutput);
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

if ($formMethod) {
	$formAttrs['method'] = esc_attr($formMethod);
}

$formAttrs[UtilsHelper::getStateAttribute('blockSsr')] = wp_json_encode($blockSsr);
$formAttrs[UtilsHelper::getStateAttribute('disabledDefaultStyles')] = wp_json_encode($formDisabledDefaultStyles);

$formAttrsOutput = '';
if ($formAttrs) {
	foreach ($formAttrs as $key => $value) {
		$formAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<<?php echo $formServerSideRender ? 'div' : 'form'; ?>
	class="<?php echo esc_attr($formClass); ?>"
	<?php echo $formAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
	novalidate
	onsubmit="event.preventDefault();"
>
	<?php if (is_user_logged_in() && !is_admin()) { ?>
		<div class="<?php echo esc_attr('es-block-edit-options__edit-wrap') ?>">
			<?php if (current_user_can(Forms::POST_CAPABILITY_TYPE)) { ?>
				<a
					class="<?php echo esc_attr('es-block-edit-options__edit-link') ?>"
					href="<?php echo esc_url(UtilsGeneralHelper::getFormEditPageUrl($formPostId)) ?>"
					title="<?php esc_html_e('Edit form', 'eightshift-forms'); ?>"
				>
					<?php echo UtilsHelper::getUtilsIcons('edit'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
				</a>
			<?php } ?>

			<?php if (current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) { ?>
				<a
					class="<?php echo esc_attr('es-block-edit-options__edit-link') ?>"
					href="<?php echo esc_url(UtilsGeneralHelper::getSettingsPageUrl($formPostId, SettingsGeneral::SETTINGS_TYPE_KEY)) ?>"
					title="<?php esc_html_e('Edit settings', 'eightshift-forms'); ?>"
				>
				<?php echo UtilsHelper::getUtilsIcons('settings'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
				</a>
			<?php } ?>

			<?php if (current_user_can(Forms::POST_CAPABILITY_TYPE)) { ?>
				<a
				class="<?php echo esc_attr('es-block-edit-options__edit-link') ?>"
				href="<?php echo esc_url(UtilsGeneralHelper::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY)) ?>"
				title="<?php esc_html_e('Edit global settings', 'eightshift-forms'); ?>"
			>
					<?php echo UtilsHelper::getUtilsIcons('dashboard'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
				</a>

				<?php if ($formHasSteps) { ?>
					<a
						class="<?php echo esc_attr('es-block-edit-options__edit-link ' .  UtilsHelper::getStateSelector('stepDebugPreview')); ?>"
						href="#" class="<?php echo esc_attr('es-block-edit-options__edit-link') ?>"
						title="<?php esc_html_e('Debug form', 'eightshift-forms'); ?>"
					>
						<?php echo UtilsHelper::getUtilsIcons('debug'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
	<?php } ?>

	<?php
	echo Helpers::render(
		'global-msg',
		Helpers::props('globalMsg', $attributes)
	);

	echo Helpers::render(
		'progress-bar',
		Helpers::props('progressBar', $attributes)
	);
	?>

	<div class="<?php echo esc_attr("{$componentClass}__fields"); ?>">
		<?php echo $formContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>

		<?php echo UtilsGeneralHelper::getBlockAdditionalContentViaFilter('form', $attributes); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
	</div>

	<?php
	echo Helpers::render(
		'loader',
		Helpers::props('loader', $attributes)
	);
	?>
</<?php echo $formServerSideRender ? 'div' : 'form'; ?>>
