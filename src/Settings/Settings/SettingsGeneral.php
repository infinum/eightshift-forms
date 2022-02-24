<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeneral class.
 */
class SettingsGeneral implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_general';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_general';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_general';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'general';

	/**
	 * Redirection Success key.
	 */
	public const SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY = 'general-redirection-success';

	/**
	 * Tracking event name key.
	 */
	public const SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY = 'general-tracking-event-name';

	/**
	 * Disable default enqueue key.
	 */
	public const SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY = 'general-disable-default-enqueue';
	public const SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY = 'scripts';
	public const SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY = 'autoinit';
	public const SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY = 'styles';

	/**
	 * Disable scroll settings key.
	 */
	public const SETTINGS_GENERAL_DISABLE_SCROLL_KEY = 'general-disable-scroll';
	public const SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR = 'disable-scroll-to-field-on-error';
	public const SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS = 'disable-scroll-to-global-message-on-success';

	/**
	 * Disable custom options on fields key.
	 */
	public const SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY = 'general-custom-options';
	public const SETTINGS_GENERAL_CUSTOM_OPTIONS_SELECT = 'select';
	public const SETTINGS_GENERAL_CUSTOM_OPTIONS_TEXTAREA = 'textarea';
	public const SETTINGS_GENERAL_CUSTOM_OPTIONS_FILE = 'file';

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
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('General', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.196 1.41c-1.813-.743-3.59-.31-4.25 0 .118 1.619-.581 4.37-4.321 2.428-.661.62-2.012 2.186-2.125 3.5 1.653.761 3.995 2.885.142 5.285.236.976.963 3.042 1.983 3.499 1.417-1 4.264-1.9 4.32 2.5.922.285 3.06.685 4.25 0-.117-1.762.567-4.728 4.25-2.5.567-.476 1.772-1.843 2.055-3.5-1.511-.928-3.627-3.285 0-5.284-.212-.834-.935-2.7-2.125-3.5-3.287 1.943-4.156-.81-4.18-2.428z" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><ellipse cx="10.071" cy="9.91" rx="2.975" ry="3" stroke="#29A3A3" stroke-width="1.5" fill="none"/></svg>',
		];
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		$successRedirectUrl = [
			'component' => 'input',
			'inputName' => $this->getSettingsName(self::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY),
			'inputId' => $this->getSettingsName(self::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY),
			'inputFieldLabel' => __('After submit redirect URL', 'eightshift-forms'),
			// translators: %s will be replaced with forms field name.
			'inputFieldHelp' => sprintf(__('
				If URL is provided, after a successful submission the user is redirected to the provided URL. The success message will <strong>not</strong> be shown.
				<br /> <br />
				Data from the form can be used in the form of template tags (<code>{field-name}</code>).
				<br /> <br />
				These tags are detected from the form:
				<br />
				%s
				<br /> <br />
				If some tags are missing or you don\'t see any tags above, check that the <code>name</code> on the form field is set in the Form editor.', 'eightshift-forms'), Helper::getFormNames($formId)),
			'inputType' => 'url',
			'inputIsUrl' => true,
			'inputValue' => $this->getSettingsValue(self::SETTINGS_GENERAL_REDIRECTION_SUCCESS_KEY, $formId),
		];

		if (has_filter(Filters::getBlockFilterName('form', 'successRedirectUrl'))) {
			$successRedirectUrl['inputFieldHelp'] = $successRedirectUrl['inputFieldHelp'] . '<br /> <strong>' . __('The redirect URL is set by a global constant, the value above will be ignored.', 'eightshift-forms') . '</strong>';
		}

		$trackingEventName = [
			'component' => 'input',
			'inputName' => $this->getSettingsName(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY),
			'inputId' => $this->getSettingsName(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY),
			'inputFieldLabel' => __('Tracking event name', 'eightshift-forms'),
			'inputFieldHelp' => __('Used when pushing data to Google Tag Manager.', 'eightshift-forms'),
			'inputType' => 'text',
			'inputValue' => $this->getSettingsValue(self::SETTINGS_GENERAL_TRACKING_EVENT_NAME_KEY, $formId),
		];

		if (has_filter(Filters::getBlockFilterName('form', 'trackingEventName'))) {
			$trackingEventName['inputFieldHelp'] = $trackingEventName['inputFieldHelp'] . '<br /> <strong>' . __('The tracking event name is set by a global constant, the value above will be ignored.', 'eightshift-forms') . '</strong>';
		}

		return [
			$successRedirectUrl,
			$trackingEventName,
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		return [
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Built-in scripts and styles', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('
					Don\'t forget to provide your own scripts and styles if you disable the built-in ones. You can get a template for the stylesheet using a WP CLI command.
					<br /> <br />
					<i>Disable default styles</i> will disable all the frontend and block editor styles.
					<br /> <br />
					<i>Disable default scripts</i> will remove all the frontend logic, including validation and form submission.
					<br /> <br />
					<i>Don\'t auto-initialize scripts</i> will load all the scripts, but not initialize them. To learn how to do it manually refer to the documentation.
				', 'eightshift-forms'),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Disable default styles', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
						'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Disable default scripts', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
						'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Don\'t auto-initialize scripts', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
						'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY,
					]
				]
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Custom fields', 'eightshift-forms'),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY),
				'checkboxesFieldHelp' => __('If checked, fields will use the default browser implementation.', 'eightshift-forms'),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Disable custom selection dropdown', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_CUSTOM_OPTIONS_SELECT, self::SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY),
						'checkboxValue' => self::SETTINGS_GENERAL_CUSTOM_OPTIONS_SELECT,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Disable custom textarea', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_CUSTOM_OPTIONS_TEXTAREA, self::SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY),
						'checkboxValue' => self::SETTINGS_GENERAL_CUSTOM_OPTIONS_TEXTAREA,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Disable custom file picker', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_CUSTOM_OPTIONS_FILE, self::SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY),
						'checkboxValue' => self::SETTINGS_GENERAL_CUSTOM_OPTIONS_FILE,
					],
				]
			],
			[
				'component' => 'divider',
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('After submitting the form', 'eightshift-forms'),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
				'checkboxesFieldHelp' => __('If checked, forms will not use these features.', 'eightshift-forms'),
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Disable scroll to first field with an error', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR, self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
						'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Disable scroll to top of the form to see the success message', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS, self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
						'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
					]
				]
			],
		];
	}
}
