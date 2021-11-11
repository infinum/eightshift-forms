<?php

/**
 * Goodbits integration class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Goodbits integration class.
 */
class Goodbits extends AbstractFormBuilder implements MapperInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter mapper.
	 *
	 * @var string
	 */
	public const FILTER_MAPPER_NAME = 'es_goodbits_mapper_filter';

	/**
	 * Filter form fields.
	 *
	 * @var string
	 */
	public const FILTER_FORM_FIELDS_NAME = 'es_goodbits_form_fields_filter';

	/**
	 * Instance variable for Goodbits data.
	 *
	 * @var ClientInterface
	 */
	protected $goodbitsClient;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	public $validator;

	/**
	 * Create a new instance.
	 *
	 * @param ClientInterface $goodbitsClient Inject Goodbits which holds Goodbits connect data.
	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 */
	public function __construct(
		ClientInterface $goodbitsClient,
		ValidatorInterface $validator
	) {
		$this->goodbitsClient = $goodbitsClient;
		$this->validator = $validator;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Blocks string to value filter name constant.
		\add_filter(static::FILTER_MAPPER_NAME, [$this, 'getForm']);
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields']);
	}

	/**
	 * Map form to our components.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getForm(string $formId): string
	{
		$formAdditionalProps = [];

		$formIdDecoded = (string) Helper::encryptor('decrypt', $formId);

		// Get post ID prop.
		$formAdditionalProps['formPostId'] = $formId;

		// Get form type.
		$formAdditionalProps['formType'] = SettingsGoodbits::SETTINGS_TYPE_KEY;

		// Reset form on success.
		$formAdditionalProps['formResetOnSuccess'] = !Variables::isDevelopMode();

		// Disable scroll to field on error.
		$formAdditionalProps['formDisableScrollToFieldOnError'] = $this->isCheckboxOptionChecked(
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);

		// Disable scroll to global message on success.
		$formAdditionalProps['formDisableScrollToGlobalMessageOnSuccess'] = $this->isCheckboxOptionChecked(
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
			SettingsGeneral::SETTINGS_GENERAL_DISABLE_SCROLL_KEY
		);

		// Tracking event name.
		$formAdditionalProps['formTrackingEventName'] = $this->getSettingsValue(
			SettingsGeneral::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY,
			$formIdDecoded
		);

		// Success redirect url.
		$formAdditionalProps['formSuccessRedirect'] = $this->getSettingsValue(
			SettingsGeneral::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY,
			$formIdDecoded
		);

		return $this->buildForm(
			$this->getFormFields($formIdDecoded),
			$formAdditionalProps
		);
	}

	/**
	 * Get mapped form fields.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId): array
	{
		// Get item Id.
		$itemId = $this->getSettingsValue(SettingsGoodbits::SETTINGS_GOODBITS_LIST_KEY, (string) $formId);
		if (empty($itemId)) {
			return [];
		}

		return $this->getFields($formId);
	}

	/**
	 * Map Goodbits fields to our components.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getFields(string $formId): array
	{
		$output = [
			[
				'component' => 'input',
				'inputName' => 'email',
				'inputFieldLabel' => __('Email', 'eightshift-forms'),
				'inputId' => 'email',
				'inputType' => 'text',
				'inputIsRequired' => true,
				'inputIsEmail' => true,
			],
			[
				'component' => 'input',
				'inputName' => 'first_name',
				'inputFieldLabel' => __('First Name', 'eightshift-forms'),
				'inputId' => 'first_name',
				'inputType' => 'text',
			],
			[
				'component' => 'input',
				'inputName' => 'last_name',
				'inputFieldLabel' => __('Last Name', 'eightshift-forms'),
				'inputId' => 'last_name',
				'inputType' => 'text',
			],
			[
				'component' => 'submit',
				'submitName' => 'submit',
				'submitId' => 'submit',
				'submitFieldUseError' => false,
				'submitFieldOrder' => 4,
			],
		];

		return $this->getIntegrationFieldsValue(
			$this->getSettingsValueGroup(SettingsGoodbits::SETTINGS_GOODBITS_INTEGRATION_FIELDS_KEY, $formId),
			$output
		);
	}
}
