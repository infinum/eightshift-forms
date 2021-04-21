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
    $result = Forms::maybeOverrideValueFromQueryString( 'not-expected', 'test' );
    $this->assertEquals('expected', $result);
  }

  public function testNotOverridingValueFromQueryStringBecauseItsNotFound()
  {
    $_GET['field-test'] = 'not-expected';
    $result = Forms::maybeOverrideValueFromQueryString( 'expected', 'non-existent-key' );
    $this->assertEquals('expected', $result);
  }

  public function testDetectUsedFormTypesSingle()
  {
    $usedTypes = Forms::detectUsedTypes(
      false,
      'mailchimp',
      [],
      []
    );

    $this->assertArrayHasKey('mailchimp', $usedTypes);

    $usedTypes_2 = Forms::detectUsedTypes(
      false,
      'mailchimp',
      [ 'some-complex-type', 'another'],
      [ 'redirect-type']
    );

    $this->assertArrayHasKey('mailchimp', $usedTypes_2);
  }

  public function testDetectUsedFormTypesComplex()
  {
    $usedTypes = Forms::detectUsedTypes(
      true,
      '',
      [ 'some-complex-type', 'another'],
      [ 'redirect-type']
    );

    $this->assertArrayHasKey('some-complex-type', $usedTypes);
    $this->assertArrayHasKey('another', $usedTypes);
    $this->assertArrayHasKey('redirect-type', $usedTypes);

    $usedTypes_2 = Forms::detectUsedTypes(
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
    $blocksWithTheme = Forms::recursivelyChangeThemeForAllBlocks($this->DataProvider->parsedBlocksMock(), $theme);

    $this->checkThemeInBlocks($blocksWithTheme, $theme);
  }

  private function checkThemeInBlocks(array $blocks, string $theme) {
    foreach( $blocks as $blockWithTheme ) {
      $this->assertArrayHasKey('attrs', $blockWithTheme );
      $this->assertArrayHasKey('theme', $blockWithTheme['attrs'] );
      $this->assertEquals($theme, $blockWithTheme['attrs']['theme']);

      if ( ! empty( $blockWithTheme['innerBlocks'] ) ) {
        $this->checkThemeInBlocks($blockWithTheme['innerBlocks'], $theme);
      }
    }
  }
}