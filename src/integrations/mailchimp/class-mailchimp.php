<?php
/**
 * Mailchimp integration class.
 *
 * @package Eightshift_Forms\Integrations
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Mailchimp;

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Integrations\Core\Http_Client;
use Eightshift_Forms\Exception\Missing_Filter_Info_Exception;
use \MailchimpMarketing\ApiClient;

/**
 * Mailchimp integration class.
 */
class Mailchimp {

  /**
   * Constructs object
   *
   * @param Http_Client $guzzle_client Guzzle client.
   */
  public function __construct( Http_Client $guzzle_client ) {
    $this->guzzle_client = $guzzle_client;
  }

  public function add_or_update_record( string $email, string $list_id, array $params ) {
    error_log( 'Added or updated record' );
    $this->build_client();

    $this->add_or_update_member( $email, $list_id );
    error_log(print_r($mailchimp->lists, true));
    $response = $mailchimp->ping->get();
    return $response;
  }

  /**
   * Builds Mailchimp API client
   *
   * @return void
   */
  private function build_client(): void {
    $mailchimp = new ApiClient();
    $mailchimp->setConfig( $this->get_config() );
    $this->client = $mailchimp;
  }

  /**
   * Add or update member to Mailchimp.
   *
   * @param string $email
   * @param string $list_id
   * @return boolean
   */
  private function add_or_update_member( string $email, string $list_id ): bool {
    return true;
  }

  /**
   * Reads API connection details from filters.
   *
   * @return array
   */
  private function get_config(): array {
    $this->verify_mailchimp_info_exists();

    return [
      'apiKey' => \apply_filters( Filters::MAILCHIMP, 'api_key' ),
      'server' => \apply_filters( Filters::MAILCHIMP, 'server' ),
    ];
  }

  /**
   * Make sure we have the data we need defined as filters.
   *
   * @throws \Missing_Filter_Info_Exception When not all required keys are set.
   *
   * @return void
   */
  private function verify_mailchimp_info_exists(): void {
    if ( empty( \apply_filters( Filters::MAILCHIMP, 'api_key' ) ) ) {
      throw Missing_Filter_Info_Exception::view_exception( Filters::MAILCHIMP, 'api_key' );
    }

    if ( empty( \apply_filters( Filters::MAILCHIMP, 'server' ) ) ) {
      throw Missing_Filter_Info_Exception::view_exception( Filters::MAILCHIMP, 'server' );
    }
  }

}
