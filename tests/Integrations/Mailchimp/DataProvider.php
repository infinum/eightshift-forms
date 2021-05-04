<?php

namespace EightshiftFormsTests\Integrations\Mailchimp;

use stdClass;

class DataProvider
{
	public const INVALID_LIST_ID = 'invalid-list-id';
	public const MOCK_EMAIL = 'someemail@infinum.com';
	public const MOCK_TAG_1 = 'aaa';
	public const MOCK_TAG_2 = 'bbb';

	public static function defaultMergeFields() {
    return [
      'FNAME' => '',
      'LNAME' => '',
      'ADDRESS' => '',
      'PHONE' => '',
      'BIRTHDAY' => '',
      'LDONDATE' => '',
      'DON' => '',
    ];
  }
	/**
	 * Example of successful transaction Buckaroo response.
	 *
	 * @return stdClass
	 */
	public static function getMockAddOrUpdateMemberResponse( array $params ): stdClass {
    $response = new \stdClass();
    $response->id = '5ae1cf23294c7b08b5ddd696a454635a';
    $response->emailAddress = $params['email'];
    $response->uniqueEmailId = '3cdee7fb82';
    $response->webId = '205197020';
    $response->emailType = 'html';
    $response->status = $params['status'] ?? 'pending';
    $response->mergeFields = new \stdClass();

    $allMergeFields = array_merge( self::defaultMergeFields(), $params['merge_fields'] );
    foreach( $allMergeFields as $key => $value ) {
      $response->mergeFields->$key = $value;
    }

    $response->stats = new \stdClass();
    $response->stats->avgOpenRate = 0;
    $response->stats->avgClickRate = 0;
    
    $response->ipSignup = '213.186.17.146';
    $response->timestampSignup = '2020-10-28T13:46:46+00:00';
    $response->ipOpt = '';
    $response->timestampOpt = '';
    $response->memberRating = 2;
    $response->lastChanged = '2020-10-28T13:46:46+00:00';
    $response->language = '';
    $response->vip = '';
    $response->emailClient = '';
    $response->location = new \stdClass();
    $response->location->latitude = new \stdClass();
    $response->location->latitude = 0;
    $response->location->longitude = 0;
    $response->location->gmtoff = 0;
    $response->location->dstoff = 0;
    $response->location->countryCode = '';
    $response->location->timezone = '';
    $response->source = 'API - Generic';
    $response->tagsCount = 2;
    $tag_1 = new stdClass();
    $tag_1->id = 280164;
    $tag_1->name = self::MOCK_TAG_1;
    $tag_2 = new stdClass();
    $tag_2->id = 280216;
    $tag_2->name = self::MOCK_TAG_2;
    $response->tags = [
      $tag_1,
      $tag_2
    ];

    $response->listId = $params['list_id'] ?? 'invalid-list-id';
    $response->_links = [
      (object) [
        'rel' => 'self',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Response.json',
      ],
      (object) [
        'rel' => 'self',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Response.json',
      ],
      (object) [
        'rel' => 'parent',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/CollectionResponse.json',
        'schema' => 'https://us5.api.mailchimp.com/schema/3.0/CollectionLinks/Lists/Members.json',
      ],
      (object) [
        'rel' => 'update',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'PATCH',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Response.json',
        'schema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/PATCH.json',
      ],
      (object) [
        'rel' => 'upsert',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'PUT',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Response.json',
        'schema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/PUT.json',
      ],
      (object) [
        'rel' => 'delete',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a',
        'method' => 'DELETE',
      ],
      (object) [
        'rel' => 'activity',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/activity',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Activity/Response.json',
      ],
      (object) [
        'rel' => 'goals',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/goals',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Goals/Response.json',
      ],
      (object) [
        'rel' => 'notes',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/notes',
        'method' => 'GET',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Notes/CollectionResponse.json',
      ],
      (object) [
        'rel' => 'events',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/events',
        'method' => 'POST',
        'targetSchema' => 'https://us5.api.mailchimp.com/schema/3.0/Definitions/Lists/Members/Events/POST.json',
      ],
      (object) [
        'rel' => 'delete_permanent',
        'href' => 'https://us5.api.mailchimp.com/3.0/lists/eb7fd0b84a/members/5ae1cf23294c7b08b5ddd696a454635a/actions/delete-permanent',
        'method' => 'POST',
      ],
    ];

    return $response;
  }
}