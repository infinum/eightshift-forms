<?php

namespace EightshiftFormsTests\Helpers;

use EightshiftForms\Helpers\Forms;
use EightshiftFormsTests\BaseTest;

class FormsTest extends BaseTest
{

  protected function _inject(HelpersDataProvider $HelpersDataProvider)
  {
    $this->DataProvider = $HelpersDataProvider;
  }

  public function testOverridingValueFromQueryString()
  {
    $_GET['field-test'] = 'expected';
    $result = Forms::maybe_override_value_from_query_string( 'not-expected', 'test' );
    $this->assertEquals('expected', $result);
  }

  public function testNotOverridingValueFromQueryStringBecauseItsNotFound()
  {
    $_GET['field-test'] = 'not-expected';
    $result = Forms::maybe_override_value_from_query_string( 'expected', 'non-existent-key' );
    $this->assertEquals('expected', $result);
  }

  public function testDetectUsedFormTypesSingle()
  {
    $used_types = Forms::detect_used_types(
      false,
      'mailchimp',
      [],
      []
    );

    $this->assertArrayHasKey('mailchimp', $used_types);

    $used_types_2 = Forms::detect_used_types(
      false,
      'mailchimp',
      [ 'some-complex-type', 'another'],
      [ 'redirect-type']
    );

    $this->assertArrayHasKey('mailchimp', $used_types_2);
  }

  public function testDetectUsedFormTypesComplex()
  {
    $used_types = Forms::detect_used_types(
      true,
      '',
      [ 'some-complex-type', 'another'],
      [ 'redirect-type']
    );

    $this->assertArrayHasKey('some-complex-type', $used_types);
    $this->assertArrayHasKey('another', $used_types);
    $this->assertArrayHasKey('redirect-type', $used_types);

    $used_types_2 = Forms::detect_used_types(
      true,
      'mailchimp',
      [ 'some-complex-type', 'another'],
      [ 'redirect-type']
    );

    $this->assertArrayHasKey('some-complex-type', $used_types_2);
    $this->assertArrayHasKey('another', $used_types_2);
    $this->assertArrayHasKey('redirect-type', $used_types_2);
  }

  public function testAddingThemeToAllBlocks() {
    $theme = 'test-theme-name';
    $blocks_with_theme = Forms::recursively_change_theme_for_all_blocks($this->DataProvider->parsedBlocksMock(), $theme);

    $this->checkThemeInBlocks($blocks_with_theme, $theme);
  }

  private function checkThemeInBlocks(array $blocks, string $theme) {
    foreach( $blocks as $block_with_theme ) {
      $this->assertArrayHasKey('attrs', $block_with_theme );
      $this->assertArrayHasKey('theme', $block_with_theme['attrs'] );
      $this->assertEquals($theme, $block_with_theme['attrs']['theme']);

      if ( ! empty( $block_with_theme['innerBlocks'] ) ) {
        $this->checkThemeInBlocks($block_with_theme['innerBlocks'], $theme);
      }
    }
  }
}