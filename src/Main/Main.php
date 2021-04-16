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

use EightshiftFormsVendor\EightshiftLibs\Main\AbstractMain;
use Eightshift_Libs\Manifest as Lib_Manifest;
use Eightshift_Libs\I18n as Lib_I18n;
use Eightshift_Forms\Admin;
use Eightshift_Forms\Blocks;
use Eightshift_Forms\Cache;
use Eightshift_Forms\Captcha;
use Eightshift_Forms\Rest;
use Eightshift_Forms\Enqueue;
use Eightshift_Forms\Enqueue\Localization_Constants;
use Eightshift_Forms\View;
use Eightshift_Forms\Integrations;
use EightshiftFormsTests\Mocks;
use GuzzleHttp\Client;

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
	 * The register_service method will call the register() method in every service class,
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
	 * @param	boolean $isTest Set to true if running tests.
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
		return [
			// Config.
			Config::class,

			// Manifest.
			Lib_Manifest\Manifest::class => [ Config::class ],

			// Authorization.
			Integrations\Authorization\HMAC::class,

			// Admin.
			Admin\Users::class,

			// Dynamics CRM.
			Integrations\Core\Guzzle_Client::class => [
				Client::class,
			],
			Integrations\OAuth2_Client::class => [
				Integrations\Core\Guzzle_Client::class,
			],
			Integrations\Dynamics_CRM::class => [
				Integrations\OAuth2_Client::class,
			],
			Rest\Dynamics_Crm_Route::class => [
				Config::class,
				Integrations\Dynamics_CRM::class,
				Captcha\Basic_Captcha::class,
			],
			Rest\Dynamics_Crm_Fetch_Entity_Route::class => [
				Config::class,
				Integrations\Dynamics_CRM::class,
				Integrations\Authorization\HMAC::class,
				Cache\Transient_Cache::class,
			],

			// Buckaroo.
			Integrations\Buckaroo\Buckaroo::class => [
				Integrations\Core\Guzzle_Client::class,
			],
			Rest\Buckaroo_Response_Handler_Route::class => [
				Config::class,
				Integrations\Buckaroo\Buckaroo::class,
				Integrations\Authorization\HMAC::class,
			],
			Rest\Buckaroo_Ideal_Route::class => [
				Config::class,
				Integrations\Buckaroo\Buckaroo::class,
				Rest\Buckaroo_Response_Handler_Route::class,
				Integrations\Authorization\HMAC::class,
				Captcha\Basic_Captcha::class,
			],
			Rest\Buckaroo_Emandate_Route::class => [
				Config::class,
				Integrations\Buckaroo\Buckaroo::class,
				Rest\Buckaroo_Response_Handler_Route::class,
				Integrations\Authorization\HMAC::class,
				Captcha\Basic_Captcha::class,
			],
			Rest\Buckaroo_Pay_By_Email_Route::class => [
				Config::class,
				Integrations\Buckaroo\Buckaroo::class,
				Rest\Buckaroo_Response_Handler_Route::class,
				Integrations\Authorization\HMAC::class,
				Captcha\Basic_Captcha::class,
			],

			// Mailchimp.
			Integrations\Mailchimp\Mailchimp::class => [
				Integrations\Mailchimp\Mailchimp_Marketing_Client::class,
				Cache\Transient_Cache::class,
			],
			Rest\Mailchimp_Route::class => [
				Config::class,
				Integrations\Mailchimp\Mailchimp::class,
				Captcha\Basic_Captcha::class,
			],
			Rest\Mailchimp_Fetch_Segments_Route::class => [
				Config::class,
				Integrations\Mailchimp\Mailchimp::class,
				Captcha\Basic_Captcha::class,
			],

			// Mailerlite.
			Integrations\Mailerlite\Mailerlite::class => [
				Integrations\Mailerlite\Mailerlite_Client::class,
			],
			Rest\Mailerlite_Route::class => [
				Config::class,
				Integrations\Mailerlite\Mailerlite::class,
				Captcha\Basic_Captcha::class,
			],
			Rest\Mailerlite_Fetch_Groups_Route::class => [
				Config::class,
				Integrations\Mailerlite\Mailerlite::class,
				Captcha\Basic_Captcha::class,
			],

			// Email.
			Rest\Send_Email_Route::class => [
				Config::class,
				Captcha\Basic_Captcha::class,
			],

			// Enqueue.
			Localization_Constants::class => [
				Rest\Dynamics_Crm_Route::class,
				Rest\Buckaroo_Ideal_Route::class,
				Rest\Buckaroo_Emandate_Route::class,
				Rest\Buckaroo_Pay_By_Email_Route::class,
				Rest\Send_Email_Route::class,
				Rest\Mailchimp_Route::class,
				Integrations\Mailchimp\Mailchimp::class,
				Rest\Mailerlite_Route::class,
				Integrations\Mailerlite\Mailerlite::class,
			],
			Enqueue\Enqueue_Theme::class => [
				Lib_Manifest\Manifest::class,
				Enqueue\Localization_Constants::class,
			],
			Enqueue\Enqueue_Blocks::class => [
				Lib_Manifest\Manifest::class,
				Enqueue\Localization_Constants::class,
			],
			Enqueue\Enqueue_Admin::class => [
				Lib_Manifest\Manifest::class,
				Enqueue\Localization_Constants::class,
			],

			// Admin.
			Admin\Forms::class,
			Admin\Content::class,

			// Blocks.
			Blocks\Blocks::class => [ Config::class ],

			// View.
			View\Post_View_Filter::class,
		];
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
		return [
			// Config.
			Config::class,

			// Authorization.
			Integrations\Authorization\HMAC::class,

			// Integrations Mailchimp.
			Integrations\Mailchimp\Mailchimp::class => array(
				Mocks\MockMailchimpMarketingClient::class,
				Cache\Transient_Cache::class,
			),

			// Integrations Mailerlite.
			Integrations\Mailerlite\Mailerlite::class => array(
				Mocks\MockMailerliteClient::class,
			),

			// HTTP.
			Integrations\Core\Guzzle_Client::class => array(
				Client::class,
			),

			// Captcha.
			Captcha\Basic_Captcha::class,

			// Base route.
			Mocks\TestRoute::class => array(
				Config::class,
				Integrations\Authorization\HMAC::class,
				Captcha\Basic_Captcha::class,
			),
			Mocks\TestRouteSanitization::class => array(
				Config::class,
				Integrations\Authorization\HMAC::class,
				Captcha\Basic_Captcha::class,
			),

			// Email route.
			Rest\Send_Email_Route::class => array(
				Config::class,
				Captcha\Basic_Captcha::class,
			),

			// Buckaroo routes.
			Integrations\Buckaroo\Buckaroo::class => array(
				Integrations\Core\Guzzle_Client::class,
			),
			Rest\Buckaroo_Response_Handler_Route::class => array(
				Config::class,
				Integrations\Buckaroo\Buckaroo::class,
				Integrations\Authorization\HMAC::class,
			),

			// Mailchimp.
			Rest\Mailchimp_Route::class => array(
				Config::class,
				Integrations\Mailchimp\Mailchimp::class,
				Captcha\Basic_Captcha::class,
			),

			// Mailerlite.
			Rest\Mailerlite_Route::class => array(
				Config::class,
				Integrations\Mailerlite\Mailerlite::class,
				Captcha\Basic_Captcha::class,
			),
		];
	}
}
