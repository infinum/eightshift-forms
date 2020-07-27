<?php
/**
 * OAuth2_Client class which handles access token connections.
 *
 * @package Eightshift_Forms\Integrations
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations;

use Eightshift_Forms\Integrations\Core\Http_Client;
use GuzzleHttp\Exception\ClientException;

/**
 * OAuth2_Client class which handles access token connections.
 */
class OAuth2_Client implements OAuth2_Client_Interface {

  const HOUR_IN_SECONDS = '3600';

  /**
   * DI injected Http_Client implementation.
   *
   * @var Http_Client
   */
  protected $http_client;

  /**
   * Constructs object.
   *
   * @param Http_Client $http_client DI injected Http_Client implementation.
   */
  public function __construct( Http_Client $http_client ) {
    $this->http_client = $http_client;
  }

  /**
   * Returns the access token, either from cache or fetches a new one.
   *
   * @param  string $token_key        Token's transient key.
   * @param  bool   $should_fetch_new Pass true to skip fetching content for transient. Useful for when you want to make sure your access token is fresh.
   * @return string
   */
  public function get_token( string $token_key, bool $should_fetch_new = false ): string {
    if ( ! $should_fetch_new ) {
      $token = $this->get_token_from_cache( $token_key );
    }

    if ( $should_fetch_new || empty( $token ) ) {
      $token = $this->fetch_token( $this->url, $this->client_id, $this->client_secret, $this->scope );
    }

    return $token;
  }

  /**
   * Set credentials, used when we can't set credentials during DI services building.
   *
   * @param  array $credentials OAuth2 credentials.
   * @return void
   */
  public function set_credentials( array $credentials ): void {
    $this->url           = $credentials['url'];
    $this->client_id     = $credentials['client_id'];
    $this->client_secret = $credentials['client_secret'];
    $this->scope         = $credentials['scope'];
  }

  /**
   * Fetches token from provider using Client Credentials method
   * https://oauth.net/2/grant-types/client-credentials/
   *
   * @param  string $url           Url to authenticate against.
   * @param  string $client_id     Client ID, used for getting access token.
   * @param  string $client_secret Client secret, used for getting access token.
   * @param  string $scope         Scope for which to request access token.
   * @return string
   *
   * @throws ClientException When the request fails.
   * @throws \Exception      When the response isn't as expected.
   */
  protected function fetch_token( string $url, string $client_id, string $client_secret, string $scope ) {
    $body = array(
      'grant_type' => 'client_credentials',
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'scope' => $scope,
    );

    $response = $this->http_client->get(
      $url,
      array(
        'form_params' => $body,
      )
    );

    $json_body = json_decode( (string) $response->getBody(), true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
      throw new \Exception( 'Invalid JSON in body' );
    }

    if ( ! isset( $json_body['access_token'] ) ) {
      throw new \Exception( 'Missing access token from response' );
    }

    return $json_body['access_token'];
  }

  /**
   * Returns token from currently implemented cache.
   *
   * @param  string $token_key  Token's transient key.
   * @param  string $token      Token's value.
   * @param  int    $expiration Cache's expiration in seconds.
   * @return bool
   */
  protected function save_token_to_cache( string $token_key, string $token, int $expiration = self::HOUR_IN_SECONDS ): bool {
    return set_transient( $token_key, $token, $expiration );
  }

  /**
   * Returns token from currently implemented cache.
   *
   * @param  string $token_key Token's transient key.
   * @return string|bool
   */
  protected function get_token_from_cache( string $token_key ) {
    return get_transient( $token_key );
  }
}
