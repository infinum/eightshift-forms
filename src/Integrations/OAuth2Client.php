<?php

/**
 * OAuth2Client class which handles access token connections.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

use EightshiftForms\Integrations\Core\HttpClientInterface;

/**
 * OAuth2Client class which handles access token connections.
 */
class OAuth2Client implements OAuth2ClientInterface
{

	/**
	 * Url to which we're submitting.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Oauth2 client id.
	 *
	 * @var string
	 */
	protected $clientId;

	/**
	 * Oauth2 client secret.
	 *
	 * @var string
	 */
	protected $clientSecret;

	/**
	 * Oauth2 scope.
	 *
	 * @var string
	 */
	protected $scope;

	/**
	 * HTTP client implementation obj which uses Guzzle.
	 *
	 * @var HttpClientInterface.
	 */
	private $httpClient;

	/**
	 * Constructs object
	 *
	 * @param HttpClientInterface $guzzleClient HTTP client implementation.
	 */
	public function __construct(HttpClientInterface $guzzleClient)
	{
		$this->httpClient = $guzzleClient;
	}

	/**
	 * Returns the access token, either from cache or fetches a new one.
	 *
	 * @param  string $tokenKey        Token's transient key.
	 * @param  bool   $shouldFetchNew Pass true to skip fetching content for transient. Useful for when you want to make sure your access token is fresh.
	 * @return string
	 */
	public function getToken(string $tokenKey, bool $shouldFetchNew = false): string
	{
		if (! $shouldFetchNew) {
			$token = (string) $this->getTokenFromCache($tokenKey);
		}

		if ($shouldFetchNew || empty($token)) {
			$token = $this->fetchToken($this->url, $this->clientId, $this->clientSecret, $this->scope);
		}

		return $token;
	}

	/**
	 * Set credentials, used when we can't set credentials during DI services building.
	 *
	 * @param  array $credentials OAuth2 credentials.
	 * @return void
	 */
	public function setCredentials(array $credentials): void
	{
		$this->url           = $credentials['url'];
		$this->clientId     = $credentials['clientId'];
		$this->clientSecret = $credentials['clientSecret'];
		$this->scope         = $credentials['scope'];
	}

	/**
	 * Fetches token from provider using Client Credentials method
	 * https://oauth.net/2/grant-types/client-credentials/
	 *
	 * @param  string $url           Url to authenticate against.
	 * @param  string $clientId     Client ID, used for getting access token.
	 * @param  string $clientSecret Client secret, used for getting access token.
	 * @param  string $scope         Scope for which to request access token.
	 * @return string
	 *
	 * @throws \Exception      When the response isn't as expected.
	 */
	protected function fetchToken(string $url, string $clientId, string $clientSecret, string $scope)
	{
		$body = [
			'grant_type' => 'client_credentials',
			'clientId' => $clientId,
			'clientSecret' => $clientSecret,
			'scope' => $scope,
		];

		$response = $this->httpClient->get(
			$url,
			[
				'form_params' => $body,
			]
		);

		$jsonBody = json_decode((string) $response->getBody(), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new \Exception('Invalid JSON in body');
		}

		if (! isset($jsonBody['access_token'])) {
			throw new \Exception('Missing access token from response');
		}

		return $jsonBody['access_token'];
	}

	/**
	 * Returns token from currently implemented cache.
	 *
	 * @param  string $tokenKey  Token's transient key.
	 * @param  string $token      Token's value.
	 * @param  int    $expiration Cache's expiration in seconds.
	 * @return bool
	 */
	protected function saveTokenToCache(string $tokenKey, string $token, int $expiration = HOUR_IN_SECONDS): bool
	{
		return set_transient($tokenKey, $token, $expiration);
	}

	/**
	 * Returns token from currently implemented cache.
	 *
	 * @param  string $tokenKey Token's transient key.
	 * @return string|bool
	 */
	protected function getTokenFromCache(string $tokenKey)
	{
		return get_transient($tokenKey);
	}
}
