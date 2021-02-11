<?php
/**
 * Mailerlite integration class.
 *
 * @package Eightshift_Forms\Integrations
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Mailerlite;

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Exception\Missing_Filter_Info_Exception;
use \MailerLiteApi\MailerLite as MailerLiteClient;
/**
 * Mailerlite integration class.
 */
class Mailerlite {

  /**
   * Mailerlite Marketing Api client.
   *
   * @var MailerLiteClient
   */
  private $client;

  /**
   * Our own implementation of Mailerlite Marketing Client.
   *
   * @var Mailerlite_Client_Interface
   */
  private $mailerlite_client;

  /**
   * Constructs object
   *
   * @param Mailerlite_Client_Interface $mailerlite_client Mailerlite marketing client.
   */
  public function __construct( Mailerlite_Client_Interface $mailerlite_client ) {
    $this->mailerlite_client = $mailerlite_client;
  }

  /**
   * Get all email groups
   *
   * @return mixed
   */
  public function get_all_groups() {
    $this->setup_client_config_and_verify();

    return $this->client->groups()->get();
  }

  /**
   * Adds a subscriber in Mailerlite.
   *
   * @param  string $group_id        Group  ID.
   * @param  string $email           Contact's email.
   * @param  array  $subscriber_data List of merge fields to add to request.
   * @param  array  $params          Additional params
   * @return mixed
   *
   * @throws \Exception When response is invalid.
   */
  public function add_subscriber( string $group_id, string $email, array $subscriber_data, array $params = [] ) {
    $this->setup_client_config_and_verify();

    $subscriber_data['email'] = $email;

    $response = $this->client->groups()->addSubscriber( $group_id, $subscriber_data, $params );

    return $response;
  }

  /**
   * Make sure we have the data we need defined as filters.
   *
   * @throws Missing_Filter_Info_Exception When not all required keys are set.
   *
   * @return void
   */
  private function setup_client_config_and_verify(): void {
    if ( ! has_filter( Filters::MAILERLITE ) ) {
      throw Missing_Filter_Info_Exception::view_exception( Filters::MAILERLITE, esc_html__( 'entire_filter', 'eightshift-forms' ) );
    }

    if ( empty( \apply_filters( Filters::MAILERLITE, 'api_key' ) ) ) {
      throw Missing_Filter_Info_Exception::view_exception( Filters::MAILERLITE, 'api_key' );
    }

    if ( empty( $this->client ) ) {
      $this->mailerlite_client->set_config();
      $this->client = $this->mailerlite_client->get_client();
    }
  }

}
