<?php

namespace EightshiftFormsTests\Integrations\Mailchimp;

use EightshiftForms\Main\Main;
use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Integrations\Mailchimp\Mailchimp;
use EightshiftFormsTests\BaseTest;
use \GuzzleHttp\Exception\ClientException;

class MailchimpTest extends BaseTest
{

  protected function _inject(DataProvider $dataProvider, Main $main)
  {
    $this->dataProvider = $dataProvider;
    $main->setTest(true);
    $this->di_container = $main->buildDiContainer();
    $this->mailchimp = $this->di_container->get( Mailchimp::class);
  }

  public function testAddOrUpdateMember()
  {
    $this->addHooks();
    $params = [
      'listId' => 'list-id',
      'email' => DataProvider::MOCK_EMAIL,
      'mergeFields' => [
        'FNAME' => 'some name',
      ],
    ];

    $response = $this->mailchimp->add_or_update_member(
      $params['listId'],
      $params['email'],
      $params['mergeFields'],
      []
    );

    $this->assertEquals($response, $this->dataProvider->getMockAddOrUpdateMemberResponse( $params ));
  }

  public function testAddOrUpdateMemberIfMissingListId()
  {
    $this->addHooks();
    $params = [
      'listId' => DataProvider::INVALID_LIST_ID,
      'email' => DataProvider::MOCK_EMAIL,
      'mergeFields' => [
        'FNAME' => 'some name',
      ],
    ];

    try {      
      $this->mailchimp->add_or_update_member(
        $params['listId'],
        $params['email'],
        $params['mergeFields'],
        []
      );

      $this->assertEquals(1,0);
    } catch(ClientException $e) {
      $this->assertIsObject($e);
    }
  }

  /**
   * Mocking that a certain filter exists. See documentation of Brain Monkey:
   * https://brain-wp.github.io/BrainMonkey/docs/wordpress-hooks-added.html
   *
   * We can't return any actual value, we can just "mock register" this filter.
   *
   * @return void
   */
  protected function addHooks() {
    add_filter( Filters::MAILCHIMP, function($key) {
      return $key;
    }, 1, 1);
  }
}