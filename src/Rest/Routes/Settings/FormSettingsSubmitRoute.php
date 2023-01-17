<?php

/**
 * The class register route for Form Settings endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\AdminMenus\FormGlobalSettingsAdminSubMenu;
use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\ValidationPatternsInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use WP_REST_Request;

/**
 * Class FormSettingsSubmitRoute
 */
class FormSettingsSubmitRoute extends AbstractFormSubmit
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Instance variable of ValidatorInterface data.
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * Instance variable of ValidationPatternsInterface data.
	 *
	 * @var ValidationPatternsInterface
	 */
	protected $validationPatterns;

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance that injects classes
	 *
	 	 * @param ValidatorInterface $validator Inject ValidatorInterface which holds validation methods.
	 * @param ValidationPatternsInterface $validationPatterns Inject ValidationPatternsInterface which holds validation methods.
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 */
	public function __construct(
		ValidatorInterface $validator,
		ValidationPatternsInterface $validationPatterns,
		LabelsInterface $labels
	) {
		$this->validator = $validator;
		$this->validationPatterns = $validationPatterns;
		$this->labels = $labels;
	}

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/form-settings-submit';

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
	 * Returns validator class.
	 *
	 * @return ValidatorInterface
	 */
	protected function getValidator()
	{
		return $this->validator;
	}

	/**
	 * Returns validator patterns class.
	 *
	 * @return ValidationPatternsInterface
	 */
	protected function getValidatorPatterns()
	{
		return $this->validationPatterns;
	}

	/**
	 * Returns validator labels class.
	 *
	 * @return LabelsInterface
	 */
	protected function getValidatorLabels()
	{
		return $this->labels;
	}

	/**
	 * Implement submit action.
	 *
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 *
	 * @return mixed
	 */
	protected function submitAction(array $formDataRefrerence)
	{
		// Remove unnecessary internal params before continue.
		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

		// Remove unnecessary params.
		foreach ($formDataRefrerence['params'] as $key => $value) {
			if (isset($customFields[$key])) {
				unset($formDataRefrerence['params'][$key]);
			}
		}

		// Determine form type to use.
		switch ($formDataRefrerence['type']) {
			case SettingsCache::SETTINGS_TYPE_KEY:
				return $this->cache($formDataRefrerence['params']);
			default:
			// error_log( print_r( ( $formDataRefrerence ), true ) );
			
				// If form ID is not set this is considered an global setting.
				// Save all fields in the settings.
				foreach ($formDataRefrerence['params'] as $key => $value) {
					// Check if key needs updating or deleting.
					if ($value['value']) {
						if (!$formDataRefrerence['formId']) {
							\update_option($key, $value['value']);
						} else {
							\update_post_meta((int) $formDataRefrerence['formId'], $key, $value['value']);
						}
					} else {
						if (!$formDataRefrerence['formId']) {
							\delete_option($key);
						} else {
							\delete_post_meta((int) $formDataRefrerence['formId'], $key);
						}
					}
				}
				break;
		}

		// Finish.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => \esc_html__('Changes saved!', 'eightshift-forms'),
		]);
	}

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	// public function routeCallback(WP_REST_Request $request)
	// {
	// 		// Remove unnecessary internal params before continue.
	// 		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

	// 		// Remove unnecessary params.
	// 		foreach ($params as $key => $value) {
	// 			if (isset($customFields[$key])) {
	// 				unset($params[$key]);
	// 			}
	// 		}

	// 		// Determine form type to use.
	// 		switch ($formType) {
	// 			case SettingsCache::SETTINGS_TYPE_KEY:
	// 				return $this->cache($params);
	// 			default:
	// 				// If form ID is not set this is considered an global setting.
	// 				if (!$formId) {
	// 					// Save all fields in the settings.
	// 					foreach ($params as $key => $value) {
	// 						// Check if key needs updating or deleting.
	// 						if ($value['value']) {
	// 							\update_option($key, $value['value']);
	// 						} else {
	// 							\delete_option($key);
	// 						}
	// 					}
	// 				} else {
	// 					// Save all fields in the settings.
	// 					foreach ($params as $key => $value) {
	// 						// Check if key needs updating or deleting.
	// 						if ($value['value']) {
	// 							\update_post_meta((int) $formId, $key, $value['value']);
	// 						} else {
	// 							\delete_post_meta((int) $formId, $key);
	// 						}
	// 					}
	// 				}
	// 				break;
	// 		}

	// 		return \rest_ensure_response([
	// 			'code' => 200,
	// 			'status' => 'success',
	// 			'message' => \esc_html__('Changes saved!', 'eightshift-forms'),
	// 		]);
	// 	} catch (UnverifiedRequestException $e) {
	// 		// Die if any of the validation fails.
	// 		return \rest_ensure_response(
	// 			[
	// 				'code' => 400,
	// 				'status' => 'error_validation',
	// 				'message' => $e->getMessage(),
	// 				'validation' => $e->getData(),
	// 			]
	// 		);
	// 	}
	// }

	/**
	 * Delete transient cache from the DB.
	 *
	 * @param array<int|string, mixed> $params Keys to delete.
	 *
	 * @return mixed
	 */
	private function cache(array $params)
	{
		if (! \current_user_can(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
			]);
		}

		foreach ($params as $key => $items) {
			if (!isset(Filters::ALL[$key]['cache'])) {
				continue;
			}

			foreach (Filters::ALL[$key]['cache'] as $item) {
				\delete_transient($item);
			}
		}

		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => \esc_html__('Selected cache successfully deleted!', 'eightshift-forms'),
		]);
	}
}
