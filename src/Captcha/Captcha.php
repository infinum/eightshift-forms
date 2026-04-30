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
	 * Google reCAPTCHA provider.
	 *
	 * @var Recaptcha
	 */
	private Recaptcha $recaptcha;

	/**
	 * Friendly Captcha provider.
	 *
	 * @var FriendlyCaptcha
	 */
	private FriendlyCaptcha $friendlyCaptcha;

	/**
	 * Constructor.
	 *
	 * @param Recaptcha $recaptcha Google reCAPTCHA provider.
	 * @param FriendlyCaptcha $friendlyCaptcha Friendly Captcha provider.
	 */
	public function __construct(Recaptcha $recaptcha, FriendlyCaptcha $friendlyCaptcha)
	{
		$this->recaptcha = $recaptcha;
		$this->friendlyCaptcha = $friendlyCaptcha;
	}

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
		switch (SettingsCaptcha::getActiveProvider()) {
			case SettingsCaptcha::PROVIDER_FRIENDLY:
				return $this->friendlyCaptcha->check($token, $action, $isEnterprise, $formDetails);
			default:
				return $this->recaptcha->check($token, $action, $isEnterprise, $formDetails);
		}
	}
}
