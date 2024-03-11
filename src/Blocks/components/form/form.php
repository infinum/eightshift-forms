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
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$blockSsr = $attributes['blockSsr'] ?? false;
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

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
$formSuccessRedirectVariationUrlTitle = Components::checkAttr('formSuccessRedirectVariationUrlTitle', $attributes, $manifest);
$formDisabledDefaultStyles = Components::checkAttr('formDisabledDefaultStyles', $attributes, $manifest);
$formHasSteps = Components::checkAttr('formHasSteps', $attributes, $manifest);
$formCustomName = Components::checkAttr('formCustomName', $attributes, $manifest);
$formHideGlobalMsgOnSuccess = Components::checkAttr('formHideGlobalMsgOnSuccess', $attributes, $manifest);
$formUseSingleSubmit = Components::checkAttr('formUseSingleSubmit', $attributes, $manifest);

$formDataTypeSelectorFilterName = UtilsHooksHelper::getFilterName(['block', 'form', 'dataTypeSelector']);
$formDataTypeSelector = apply_filters(
	$formDataTypeSelectorFilterName,
	Components::checkAttr('formDataTypeSelector', $attributes, $manifest),
	$attributes
);

$formAttrs = Components::checkAttr('formAttrs', $attributes, $manifest);

$formClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
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
	<?php echo $formAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
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
					<?php echo UtilsHelper::getUtilsIcons('edit'); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
				</a>
			<?php } ?>

			<?php if (current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) { ?>
				<a
					class="<?php echo esc_attr('es-block-edit-options__edit-link') ?>"
					href="<?php echo esc_url(UtilsGeneralHelper::getSettingsPageUrl($formPostId, SettingsGeneral::SETTINGS_TYPE_KEY)) ?>"
					title="<?php esc_html_e('Edit settings', 'eightshift-forms'); ?>"
				>
				<?php echo UtilsHelper::getUtilsIcons('settings'); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
				</a>
			<?php } ?>

			<?php if (current_user_can(Forms::POST_CAPABILITY_TYPE)) { ?>
				<a
				class="<?php echo esc_attr('es-block-edit-options__edit-link') ?>"
				href="<?php echo esc_url(UtilsGeneralHelper::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY)) ?>"
				title="<?php esc_html_e('Edit global settings', 'eightshift-forms'); ?>"
			>
					<?php echo UtilsHelper::getUtilsIcons('dashboard'); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
				</a>

				<?php if ($formHasSteps) { ?>
					<a
						class="<?php echo esc_attr('es-block-edit-options__edit-link ' .  UtilsHelper::getStateSelector('stepDebugPreview')); ?>"
						href="#" class="<?php echo esc_attr('es-block-edit-options__edit-link') ?>"
						title="<?php esc_html_e('Debug form', 'eightshift-forms'); ?>"
					>
						<?php echo UtilsHelper::getUtilsIcons('debug'); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
	<?php } ?>

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
		<?php echo $formContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>

		<?php echo UtilsGeneralHelper::getBlockAdditionalContentViaFilter('form', $attributes); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
	</div>

	<?php
	echo Components::render(
		'loader',
		Components::props('loader', $attributes)
	);
	?>
</<?php echo $formServerSideRender ? 'div' : 'form'; ?>>
