<?php
/**
 * Blocks class used to define configurations for blocks.
 *
 * @package Eightshift_Forms\Blocks
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations;

/**
 * OAuth2_Client class which handles access token connections.
 */
class OAuth2_Client {

  const HOUR_IN_SECONDS = '3600';

  /**
   * Constructs object
   *
   * @param string $url           Url to authenticate against.
   * @param string $client_id     Client ID, used for getting access token.
   * @param string $client_secret Client secret, used for getting access token.
   * @param string $scope         Scope for which to request access token.
   */
  public function __construct(string $url, string $client_id, string $client_secret, string $scope) {
    $this->url = $url;
    $this->client_id = $client_id;
    $this->client_secret = $client_secret;
    $this->scope = $scope;

    error_log(print_r(compact('url', 'client_secret', 'scope', 'client_id'), true));
  }

  /**
   * Returns the access token, either from cache or fetches a new one.
   *
   * @param  string $token_key        Token's transient key.
   * @param  bool   $should_fetch_new Pass true to skip fetching content for transient. Useful for when you want to make sure your access token nis fresh.
   * @return string
   */
  public function get_token(string $token_key, bool $should_fetch_new = false): string {
    if (!$should_fetch_new) {
      $token = $this->get_token_from_cache($token_key);
    }

    if ($should_fetch_new || empty( $token )) {
      $token = $this->fetch_token( $this->url, $this->client_id, $this->client_secret, $this->scope );
    }

    return $token;
  }

  /**
   * Fetches token from provider using Client Credentials method
   * https://oauth.net/2/grant-types/client-credentials/
   *
   * @param string $url           Url to authenticate against.
   * @param string $client_id     Client ID, used for getting access token.
   * @param string $client_secret Client secret, used for getting access token.
   * @param string $scope         Scope for which to request access token.
   * @return string
   */
  protected function fetch_token(string $url, string $client_id, string $client_secret, string $scope) {
    $body = [
      'grant_type' => 'client_credentials',
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'scope' => $scope
    ];
    
    $client = new \GuzzleHttp\Client();
    $response = $client->get($url, [
      'form_params' => $body,
    ]);

    if ($response->getStatusCode() !== 200) {
      throw new \Exception('Something went wrong, status code: ' . $response->getStatusCode());
    }

    $json_body = json_decode((string) $response->getBody(), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \Exception('Invalid JSON in body');
    }

    if (!isset($json_body['access_token'])) {
      throw new \Exception('Missing access token from response');
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
  protected function save_token_to_cache(string $token_key, string $token, int $expiration = self::HOUR_IN_SECONDS): bool {
    return set_transient( $token_key, $token, $expiration );
  }

  /**
   * Returns token from currently implemented cache.
   *
   * @param  string $token_key Token's transient key.
   * @return string|bool
   */
  protected function get_token_from_cache(string $token_key) {
    return get_transient( $token_key );
  }
}
