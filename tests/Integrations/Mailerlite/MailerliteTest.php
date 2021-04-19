<?php

namespace EightshiftFormsTests\Integrations\Mailerlite;

use EightshiftForms\Main\Main;
use EightshiftForms\Hooks\Filters;
use Eightshift_Forms\Integrations\Mailerlite\Mailerlite;
use EightshiftFormsTests\BaseTest;

class MailerliteTest extends BaseTest
{

  protected function _inject(DataProvider $dataProvider, Main $main)
  {
    $this->dataProvider = $dataProvider;
    $main->setTest(true);
    $this->di_container = $main->buildDiContainer();
    $this->mailerlite = $this->di_container->get( Mailerlite::class );
  }

  public function testAddOrUpdateSubscriber()
  {
    $this->addHooks();
    $params = [
      'email' => DataProvider::MOCK_EMAIL,
    ];
    $groupId = 12345;
    $subscriber_data = [
      'name' => 'some name',
    ];

    $response = $this->mailerlite->add_subscriber(
      $groupId,
      DataProvider::MOCK_EMAIL,
      $subscriber_data,
      $params
    );

    $this->assertEquals($response, $this->dataProvider->getMockAddSubscriberResponse( $params ));
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
    add_filter( Filters::MAILERLITE, function($key) {
      return $key;
    }, 1, 1);
  }
}
