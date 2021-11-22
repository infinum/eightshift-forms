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
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
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
		\add_filter(static::FILTER_MAPPER_NAME, [$this, 'getForm'], 10, 2);
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields']);
	}

	/**
	 * Map form to our components.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $formAdditionalProps Additional props.
	 *
	 * @return string
	 */
	public function getForm(string $formId, array $formAdditionalProps = []): string
	{
		$formIdDecoded = (string) Helper::encryptor('decrypt', $formId);

		// Get post ID prop.
		$formAdditionalProps['formPostId'] = $formId;

		// Get form type.
		$formAdditionalProps['formType'] = SettingsGoodbits::SETTINGS_TYPE_KEY;

		return $this->buildForm(
			$this->getFormFields($formIdDecoded),
			array_merge($formAdditionalProps, $this->getFormAdditionalProps($formIdDecoded))
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
