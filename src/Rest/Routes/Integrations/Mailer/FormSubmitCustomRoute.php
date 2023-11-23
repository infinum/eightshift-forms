<?php

/**
 * The class register route for public form submiting endpoint - custom
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailer;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidationPatternsInterface;

/**
 * Class FormSubmitCustomRoute
 */
class FormSubmitCustomRoute extends AbstractFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'custom';

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
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . AbstractBaseRoute::ROUTE_PREFIX_FORM_SUBMIT . '/' . self::ROUTE_SLUG;
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
		$params = $formDataReference['params'];
		$action = $formDataReference['action'];
		$actionExternal = $formDataReference['actionExternal'];

		$debug = [
			'formDataReference' => $formDataReference,
		];

		// If form action is not set or empty.
		if (!$action) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('customNoAction', $formId),
					[],
					$debug
				)
			);
		}

		if ($actionExternal) {
			return \rest_ensure_response(
				$this->getApiSuccessOutput(
					$this->labels->getLabel('customSuccessRedirect', $formId),
					[
						'processExternaly' => true,
					],
					$debug
				)
			);
		}

		// Prepare params for output.
		$params = Helper::prepareGenericParamsOutput($params);

		// Create a custom form action request.
		$customResponse = \wp_remote_post(
			$action,
			[
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body' => \http_build_query($params),
			]
		);

		$customResponseCode = \wp_remote_retrieve_response_code($customResponse);

		// If custom action request fails we'll return the generic error message.
		if (!$customResponseCode || $customResponseCode > 399) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('customError', $formId),
					[],
					$debug
				)
			);
		}

		// Finish.
		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				$this->labels->getLabel('customSuccess', $formId),
				[],
				$debug
			)
		);
	}
}
