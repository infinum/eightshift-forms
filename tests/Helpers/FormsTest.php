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
    $usedTypes = Forms::detect_used_types(
      false,
      'mailchimp',
      [],
      []
    );

    $this->assertArrayHasKey('mailchimp', $usedTypes);

    $usedTypes_2 = Forms::detect_used_types(
      false,
      'mailchimp',
      [ 'some-complex-type', 'another'],
      [ 'redirect-type']
    );

    $this->assertArrayHasKey('mailchimp', $usedTypes_2);
  }

  public function testDetectUsedFormTypesComplex()
  {
    $usedTypes = Forms::detect_used_types(
      true,
      '',
      [ 'some-complex-type', 'another'],
      [ 'redirect-type']
    );

    $this->assertArrayHasKey('some-complex-type', $usedTypes);
    $this->assertArrayHasKey('another', $usedTypes);
    $this->assertArrayHasKey('redirect-type', $usedTypes);

    $usedTypes_2 = Forms::detect_used_types(
      true,
      'mailchimp',
      [ 'some-complex-type', 'another'],
      [ 'redirect-type']
    );

    $this->assertArrayHasKey('some-complex-type', $usedTypes_2);
    $this->assertArrayHasKey('another', $usedTypes_2);
    $this->assertArrayHasKey('redirect-type', $usedTypes_2);
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