<?php

/**
 * The class register route for public form submitting endpoint - Captcha
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class SubmitCaptchaRoute
 */
class SubmitCaptchaRoute extends AbstractSimpleFormSubmit
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
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param CaptchaInterface $captcha Inject captcha methods.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		CaptchaInterface $captcha
	) {
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
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(): array
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
				AbstractBaseRoute::R_MSG => $this->labels->getLabel('captchaSkipCheck'),
				AbstractBaseRoute::R_DEBUG => [
					AbstractBaseRoute::R_DEBUG_KEY => 'captchaDebugSkipCheck',
				],
			];
		}

		$token = $params['token'] ?? '';
		$action = $params['action'] ?? '';
		$isEnterprise = $params['isEnterprise'] ?? 'false';

		return $this->captcha->check($token, $action, $isEnterprise === 'true');
	}
}
