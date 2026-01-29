<?php

/**
 * Interface that holds captcha.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

/**
 * Captcha Interface.
 */
interface CaptchaInterface
{
	/**
	 * Check captcha request.
	 *
	 * @param string $token Token from frontend.
	 * @param string $action Action to check.
	 * @param boolean $isEnterprise Type of captcha.
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<mixed>
	 */
	public function check(string $token, string $action, bool $isEnterprise, array $formDetails = []): array;
}
