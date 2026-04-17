<?php

/**
 * Interface that holds Friendly Captcha check.
 *
 * @package EightshiftForms\FriendlyCaptcha
 */

declare(strict_types=1);

namespace EightshiftForms\FriendlyCaptcha;

/**
 * FriendlyCaptcha Interface.
 */
interface FriendlyCaptchaInterface
{
	/**
	 * Check Friendly Captcha request.
	 *
	 * @param string $response Response token from frontend.
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<mixed>
	 */
	public function check(string $response, array $formDetails = []): array;
}
