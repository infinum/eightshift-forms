<?php

/**
 * Mailchimp integration class.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Cache\Cache;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Integrations\ClientInterface;
use MailchimpMarketing\ApiClient;

/**
 * Mailchimp integration class.
 */
class Mailchimp
{

  /**
   * Lists transient name
   *
   * @var string
   */
	public const CACHE_LISTS = 'eightshift-forms-mailchimp-lists';

  /**
   * Lists transient expiration time.
   *
   * @var int
   */
	public const CACHE_LIST_TIMEOUT = 60 * 15; // 15 min

  /**
   * Mailchimp Marketing Api client.
   *
   * @var ApiClient
   */
	private $client;

  /**
   * Our own implementation of Mailchimp Marketing Client.
   *
   * @var ClientInterface
   */
	private $mailchimpMarketingClient;

  /**
   * Cache object used for caching Mailchimp responses.
   *
   * @var Cache
   */
	private $cache;

  /**
   * Constructs object
   *
   * @param ClientInterface $mailchimpMarketingClient Mailchimp marketing client.
   * @param Cache           $transientCache            Transient cache object.
   */
	public function __construct(ClientInterface $mailchimpMarketingClient, Cache $transientCache)
	{
		$this->mailchimpMarketingClient = $mailchimpMarketingClient;
		$this->cache = $transientCache;
	}

  /**
   * Adds or updates a member in Mailchimp.
   *
   * @param  string $listId      Audience list ID.
   * @param  string $email        Contact's email.
   * @param  array  $mergeFields List of merge fields to add to request.
   * @param  array  $params       (Optional) list of params from request.
   * @param  string $status       (Optional) Member's status (if new).
   * @return mixed
   *
   * @throws \Exception When response is invalid.
   */
	public function addOrUpdateMember(string $listId, string $email, array $mergeFields, array $params = [], string $status = 'pending')
	{
		$this->setupClientConfigAndVerify();

		$params['email_address'] = $email;
		$params['status_if_new'] = $status;
		$params['merge_fields']  = $mergeFields;

		$response = $this->client->lists->setListMember($listId, $this->calculateSubscriberHash($email), $params);

		if (! is_object($response) || ! isset($response->id, $response->emailAddress)) {
			throw new \Exception('setListMember response invalid');
		}

		return $response;
	}

  /**
   * Adds a member in Mailchimp.
   *
   * @param  string $listId      Audience list ID.
   * @param  string $email        Contact's email.
   * @param  array  $mergeFields List of merge fields to add to request.
   * @param  array  $params       (Optional) list of params from request.
   * @param  string $status       (Optional) Member's status (if new).
   * @return mixed
   *
   * @throws \Exception When response is invalid.
   */
	public function addMember(string $listId, string $email, array $mergeFields, array $params = [], string $status = 'pending')
	{
		$this->setupClientConfigAndVerify();

		$params['email_address'] = $email;
		$params['status']        = $status;
		$params['merge_fields']  = $mergeFields;

		$response = $this->client->lists->addListMember($listId, $params);

		if (! is_object($response) || ! isset($response->id, $response->emailAddress)) {
			throw new \Exception('setListMember response invalid');
		}

		return $response;
	}

  /**
   * Add a tag to a member.
   *
   * @param  string $listId   Audience list ID.
   * @param  string $email     Contact's email.
   * @param  array  $tagNames Just a 1d array of tag names. Other processing is done inside.
   * @return bool
   *
   * @throws \Exception When response is invalid.
   */
	public function addMemberTags(string $listId, string $email, array $tagNames): bool
	{
		$this->setupClientConfigAndVerify();

	  // This call requires a very specific format for tags.
		$tagsRequest = [
		'tags' => array_map(function ($tagName) {
			return [
				'name' => $tagName,
				'status' => 'active',
			];
		}, $tagNames),
		];

		$updateResponse = $this->client->lists->updateListMemberTags($listId, md5($email), $tagsRequest);

	  // This call is weird in that an empty (204) response means success. If something went very wrong it
	  // will throw an exception. If something is slightly off (such as not having the correct format for
	  // tags array), it will also return an empty response.
		return ! empty($updateResponse);
	}

  /**
   * List a member
   *
   * @param  string $listId Audience list ID.
   * @param  string $email   Contact's email.
   * @return mixed
   */
	public function getListMember(string $listId, string $email)
	{
		$this->setupClientConfigAndVerify();
		return $this->client->lists->getListMember($listId, $this->calculateSubscriberHash($email));
	}

  /**
   * Get information about all lists in the account.
   *
   * @param bool $isFresh Set to true if you want to fetch the lists regardless if we already have them cached.
   * @return mixed
   *
   * @throws \Exception When response is invalid.
   */
	public function getAllLists(bool $isFresh = false)
	{

		$cachedResponse = $this->cache->get(self::CACHE_LISTS);

		if ($isFresh || empty($cachedResponse)) {
			$this->setupClientConfigAndVerify();
			$response = $this->client->lists->getAllLists();

			if (! isset($response, $response->lists) && ! is_array($response->lists)) {
				throw new \Exception('Lists response invalid');
			}

			foreach ($response->lists as $listObj) {
				if (! is_object($listObj) || ! isset($listObj->id, $listObj->name)) {
					throw new \Exception('Lists response invalid');
				}
			}

			$this->cache->save(self::CACHE_LISTS, (string) wp_json_encode($response), self::CACHE_LIST_TIMEOUT);
		} else {
			$response = json_decode($cachedResponse);
		}

		return $response;
	}

  /**
   * Get information about all tags & segments in the account.
   *
   * @param  string $listId Audience list ID.
   * @return mixed
   *
   * @throws \Exception When response is invalid.
   */
	public function getAllSegments(string $listId)
	{
		$this->setupClientConfigAndVerify();
		$response = $this->client->lists->listSegments($listId);

		if (! isset($response, $response->segments) && ! is_array($response->segments)) {
			throw new \Exception('Segments response invalid');
		}

		foreach ($response->segments as $segmentObj) {
			if (! isObject($segmentObj) || ! isset($segmentObj->id, $segmentObj->name, $segmentObj->type)) {
				throw new \Exception('Specific segment response invalid');
			}
		}
		return $response;
	}

  /**
   * Calculates the subscriber hash from email.
   *
   * @param  string $email Contact's email.
   * @return string
   */
	private function calculateSubscriberHash(string $email): string
	{
		return md5($email);
	}

  /**
   * Make sure we have the data we need defined as filters.
   *
   * @throws MissingFilterInfoException When not all required keys are set.
   *
   * @return void
   */
	private function setupClientConfigAndVerify(): void
	{
		if (! has_filter(Filters::MAILCHIMP)) {
			throw MissingFilterInfoException::viewException(Filters::MAILCHIMP, esc_html__('entire_filter', 'eightshift-forms'));
		}

		if (empty(\apply_filters(Filters::MAILCHIMP, 'apiKey'))) {
			throw MissingFilterInfoException::viewException(Filters::MAILCHIMP, 'apiKey');
		}

		if (empty(\apply_filters(Filters::MAILCHIMP, 'server'))) {
			throw MissingFilterInfoException::viewException(Filters::MAILCHIMP, 'server');
		}

		if (empty($this->client)) {
			$this->mailchimpMarketingClient->setConfig();
			$this->client = $this->mailchimpMarketingClient->getClient();
		}
	}
}
