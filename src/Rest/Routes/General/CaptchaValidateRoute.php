<?php

/**
 * The class register route for public form submitting endpoint - Captcha validation
 *
 * @package EightshiftForms\Rest\Routes\General;
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\General;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class CaptchaValidateRoute
 */
class CaptchaValidateRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'captcha';

	/**
	 * Instance variable of CaptchaInterface data.
	 *
	 * @var CaptchaInterface
	 */
	protected $captcha;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha
	) {
		$this->security = $security;
		$this->validator = $validator;
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
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'token' => 'string',
			'action' => 'string',
			'isEnterprise' => 'string',
		];
	}

	/**
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return false;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		// Bailout if troubleshooting skip captcha is on.
		if (DeveloperHelpers::isDeveloperSkipCaptchaActive()) {
			return [
				AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('captchaSkipCheck'),
				AbstractBaseRoute::R_DEBUG => [
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_CAPTCHA_DEBUG_SKIP_CHECK,
				],
			];
		}

		$token = $params['token'] ?? '';
		$action = $params['action'] ?? '';
		$isEnterprise = $params['isEnterprise'] ?? 'false';

		return $this->captcha->check($token, $action, $isEnterprise === 'true');
	}
}
