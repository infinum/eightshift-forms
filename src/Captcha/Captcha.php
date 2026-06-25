<?php

/**
 * Runtime captcha dispatcher — delegates to the active provider.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

/**
 * Captcha class.
 *
 * Resolved by autowiring whenever a consumer type-hints `CaptchaInterface $captcha`.
 * Delegates to whichever provider is currently active.
 */
class Captcha implements CaptchaInterface
{
	/**
	 * Constructor.
	 *
	 * @param Recaptcha $recaptcha Google reCAPTCHA provider.
	 * @param FriendlyCaptcha $friendlyCaptcha Friendly Captcha provider.
	 */
	public function __construct(
		private readonly Recaptcha $recaptcha,
		private readonly FriendlyCaptcha $friendlyCaptcha
	) {} // phpcs:ignore

	/**
	 * Delegate to the active captcha provider.
	 *
	 * @param string $token Token from frontend.
	 * @param string $action Action to check.
	 * @param boolean $isEnterprise Type of captcha.
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<mixed>
	 */
	public function check(string $token, string $action, bool $isEnterprise, array $formDetails = []): array
	{
		return match (SettingsCaptcha::getActiveProvider()) {
			SettingsCaptcha::PROVIDER_FRIENDLY => $this->friendlyCaptcha->check($token, $action, $isEnterprise, $formDetails),
			default => $this->recaptcha->check($token, $action, $isEnterprise, $formDetails),
		};
	}
}
