<?php

/**
 * Goodbits integration class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Filters;
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
	 * Create a new instance.
	 *
	 * @param ClientInterface $goodbitsClient Inject Goodbits which holds Goodbits connect data.
	 */
	public function __construct(ClientInterface $goodbitsClient)
	{
		$this->goodbitsClient = $goodbitsClient;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Blocks string to value filter name constant.
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormFields'], 10, 3);
	}

	/**
	 * Get mapped form fields from integration.
	 *
	 * @param string $formId Form Id.
	 * @param string $itemId Integration/external form ID.
	 * @param string $innerId Integration/external additional inner form ID.
	 *
	 * @return array<string, array<int, array<string, mixed>>|string>
	 */
	public function getFormFields(string $formId, string $itemId, string $innerId): array
	{
		$output = [
			'type' => SettingsGoodbits::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
			'innerId' => $innerId,
			'fields' => [],
		];

		// Get fields.
		$item = $this->goodbitsClient->getItem($itemId);

		if (empty($item)) {
			return $output;
		}

		$fields = $this->getFields($formId);

		if (!$fields) {
			return $output;
		}

		$output['fields'] = $fields;

		return $output;
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
				'inputTracking' => 'email',
				'inputFieldLabel' => \__('Email', 'eightshift-forms'),
				'inputType' => 'email',
				'inputIsRequired' => true,
				'inputIsEmail' => true,
				'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
					'inputType',
					'inputIsRequired',
					'inputIsEmail',
				]),
			],
			[
				'component' => 'input',
				'inputName' => 'first_name',
				'inputTracking' => 'first_name',
				'inputFieldLabel' => \__('First Name', 'eightshift-forms'),
				'inputType' => 'text',
				'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
					'inputType',
				]),
			],
			[
				'component' => 'input',
				'inputName' => 'last_name',
				'inputTracking' => 'last_name',
				'inputFieldLabel' => \__('Last Name', 'eightshift-forms'),
				'inputType' => 'text',
				'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
					'inputType',
				]),
			],
			[
				'component' => 'submit',
				'submitName' => 'submit',
				'submitFieldUseError' => false,
				'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
			],
		];

		// Change the final output if necesery.
		$filterName = Filters::getFilterName(['integrations', SettingsGoodbits::SETTINGS_TYPE_KEY, 'data']);
		if (\has_filter($filterName)) {
			$output = \apply_filters($filterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
