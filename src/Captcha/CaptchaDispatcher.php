<?php

/**
 * Runtime captcha dispatcher — delegates to the active provider.
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

/**
 * CaptchaDispatcher class.
 */
class CaptchaDispatcher implements CaptchaInterface
{
	/**
	 * Google reCAPTCHA provider.
	 *
	 * @var Captcha
	 */
	private Captcha $captcha;

	/**
	 * Friendly Captcha provider.
	 *
	 * @var FriendlyCaptcha
	 */
	private FriendlyCaptcha $friendlyCaptcha;

	/**
	 * Constructor.
	 *
	 * @param Captcha $captcha Google reCAPTCHA provider.
	 * @param FriendlyCaptcha $friendlyCaptcha Friendly Captcha provider.
	 */
	public function __construct(Captcha $captcha, FriendlyCaptcha $friendlyCaptcha)
	{
		$this->captcha = $captcha;
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
		if (SettingsCaptchaProvider::getActiveProvider() === SettingsCaptchaProvider::PROVIDER_FRIENDLY) {
			return $this->friendlyCaptcha->check($token, $action, $isEnterprise, $formDetails);
		}

		return $this->captcha->check($token, $action, $isEnterprise, $formDetails);
	}
}
