<?php

namespace EightshiftFormsTests\Integrations\Mailchimp;

use Eightshift_Forms\Core\Main;
use Eightshift_Forms\Integrations\Mailchimp\Mailchimp;
use EightshiftFormsTests\BaseTest;
use \GuzzleHttp\Exception\ClientException;

class MailchimpTest extends BaseTest
{

  protected function _inject(DataProvider $dataProvider, Main $main)
  {
    $this->dataProvider = $dataProvider;
    $main->set_test(true);
    $this->di_container = $main->build_di_container();
    $this->mailchimp = $this->di_container->get( Mailchimp::class);
  }

  public function testAddOrUpdateMember()
  {
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
}