<?php

/**
 * The class register route for Form Settings endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * Class SettingsSubmitRoute
 */
class SettingsSubmitRoute extends AbstractFormSubmit
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ValidatorInterface $validator Inject validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject validation patterns methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 * @param SecurityInterface $security Inject security methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels,
		CaptchaInterface $captcha,
		SecurityInterface $security
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
		$this->captcha = $captcha;
		$this->security = $security;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'settings';

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Detect what type of route it is.
	 *
	 * @return string
	 */
	protected function routeGetType(): string
	{
		return self::ROUTE_TYPE_SETTINGS;
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
		$manifestCustomFormParams = Components::getSettings()['customFormParams'];
		$debug = [
			'formDataReference' => $formDataReference,
		];
		$formId = $formDataReference['formId'];
		$params = $formDataReference['params'];

		// Remove unnecessary internal params before continue.
		$customFields = \array_flip(Components::flattenArray($manifestCustomFormParams));

		// Remove unnecessary params.
		foreach ($params as $key => $value) {
			if (isset($customFields[$key])) {
				unset($params[$key]);
			}
		}

		// If form ID is not set this is considered an global setting.
		// Save all fields in the settings.
		foreach ($params as $key => $value) {
			// Check if key needs updating or deleting.
			if ($value['value']) {
				if (!$formId) {
					\update_option($key, $value['value']);
				} else {
					\update_post_meta((int) $formId, $key, $value['value']);
				}
			} else {
				if (!$formId) {
					\delete_option($key);
				} else {
					\delete_post_meta((int) $formId, $key);
				}
			}
		}

		// Finish.
		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				\esc_html__('Changes saved!', 'eightshift-forms'),
				[],
				$debug
			)
		);
	}
}
