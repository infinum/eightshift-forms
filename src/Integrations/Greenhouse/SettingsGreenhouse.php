<?php

/**
 * Greenhouse Settings class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Helpers\TraitHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGreenhouse class.
 */
class SettingsGreenhouse implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use TraitHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_greenhouse';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_greenhouse';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'greenhouse';

	/**
	 * Greenhouse Use key.
	 */
	public const SETTINGS_GREENHOUSE_USE_KEY = 'greenhouseUse';

	/**
	 * API Key.
	 */
	public const SETTINGS_GREENHOUSE_API_KEY_KEY = 'greenhouseApiKey';

	/**
	 * Board Token Key.
	 */
	public const SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY = 'greenhouseBoardToken';

	/**
	 * Job ID Key.
	 */
	public const SETTINGS_GREENHOUSE_JOB_ID_KEY = 'greenhouseJobId';

	/**
	 * Instance variable for Greenhouse data.
	 *
	 * @var GreenhouseClientInterface
	 */
	protected $greenhouseClient;

	/**
	 * Create a new instance.
	 *
	 * @param GreenhouseClientInterface $greenhouseClient Inject Greenhouse which holds greenhouse connect data.
	 */
	public function __construct(GreenhouseClientInterface $greenhouseClient)
	{
		$this->greenhouseClient = $greenhouseClient;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsData(string $formId): array
	{
		$optionsSet = $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY);

		if (!$optionsSet) {
			return [];
		}

		$jobIdOptions = array_map(
			function($option) use ($formId) {
				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $option['id'] ?? '',
					'selectOptionIsSelected' => $this->getSettingsValue(self::SETTINGS_GREENHOUSE_JOB_ID_KEY, $formId) === $option['id'],
				];
			},
			$this->greenhouseClient->getJobsSimple()
		);

		array_unshift(
			$jobIdOptions,
			[
				'component' => 'select-option',
				'selectOptionLabel' => '',
				'selectOptionValue' => '',
			]
		);

		return [
			'sidebar' => [
				'label' => __('Greenhouse', 'eightshift-forms'),
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Greenhouse settings', 'eightshift-forms'),
					'introSubtitle' => \__('Configure your greenhouse settings in one place.', 'eightshift-forms'),
				],
				[
					'component' => 'select',
					'selectName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_JOB_ID_KEY),
					'selectId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_JOB_ID_KEY),
					'selectFieldLabel' => \__('Job ID', 'eightshift-forms'),
					'selectFieldHelp' => \__('Open your Greenhouse account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
					'selectOptions' => $jobIdOptions,
					'selectIsRequired' => true,
					'selectValue' => $this->getOptionValue(self::SETTINGS_GREENHOUSE_JOB_ID_KEY),
				]
			]
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array
	 */
	public function getSettingsGlobalData(): array
	{
		$apiKey = Variables::getApiKeyGreenhouse();
		$boardToken = Variables::getBoardTokenGreenhouse();

		return [
			'sidebar' => [
				'label' => __('Greenhouse', 'eightshift-forms'),
				'value' => self::SETTINGS_TYPE_KEY,
				'icon' => 'dashicons-admin-site-alt3',
			],
			'form' => [
				[
					'component' => 'intro',
					'introTitle' => \__('Greenhouse settings', 'eightshift-forms'),
					'introSubtitle' => \__('Configure your Greenhouse settings in one place.', 'eightshift-forms'),
				],
				[
					'component' => 'checkboxes',
					'checkboxesFieldLabel' => \__('Check options to use', 'eightshift-forms'),
					'checkboxesFieldHelp' => \__('Select integrations you want to use in your form.', 'eightshift-forms'),
					'checkboxesContent' => [
						[
							'component' => 'checkbox',
							'checkboxName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_USE_KEY),
							'checkboxId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_USE_KEY),
							'checkboxLabel' => __('Use Greenhouse', 'eightshift-forms'),
							'checkboxIsChecked' => !empty($this->getOptionValue(self::SETTINGS_GREENHOUSE_USE_KEY)),
							'checkboxValue' => 'true',
						]
					]
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
					'inputFieldLabel' => \__('API Key', 'eightshift-forms'),
					'inputFieldHelp' => \__('Open your Greenhouse account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $apiKey ?? $this->getOptionValue(self::SETTINGS_GREENHOUSE_API_KEY_KEY),
					'inputIsDisabled' => !empty($apiKey),
				],
				[
					'component' => 'input',
					'inputName' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
					'inputId' => $this->getSettingsName(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
					'inputFieldLabel' => \__('Board Token', 'eightshift-forms'),
					'inputFieldHelp' => \__('Open your Greenhouse account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
					'inputType' => 'text',
					'inputIsRequired' => true,
					'inputValue' => $boardToken ?? $this->getOptionValue(self::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY),
					'inputIsDisabled' => !empty($boardToken),
				],
			],
		];
	}
}
