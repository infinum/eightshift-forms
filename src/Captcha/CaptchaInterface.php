<?php

/**
 * Interface that holds captcha.
 *
 * @package EightshiftLibs\Captcha
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
	 *
	 * @return array<mixed>
	 */
	public function check(string $token, string $action, bool $isEnterprise): array;
}
