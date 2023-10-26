<?php

/**
 * The class register route for public form submiting endpoint - Captcha
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Troubleshooting\SettingsDebug;
use Throwable;
use WP_REST_Request;

/**
 * Class SubmitCaptchaRoute
 */
class SubmitCaptchaRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'captcha';

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Instance variable of CaptchaInterface data.
	 *
	 * @var CaptchaInterface
	 */
	protected $captcha;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param LabelsInterface $labels Inject LabelsInterface which holds labels data.
	 * @param CaptchaInterface $captcha Inject CaptchaInterface which holds captcha data.
	 */
	public function __construct(
		LabelsInterface $labels,
		CaptchaInterface $captcha
	) {
		$this->labels = $labels;
		$this->captcha = $captcha;
	}

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
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
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
	public function routeCallback(WP_REST_Request $request)
	{
		$debug = [
			'request' => $request,
		];

		// Bailout if troubleshooting skip captcha is on.
		if (\apply_filters(SettingsDebug::FILTER_SETTINGS_IS_DEBUG_ACTIVE, SettingsDebug::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY)) {
			return \rest_ensure_response(
				$this->getApiSuccessOutput(
					\esc_html__('Form captcha skipped due to troubleshooting config set in settings.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		try {
			$params = $this->prepareSimpleApiParams($request);

			$token = $params['token'] ?? '';
			$action = $params['action'] ?? '';
			$isEnterprise = $params['isEnterprise'] ?? false;

			return \rest_ensure_response(
				$this->captcha->check($token, $action, (bool) $isEnterprise)
			);
		} catch (Throwable $t) {
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$this->labels->getLabel('captchaBadRequest'),
					[],
					\array_merge(
						$debug,
						[
							'exeption' => $t,
						]
					)
				)
			);
		}
	}
}
