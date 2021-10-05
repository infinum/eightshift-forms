<?php

/**
 * Mailchimp Settings class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\GlobalSettings\SettingsGlobalDataInterface;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailchimp class.
 */
class SettingsMailchimp implements SettingsDataInterface, SettingsGlobalDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_mailchimp';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_mailchimp';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_mailchimp';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_mailchimp';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'mailchimp';

	/**
	 * Mailchimp Use key.
	 */
	public const SETTINGS_MAILCHIMP_USE_KEY = 'mailchimpUse';

	/**
	 * API Key.
	 */
	public const SETTINGS_MAILCHIMP_API_KEY_KEY = 'mailchimpApiKey';

	/**
	 * Form url.
	 */
	public const SETTINGS_MAILCHIMP_FORM_URL_KEY = 'mailchimpFormUrl';

	/**
	 * List ID Key.
	 */
	public const SETTINGS_MAILCHIMP_LIST_KEY = 'mailchimpList';

	/**
	 * Instance variable for Mailchimp data.
	 *
	 * @var MailchimpClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Create a new instance.
	 *
	 * @param MailchimpClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 */
	public function __construct(MailchimpClientInterface $mailchimpClient)
	{
		$this->mailchimpClient = $mailchimpClient;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid']);
	}

	/**
	 * Determin if settings are valid.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(string $formId): bool
	{
		if (!$this->isSettingsGlobalValid($formId)) {
			return false;
		}

		$list = $this->getSettingsValue(SettingsMailchimp::SETTINGS_MAILCHIMP_LIST_KEY, $formId);

		if (empty($list)) {
			return false;
		}

		return true;
	}

	/**
	 * Determin if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->getOptionValue(SettingsMailchimp::SETTINGS_MAILCHIMP_USE_KEY);
		$apiKey = Variables::getApiKeyMailchimp() ?? $this->getOptionValue(SettingsMailchimp::SETTINGS_MAILCHIMP_API_KEY_KEY);

		if (empty($isUsed) || empty($apiKey)) {
			return false;
		}

		return true;
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array
	 */
	public function getSettingsSidebar(): array
	{
		if (!$this->isSettingsGlobalValid()) {
			return [];
		}

		return [
			'label' => __('Mailchimp', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><path id="a" d="M0 0h28.267v30H0z"/></defs><g fill="none" fill-rule="evenodd"><path d="M22.262 14.176a2.42 2.42 0 01.622 0c.112-.255.13-.697.03-1.177-.149-.714-.35-1.146-.768-1.079-.417.068-.432.585-.283 1.299.084.402.233.745.4.957M18.68 14.741c.3.131.483.218.555.142.046-.047.032-.137-.04-.254-.147-.24-.45-.484-.771-.621-.658-.283-1.442-.189-2.047.246-.2.146-.389.349-.362.472.01.04.039.07.109.08.165.018.74-.273 1.403-.313.468-.029.855.117 1.154.248M18.08 15.084c-.388.061-.603.19-.74.309-.118.103-.19.216-.19.296 0 .038.017.06.03.07a.093.093 0 00.064.025c.088 0 .284-.079.284-.079.541-.194.898-.17 1.252-.13.195.022.288.034.33-.033.013-.019.029-.06-.01-.124-.092-.148-.484-.398-1.02-.334M21.053 16.342c.264.13.555.079.65-.114.094-.192-.043-.454-.307-.583-.264-.13-.555-.08-.65.113-.094.193.043.454.307.584M22.75 14.859c-.214-.004-.392.232-.397.526-.005.294.165.535.38.539.214.003.391-.232.396-.526.005-.294-.164-.536-.379-.54" fill="#1A1919"/><g transform="translate(1)"><mask id="b" fill="#fff"><use xlink:href="#a"/></mask><path d="M7.346 20.162c-.054-.067-.141-.047-.226-.027a.797.797 0 01-.2.028.434.434 0 01-.366-.186c-.098-.15-.092-.373.015-.63l.05-.114c.173-.386.46-1.032.137-1.647-.243-.463-.64-.752-1.117-.813a1.436 1.436 0 00-1.23.444c-.474.523-.548 1.236-.457 1.487.034.092.087.118.125.123.08.011.2-.048.275-.249l.021-.065c.033-.107.095-.305.197-.463a.861.861 0 011.194-.249c.332.217.46.625.319 1.013-.074.201-.193.585-.167.901.053.64.447.896.8.923.343.013.583-.18.644-.32.035-.084.005-.135-.014-.156" fill="#1A1919" mask="url(#b)"/><path d="M10.61 8.425c1.12-1.294 2.498-2.42 3.733-3.05.043-.023.088.024.065.066-.098.177-.287.557-.347.846-.01.045.04.079.078.053.768-.524 2.105-1.085 3.277-1.157.05-.003.074.061.034.092a2.79 2.79 0 00-.515.517.05.05 0 00.039.08c.823.006 1.983.294 2.74.718.05.029.014.128-.043.115-1.144-.262-3.017-.461-4.963.013-1.737.424-3.063 1.078-4.03 1.782-.049.035-.108-.03-.068-.075zm10.195 13.083a.09.09 0 00.052-.09.084.084 0 00-.092-.075s-2.39.354-4.647-.473c.245-.8.9-.51 1.888-.43a13.806 13.806 0 004.558-.493c1.022-.294 2.365-.872 3.408-1.696.352.773.476 1.624.476 1.624s.272-.05.5.091c.215.132.373.408.265 1.119-.22 1.329-.784 2.407-1.734 3.4a7.143 7.143 0 01-2.082 1.555c-.426.224-.88.418-1.36.575-3.578 1.168-7.241-.117-8.422-2.876a4.433 4.433 0 01-.237-.652c-.503-1.818-.076-4 1.26-5.373.082-.088.166-.191.166-.32 0-.11-.069-.224-.129-.305-.467-.677-2.085-1.832-1.76-4.067.233-1.605 1.637-2.736 2.946-2.669l.332.02c.567.033 1.062.106 1.529.125.782.034 1.484-.08 2.317-.773.28-.234.506-.437.887-.502.04-.007.14-.042.338-.033.204.011.397.067.57.182.668.445.763 1.52.798 2.307.02.45.074 1.536.092 1.848.043.713.23.814.61.938.213.07.41.123.703.205.883.248 1.407.5 1.737.823.197.202.288.416.317.621.104.76-.59 1.699-2.428 2.552-2.009.932-4.445 1.168-6.13.98a433.8 433.8 0 01-.589-.066c-1.347-.182-2.115 1.559-1.307 2.751.521.769 1.94 1.27 3.36 1.27 3.255 0 5.757-1.39 6.687-2.59.028-.037.03-.04.075-.107.045-.069.008-.107-.05-.068-.76.52-4.137 2.586-7.75 1.965 0 0-.438-.073-.84-.229-.318-.123-.984-.43-1.065-1.114 2.915.902 4.751.05 4.751.05zM4.792 14.579c-1.014.197-1.907.772-2.454 1.566-.326-.273-.935-.8-1.042-1.006-.873-1.656.952-4.877 2.226-6.696 3.15-4.495 8.084-7.897 10.367-7.28.372.105 1.601 1.53 1.601 1.53s-2.283 1.268-4.4 3.033c-2.852 2.197-5.007 5.389-6.298 8.853zm1.704 7.602c-.154.026-.31.036-.468.033-1.526-.041-3.173-1.415-3.337-3.044-.181-1.8.739-3.185 2.367-3.514.195-.04.43-.062.684-.049.913.05 2.257.751 2.565 2.739.272 1.76-.16 3.553-1.811 3.835zm20.665-3.188c-.013-.046-.098-.357-.215-.733-.117-.375-.238-.639-.238-.639.47-.702.478-1.33.415-1.686-.066-.441-.25-.817-.62-1.205-.37-.389-1.127-.787-2.19-1.085-.122-.035-.524-.145-.559-.155-.003-.023-.03-1.316-.053-1.871-.018-.401-.052-1.027-.247-1.644-.231-.835-.635-1.566-1.139-2.033 1.39-1.441 2.258-3.028 2.256-4.39-.004-2.618-3.22-3.41-7.183-1.77l-.84.357c-.003-.004-1.517-1.49-1.54-1.51C10.49-3.31-3.637 12.391.879 16.204l.987.837c-.256.663-.356 1.423-.274 2.24.105 1.05.647 2.056 1.524 2.833.833.738 1.929 1.205 2.992 1.204 1.757 4.05 5.773 6.535 10.482 6.675 5.05.15 9.29-2.22 11.067-6.477.116-.299.61-1.645.61-2.834 0-1.194-.676-1.69-1.106-1.69z" fill="#1A1919" mask="url(#b)"/></g></g></svg>',
		];
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
		if (!$this->isSettingsGlobalValid()) {
			return [];
		}

		$listsOptions = array_map(
			function ($option) use ($formId) {
				return [
					'component' => 'select-option',
					'selectOptionLabel' => $option['title'] ?? '',
					'selectOptionValue' => $option['id'] ?? '',
					'selectOptionIsSelected' => $this->getSettingsValue(self::SETTINGS_MAILCHIMP_LIST_KEY, $formId) === $option['id'],
				];
			},
			$this->mailchimpClient->getLists()
		);

		array_unshift(
			$listsOptions,
			[
				'component' => 'select-option',
				'selectOptionLabel' => '',
				'selectOptionValue' => '',
			]
		);

		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Mailchimp settings', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your mailchimp settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'select',
				'selectName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_KEY),
				'selectId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_LIST_KEY),
				'selectFieldLabel' => \__('List', 'eightshift-forms'),
				'selectFieldHelp' => \__('Select list for subscription.', 'eightshift-forms'),
				'selectOptions' => $listsOptions,
				'selectIsRequired' => true,
				'selectValue' => $this->getSettingsValue(self::SETTINGS_MAILCHIMP_LIST_KEY, $formId),
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array
	 */
	public function getSettingsGlobalData(): array
	{
		$apiKey = Variables::getApiKeyMailchimp();

		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Mailchimp settings', 'eightshift-forms'),
				'introSubtitle' => \__('Configure your mailchimp settings in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => \__('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => \__('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_USE_KEY),
						'checkboxId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_USE_KEY),
						'checkboxLabel' => __('Use Mailchimp', 'eightshift-forms'),
						'checkboxIsChecked' => !empty($this->getOptionValue(self::SETTINGS_MAILCHIMP_USE_KEY)),
						'checkboxValue' => 'true',
					]
				]
			],
			[
				'component' => 'input',
				'inputName' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
				'inputId' => $this->getSettingsName(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
				'inputFieldLabel' => \__('API Key', 'eightshift-forms'),
				'inputFieldHelp' => \__('Open your Mailchimp account and provide API key. You can provide API key using global variable also.', 'eightshift-forms'),
				'inputType' => 'password',
				'inputIsRequired' => true,
				'inputValue' => !empty($apiKey) ? $apiKey : $this->getOptionValue(self::SETTINGS_MAILCHIMP_API_KEY_KEY),
				'inputIsDisabled' => !empty($apiKey),
			]
		];
	}
}
