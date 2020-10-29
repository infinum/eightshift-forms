<?php
/**
 * The file that defines the main start class.
 *
 * A class definition that includes attributes and functions used across both the
 * theme-facing side of the site and the admin area.
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

use Eightshift_Libs\Core\Main as Lib_Core;
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
class Main extends Lib_Core {

  /**
   * Set this to true if you need dependency injection in tests.
   *
   * @var boolean
   */
  private $is_test = false;

  /**
   * Default main action hook that start the whole lib. If you are using this lib in a plugin please change it to plugins_loaded.
   */
  public function get_default_register_action_hook() : string {
    return 'plugins_loaded';
  }

  /**
   * Array of services classes used in production.
   *
   * @return array
   */
  protected function prod_service_classes(): array {
    return array(

      // Config.
      Config::class,

      // Manifest.
      Lib_Manifest\Manifest::class => array( Config::class ),

      // I18n.
      Lib_I18n\I18n::class => array( Config::class ),

      // Authorization.
      Integrations\Authorization\HMAC::class,

      // Admin.
      Admin\Users::class,

      // Dynamics CRM.
      Integrations\Core\Guzzle_Client::class => array(
        Client::class,
      ),
      Integrations\OAuth2_Client::class => array(
        Integrations\Core\Guzzle_Client::class,
      ),
      Integrations\Dynamics_CRM::class => array(
        Integrations\OAuth2_Client::class,
      ),
      Rest\Dynamics_Crm_Route::class => array(
        Config::class,
        Integrations\Dynamics_CRM::class,
        Captcha\Basic_Captcha::class,
      ),
      Rest\Dynamics_Crm_Fetch_Entity_Route::class => array(
        Config::class,
        Integrations\Dynamics_CRM::class,
        Integrations\Authorization\HMAC::class,
        Cache\Transient_Cache::class,
      ),

      // Buckaroo.
      Integrations\Buckaroo\Buckaroo::class => array(
        Integrations\Core\Guzzle_Client::class,
      ),
      Rest\Buckaroo_Response_Handler_Route::class => array(
        Config::class,
        Integrations\Buckaroo\Buckaroo::class,
        Integrations\Authorization\HMAC::class,
      ),
      Rest\Buckaroo_Ideal_Route::class => array(
        Config::class,
        Integrations\Buckaroo\Buckaroo::class,
        Rest\Buckaroo_Response_Handler_Route::class,
        Integrations\Authorization\HMAC::class,
        Captcha\Basic_Captcha::class,
      ),
      Rest\Buckaroo_Emandate_Route::class => array(
        Config::class,
        Integrations\Buckaroo\Buckaroo::class,
        Rest\Buckaroo_Response_Handler_Route::class,
        Integrations\Authorization\HMAC::class,
        Captcha\Basic_Captcha::class,
      ),

      // Mailchimp.
      Integrations\Mailchimp\Mailchimp::class => array(
        Integrations\Mailchimp\Mailchimp_Marketing_Client::class,
      ),
      Rest\Mailchimp_Route::class => array(
        Config::class,
        Integrations\Mailchimp\Mailchimp::class,
        Captcha\Basic_Captcha::class,
      ),
      Rest\Mailchimp_Fetch_Segments_Route::class => array(
        Config::class,
        Integrations\Mailchimp\Mailchimp::class,
        Captcha\Basic_Captcha::class,
      ),

      // Email.
      Rest\Send_Email_Route::class => array(
        Config::class,
        Captcha\Basic_Captcha::class,
      ),

      // Enqueue.
      Localization_Constants::class => array(
        Lib_Manifest\Manifest::class,
        Rest\Dynamics_Crm_Route::class,
        Rest\Buckaroo_Ideal_Route::class,
        Rest\Buckaroo_Emandate_Route::class,
        Rest\Send_Email_Route::class,
        Rest\Mailchimp_Route::class,
        Integrations\Mailchimp\Mailchimp::class,
      ),
      Enqueue\Enqueue_Theme::class => array(
        Lib_Manifest\Manifest::class,
        Enqueue\Localization_Constants::class,
      ),
      Enqueue\Enqueue_Blocks::class => array(
        Lib_Manifest\Manifest::class,
        Enqueue\Localization_Constants::class,
      ),

      // Admin.
      Admin\Forms::class,
      Admin\Content::class,

      // Blocks.
      Blocks\Blocks::class => array( Config::class ),

      // View.
      View\Post_View_Filter::class,
    );
  }

  /**
   * Array of service classes used in tests.
   *
   * @return array
   */
  protected function test_service_classes(): array {
    return [
      // Config.
      Config::class,

      // Authorization.
      Integrations\Authorization\HMAC::class,

      // Integrations.
      Integrations\Mailchimp\Mailchimp::class => array(
        Mocks\MockMailchimpMarketingClient::class,
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
    ];
  }

  /**
   * Get the list of services to register.
   *
   * A list of classes which contain hooks.
   *
   * @return array<string> Array of fully qualified class names.
   */
  protected function get_service_classes() : array {
    return $this->is_test ? $this->test_service_classes() : $this->prod_service_classes();
  }

  /**
   * Provides additional / different services depending on if we're in test or not.
   *
   * @param  boolean $is_test Set to true if running tests.
   * @return void
   */
  public function set_test( bool $is_test ): void {
    $this->is_test = $is_test;
  }
}
