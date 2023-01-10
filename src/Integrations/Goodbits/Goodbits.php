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
	public function __construct(ClientInterface $goodbitsClient) {
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
		\add_filter(static::FILTER_FORM_FIELDS_NAME, [$this, 'getFormBlockGrammarArray'], 10, 3);
	}

	/**
	 * Get mapped form fields.
	 *
	 * @param string $formId Form Id.
	 * @param bool $ssr Does form load using ssr.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId, bool $ssr = false): array
	{
		return [];
	}

	public function getFormBlockGrammarArray(string $formId, string $itemId, string $innerId): array
	{
		$output = [
			'type' => SettingsGoodbits::SETTINGS_TYPE_KEY,
			'itemId' => $itemId,
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
				'inputId' => 'email',
				'inputType' => 'text',
				'inputIsRequired' => true,
				'inputIsEmail' => true,
				'inputDisabledOptions' => $this->prepareDisabledOptions('input', [
					'inputIsRequired',
				]),
			],
			[
				'component' => 'input',
				'inputName' => 'first_name',
				'inputTracking' => 'first_name',
				'inputFieldLabel' => \__('First Name', 'eightshift-forms'),
				'inputId' => 'first_name',
				'inputType' => 'text',
				'inputDisabledOptions' => $this->prepareDisabledOptions('input'),
			],
			[
				'component' => 'input',
				'inputName' => 'last_name',
				'inputTracking' => 'last_name',
				'inputFieldLabel' => \__('Last Name', 'eightshift-forms'),
				'inputId' => 'last_name',
				'inputType' => 'text',
				'inputDisabledOptions' => $this->prepareDisabledOptions('input'),
			],
			[
				'component' => 'submit',
				'submitName' => 'submit',
				'submitId' => 'submit',
				'submitFieldUseError' => false,
				'submitDisabledOptions' => $this->prepareDisabledOptions('submit'),
			],
		];

		// Change the final output if necesery.
		$dataFilterName = Filters::getIntegrationFilterName(SettingsGoodbits::SETTINGS_TYPE_KEY, 'data');
		if (\has_filter($dataFilterName) && !\is_admin()) {
			$output = \apply_filters($dataFilterName, $output, $formId) ?? [];
		}

		return $output;
	}
}
