<?php

namespace EightshiftFormsTests\Integrations\Mailerlite;

use Eightshift_Forms\Core\Main;
use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Integrations\Mailerlite\Mailerlite;
use EightshiftFormsTests\BaseTest;
use \GuzzleHttp\Exception\ClientException;

class MailerliteTest extends BaseTest
{

  protected function _inject(DataProvider $dataProvider, Main $main)
  {
    $this->dataProvider = $dataProvider;
    $main->set_test(true);
    $this->di_container = $main->build_di_container();
    $this->mailerlite = $this->di_container->get( Mailerlite::class);
  }

  public function testAddOrUpdateSubscriber()
  {
    $this->addHooks();
    $params = [
      'listId' => 'list-id',
      'email' => DataProvider::MOCK_EMAIL,
      'mergeFields' => [
        'FNAME' => 'some name',
      ],
    ];
    $groupId = 'group-id';
    $email = DataProvider::MOCK_EMAIL;
    $subscriber_data = [
      'name' => 'some name',
    ];

    $response = $this->mailerlite->add_subscriber(
      $groupId,
      $email,
      $subscriber_data,
      []
    );

    $this->assertEquals($response, $this->dataProvider->getMockAddOrUpdateMemberResponse( $params ));
  }

  // public function testAddOrUpdateMemberIfMissingGroupId()
  // {
  //   $this->addHooks();
  //   $groupId = DataProvider::INVALID_GROUP_ID;
  //   $email = DataProvider::MOCK_EMAIL;
  //   $subscriber_data = [
  //     'name' => 'some name',
  //   ];

  //   try {
  //     $this->mailerlite->add_subscriber(
  //       $groupId,
  //       $email,
  //       $subscriber_data,
  //       []
  //     );

  //     $this->assertEquals(1,0);
  //   } catch(ClientException $e) {
  //     $this->assertIsObject($e);
  //   }
  // }

  /**
   * Mocking that a certain filter exists. See documentation of Brain Monkey:
   * https://brain-wp.github.io/BrainMonkey/docs/wordpress-hooks-added.html
   *
   * We can't return any actual value, we can just "mock register" this filter.
   *
   * @return void
   */
  protected function addHooks() {
    add_filter( Filters::MAILERLITE, function($key) {
      return $key;
    }, 1, 1);
  }
}
