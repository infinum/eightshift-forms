<?php

/**
 * The class register route for public form submiting endpoint - Mailer
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Entries\SettingsEntries;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * Class FormSubmitMailerRoute
 */
class FormSubmitMailerRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMailer::SETTINGS_TYPE_KEY;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security,
		FormSubmitMailerInterface $formSubmitMailer
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
		$this->formSubmitMailer = $formSubmitMailer;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . UtilsConfig::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $formDataReference Form reference got from abstract helper.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDataReference)
	{
		$formId = $formDataReference['formId'];
		$additionaDataOutput = [];

		// Save entries to DB.
		if (\apply_filters(SettingsEntries::FILTER_SETTINGS_IS_VALID_NAME, $formId)) {
			EntriesHelper::setEntryByFormDataRef($formDataReference, $formId);
		}

		// Pre response filter for addon data.
		$filterNameAddon = UtilsHooksHelper::getFilterName(['block', 'form', 'preResponseAddonData']);
		if (\has_filter($filterNameAddon)) {
			$additionaDataOutput[UtilsConfig::ROUTE_OUTPUT_ADDON_DATA_KEY] = \apply_filters($filterNameAddon, $formDataReference['addonData'], $formDataReference);
		}

		// Pre response filter for success redirect data.
		$filterNameRedirect = UtilsHooksHelper::getFilterName(['block', 'form', 'preResponseSuccessRedirectData']);
		if (\has_filter($filterNameRedirect)) {
			$additionaDataOutput[UtilsConfig::ROUTE_OUTPUT_SUCCESS_REDIRECT_DATA_KEY] = UtilsEncryption::encryptor(\wp_json_encode(\apply_filters($filterNameRedirect, [], $formDataReference)));
		}

		return \rest_ensure_response(
			$this->getFormSubmitMailer()->sendEmails($formDataReference, $additionaDataOutput)
		);
	}
}
