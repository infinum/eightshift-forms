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
use \MailchimpMarketing\ApiClient as MarketingApiClient;
/**
 * Mailchimp integration class.
 */
class Mailchimp {

  /**
   * Mailchimp Marketing Api client.
   *
   * @var ApiClient
   */
  private $client;

  /**
   * Constructs object
   *
   * @param Http_Client $guzzle_client Guzzle client.
   */
  public function __construct( Http_Client $guzzle_client ) {
    $this->guzzle_client = $guzzle_client;
  }

  /**
   * Adds or updates a member in Mailchimp.
   *
   * @param  string $list_id      Audience list ID.
   * @param  string $email        Contact's email.
   * @param  array  $merge_fields List of merge fields to add to request.
   * @param  array  $params       (Optional) list of params from request.
   * @param  string $status       (Optional) Member's status (if new).
   * @return mixed
   */
  public function add_or_update_member( string $list_id, string $email, array $merge_fields, array $params = [], string $status = 'pending' ) {
    $this->maybe_build_client();

    $params['email_address'] = $email;
    $params['status_if_new'] = $status;
    $params['merge_fields']  = $merge_fields;

    $response = $this->client->lists->setListMember( $list_id, $this->calculate_subscriber_hash( $email ), $params );
    return $response;
  }

  /**
   * Add a tag to a member.
   *
   * @param  string $list_id   Audience list ID.
   * @param  string $email     Contact's email.
   * @param  array  $tag_names Just a 1d array of tag names. Other processing is done inside.
   * @return bool
   */
  public function add_member_tags( string $list_id, string $email, array $tag_names ): bool {
    $this->maybe_build_client();

    // This call requires a very specific format for tags.
    $tags_request = [
      'tags' => array_map(function( $tag_name ) {
          return [
            'name' => $tag_name,
            'status' => 'active',
          ];
      }, $tag_names),
    ];

    $update_response = $this->client->lists->updateListMemberTags( $list_id, md5( $email ), $tags_request );

    // This call is weird in that an empty (204) response means success. If something went very wrong it
    // will throw an exception. If something is slightly off (such as not having the correct format for
    // tags array), it will also return an empty response.
    return ! $update_response ? true : false;
  }

  /**
   * List member tags.
   *
   * @param  string $list_id Audience list ID.
   * @param  string $email   Contact's email.
   * @return mixed
   */
  public function list_member_tags( string $list_id, string $email ) {
    $this->maybe_build_client();
    $response = $this->client->lists->getListMemberTags( $list_id, $this->calculate_subscriber_hash( $email ) );
    return $response;
  }

  /**
   * List a member
   *
   * @param  string $list_id Audience list ID.
   * @param  string $email   Contact's email.
   * @return mixed
   */
  public function get_list_member( string $list_id, string $email ) {
    $this->maybe_build_client();
    $response = $this->client->lists->getListMember( $list_id, $this->calculate_subscriber_hash( $email ) );
    return $response;
  }

  /**
   * Get information about all lists in the account.
   *
   * @return mixed
   *
   * @throws \Exception When response is invalid.
   */
  public function get_all_lists() {
    $this->maybe_build_client();
    $response = $this->client->lists->getAllLists();

    if ( ! isset( $response, $response->lists ) && ! is_array( $response->lists ) ) {
      throw new \Exception( 'Lists response invalid' );
    }

    foreach ( $response->lists as $list_obj ) {

      if ( ! is_object( $list_obj ) || ! isset( $list_obj->id, $list_obj->name ) ) {
        throw new \Exception( 'Lists response invalid' );
      }
    }

    return $response;
  }

  /**
   * Get information about all tags & segments in the account.
   *
   * @param  string $list_id Audience list ID.
   * @return mixed
   *
   * @throws \Exception When response is invalid.
   */
  public function get_all_segments( string $list_id ) {
    $this->maybe_build_client();
    $response = $this->client->lists->listSegments( $list_id );

    if ( ! isset( $response, $response->segments ) && ! is_array( $response->segments ) ) {
      throw new \Exception( 'Segments response invalid' );
    }

    foreach ( $response->segments as $segment_obj ) {

      if ( ! is_object( $segment_obj ) || ! isset( $segment_obj->id, $segment_obj->name, $segment_obj->type ) ) {
        throw new \Exception( 'Specific segment response invalid' );
      }
    }
    return $response;
  }

  /**
   * Builds Mailchimp API client
   *
   * @return void
   */
  private function maybe_build_client(): void {
    if ( empty( $this->client ) ) {
      $this->verify_mailchimp_info_exists();
      $client = new MarketingApiClient();
      $client->setConfig( [
        'apiKey' => \apply_filters( Filters::MAILCHIMP, 'api_key' ),
        'server' => \apply_filters( Filters::MAILCHIMP, 'server' ),
      ] );
      $this->client = $client;
    }
  }

  /**
   * Calculates the subscriber hash from email.
   *
   * @param  string $email Contact's email.
   * @return string
   */
  private function calculate_subscriber_hash( string $email ): string {
    return md5( $email );
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
