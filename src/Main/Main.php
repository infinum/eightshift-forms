<?php

/**
 * The file that defines the main start class.
 *
 * A class definition that includes attributes and functions used across both the
 * theme-facing side of the site and the admin area.
 *
 * @package EightshiftForms\Main
 */

declare(strict_types=1);

namespace EightshiftForms\Main;

use EightshiftLibs\Main\AbstractMain;
use EightshiftForms\Cache;
use EightshiftForms\Captcha;
use EightshiftForms\Rest;
use EightshiftForms\Integrations;
use EightshiftFormsTests\Mocks;

/**
 * The main start class.
 *
 * This is used to define admin-specific hooks, and
 * theme-facing site hooks.
 *
 * Also maintains the unique identifier of this theme as well as the current
 * version of the theme.
 */
class Main extends AbstractMain
{

	/**
	 * Set this to true if you need dependency injection in tests.
	 *
	 * @var boolean
	 */
	private $isTest = false;

	/**
	 * Register the project with the WordPress system.
	 *
	 * The registerService method will call the register() method in every service class,
	 * which holds the actions and filters - effectively replacing the need to manually add
	 * them in one place.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('after_setup_theme', [$this, 'registerServices']);
	}

	/**
	 * Provides additional / different services depending on if we're in test or not.
	 *
	 * @param   boolean $isTest Set to true if running tests.
	 * @return void
	 */
	public function setTest(bool $isTest): void
	{
		$this->isTest = $isTest;
	}

	/**
	 * Get the list of services to register.
	 *
	 * A list of classes which contain hooks.
	 *
	 * @return array<class-string, string|string[]> Array of fully qualified service class names.
	 */
	protected function getServiceClasses(): array
	{
		return $this->isTest ? $this->getTestServiceClasses() : $this->getProdServiceClasses();
	}


	/**
	 * Get the list of services to register (for production).
	 *
	 * A list of classes which contain hooks.
	 *
	 * @return array<class-string, string|string[]> Array of fully qualified service class names.
	 */
	protected function getProdServiceClasses(): array
	{
		return [];
	}

	/**
	 * Get the list of services to register (for testing).
	 *
	 * A list of classes which contain hooks.
	 *
	 * @return array<class-string, string|string[]> Array of fully qualified service class names.
	 */
	protected function getTestServiceClasses(): array
	{
		/**
		 * Ignoring this rule because this is how PHP-DI expects the dependency tree to be defined.
		 */
		/* phpcs:disable Universal.Arrays.MixedKeyedUnkeyedArray.Found */
		return [

			// Authorization.
			Integrations\Authorization\Hmac::class,

			// Integrations Mailchimp.
			Integrations\Mailchimp\Mailchimp::class => [
				Mocks\MockMailchimpMarketingClient::class,
				Cache\TransientCache::class,
			],

			// Integrations Mailerlite.
			Integrations\Mailerlite\Mailerlite::class => [
				Mocks\MockMailerliteClient::class,
			],

			// // Captcha.
			Captcha\BasicCaptcha::class,

			// Base route.
			Mocks\TestRoute::class => [
				Integrations\Authorization\Hmac::class,
				Captcha\BasicCaptcha::class,
			],
			Mocks\TestRouteSanitization::class => [
				Integrations\Authorization\Hmac::class,
				Captcha\BasicCaptcha::class,
			],

			// // Email route.
			Rest\SendEmailRoute::class => [
				Captcha\BasicCaptcha::class,
			],

			// // Buckaroo routes.
			Integrations\Buckaroo\Buckaroo::class => [
				Integrations\Core\GuzzleClient::class,
			],
			Rest\BuckarooResponseHandlerRoute::class => [
				Integrations\Buckaroo\Buckaroo::class,
				Integrations\Authorization\Hmac::class,
			],

			// // Mailchimp.
			// Rest\MailchimpRoute::class => [
			// Integrations\Mailchimp\Mailchimp::class,
			// Captcha\BasicCaptcha::class,
			// ],

			// // Mailerlite.
			// Rest\MailerliteRoute::class => [
			// Integrations\Mailerlite\Mailerlite::class,
			// Captcha\BasicCaptcha::class,
			// ],
		];
		/* phpcs:enable Universal.Arrays.MixedKeyedUnkeyedArray.Found */
	}
}
