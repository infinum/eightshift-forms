<?php

/**
 * Dynamics CRM integration class.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

use EightshiftForms\Integrations\OAuth2ClientInterface;
use EightshiftFormsVendor\SaintSystems\OData\ODataClient;
use EightshiftFormsVendor\GuzzleHttp\Exception\ClientException;
use EightshiftFormsVendor\SaintSystems\OData\IODataClient;
use EightshiftFormsVendor\Illuminate\Support\Collection;

/**
 * Dynamics CRM integration class.
 */
class DynamicsCrm
{

	const ACCESS_TOKEN_KEY = 'dynamics-crm-access-token';

  /**
   * OAuth2 client implementation.
   *
   * @var OAuth2ClientInterface
   */
	private $oAuth2Client;

  /**
   * Odata service url.
   *
   * @var string
   */
	private $odataServiceUrl;

  /**
   * Constructs object
   *
   * @param OAuth2ClientInterface $oAuth2Client OAuth2 client implementation.
   */
	public function __construct(OAuth2ClientInterface $oAuth2Client)
	{
		$this->oAuth2Client = $oAuth2Client;
	}

  /**
   * Injects a record into CRM.
   *
   * @param  string $entity Entity to which we're adding records.
   * @param  array  $data   Data representing a record.
   * @return bool
   *
   * @throws ClientException When adding a record fails BUT it's not because of an invalid token (which we know how to handle).
   */
	public function add_record(string $entity, array $data)
	{
		$odata_client = $this->build_odata_client($this->getToken());

		try {
			$odata_client->from($entity)->post($data);
		} catch (ClientException $e) {
		  // 401 exception should indicate access token was invalid, in this case let's try again with a fresh token. If it's not that,
		  // just throw because we don't know how to handle it.
			if ($e->getCode() === 401) {
				$odata_client = $this->build_odata_client($this->getToken(true));
				$odata_client->from($entity)->post($data);
			} else {
				throw $e;
			}
		}

		return true;
	}

  /**
   * Reads all records for a single entity from CRM.
   *
   * @param  string $entity Entity to which we're adding records.
   * @param  array  $data   Optional data / params we're using while fetching. If this is empty all records in an entity will be returned.
   * @return Collection
   *
   * @throws ClientException When adding a record fails BUT it's not because of an invalid token (which we know how to handle).
   */
	public function fetch_all_from_entity(string $entity, array $data = [])
	{
		$odata_client = $this->build_odata_client($this->getToken());

		try {
			$response = $odata_client->from($entity)->get($data);
		} catch (ClientException $e) {
		  // 401 exception should indicate access token was invalid, in this case let's try again with a fresh token. If it's not that,
		  // just throw because we don't know how to handle it.
			if ($e->getCode() === 401) {
				$odata_client = $this->build_odata_client($this->getToken(true));
				$response     = $odata_client->from($entity)->get($data);
			} else {
				throw $e;
			}
		}

		return $response;
	}

  /**
   * Set OAuth credentials, used when we can't inject it in DI.
   *
   * @param  array $credentials Credentials array.
   * @return void
   */
	public function set_oauth_credentials(array $credentials): void
	{
		$this->oAuth2Client->setCredentials($credentials);
		$this->odataServiceUrl = $credentials['api_url'];
	}

  /**
   * Builds the odata client used for interacting with the CRM
   *
   * @param  string $access_token OAuth access token for this request.
   * @return IODataClient
   */
	private function build_odata_client(string $access_token): IODataClient
	{
		return new ODataClient(
			$this->odataServiceUrl,
			function ($request) use ($access_token) {

				// OAuth Bearer Token Authentication.
				$request->headers['Authorization'] = 'Bearer ' . $access_token;
			}
		);
	}

  /**
   * Fetch / get the Dynamics CRM access token.
   *
   * @param  bool $should_fetch_new (Optional) pass if you want to force OAuth2 client to fetch new access token.
   * @return string
   */
	private function getToken($should_fetch_new = false): string
	{
		return $this->oAuth2Client->getToken(self::ACCESS_TOKEN_KEY, $should_fetch_new);
	}
}
