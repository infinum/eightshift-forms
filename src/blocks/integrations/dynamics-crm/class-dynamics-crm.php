<?php
/**
 * Blocks class used to define configurations for blocks.
 *
 * @package Eightshift_Forms\Blocks
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations;

use Eightshift_Forms\Integrations\OAuth2_Client;
use SaintSystems\OData\ODataClient;

use \GuzzleHttp\Exception\ClientException;

/**
 * OAuth class which handles access token connections.
 */
class Dynamics_CRM {

  const ACCESS_TOKEN_KEY = 'dynamics-crm-access-token';

  /**
   * Constructs object
   *
   */
  public function __construct(OAuth2_Client $oauth2_client) {
    $this->oauth2_client = $oauth2_client;
  }

  /**
   * Injects a record into CRM
   *
   * @param  string $entity Entity to which we're adding records
   * @param  array  $data   Data representing a record.
   * @return bool
   *
   * @throws Exception When adding a new record fails.
   */
  public function add_record(string $entity, array $data) {
    $odata_client = $this->build_odata_client($this->get_token());

    try {
      $odata_client->from( $entity )->post( $data );
    } catch(ClientException $e) {

      // 401 exception should indicate access token was invalid, in this case let's try again with a fresh token. If it's not that,
      // just throw because we don't know how to handle it.
      if ($e->getCode() === 401) {
        $odata_client = $this->build_odata_client($this->get_token(true));
        $odata_client->from( $entity )->post( $data );
      } else {
        throw $e;
      }
    }

    return true;
  }

  /**
   * Set OAuth credentials, used when we can't inject it in DI.
   *
   * @param  array $credentials Credentials array.
   * @return bool
   */
  public function set_oauth_credentials( array $credentials ): bool {
    $this->oauth2_client->set_credentials($credentials);
    $this->odata_service_url = $credentials['api_url'];
    return true;
  }

  /**
   * Builds the odata client used for interacting with the CRM
   *
   * @param  string $access_token OAuth access token for this request.
   * @return object
   */
  protected function build_odata_client(string $access_token): object {
    return new ODataClient( $this->odata_service_url, function( $request ) use ($access_token) {

      // OAuth Bearer Token Authentication.
      $request->headers['Authorization'] = 'Bearer ' . $access_token;
    });
  }

  /**
   * Fetch / get the Dynamics CRM access token.
   *
   * @param  bool $should_fetch_new (Optional) pass if you want to force OAuth2 client to fetch new access token.
   * @return string
   */
  protected function get_token($should_fetch_new = false): string {
    return $this->oauth2_client->get_token( self::ACCESS_TOKEN_KEY, $should_fetch_new );
  }
}
