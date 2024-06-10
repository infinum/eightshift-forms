<?php

namespace Tests\Unit\Integrations\Goodbits;

use Brain\Monkey;

use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;

use function Tests\setupMocks;

// class SettingsGoodbitsMock extends SettingsGoodbits {

// 	public function __construct(
// 		SettingsFallbackDataInterface $settingsFallback
// 	) {
// 		parent::__construct($settingsFallback);
// 	}
// };

// /**
//  * Mock before tests.
//  */
// beforeEach(function () {
// 	Monkey\setUp();
// 	setupMocks();

// 	$settingsFallback = new SettingsFallback();

// 	$this->goodbitsSettings = new SettingsGoodbitsMock($settingsFallback);
// });

// afterEach(function() {
// 	unset($this->goodbitsSettings);
// 	unset($goodbits);
// 	unset($validator);
// 	unset($labels);
// 	unset($goodbitsClient);

// 	Monkey\tearDown();
// });

// test('Register method will call sidebar hook', function () {
// 	$this->goodbitsSettings->register();

// 	$this->assertSame(10, \has_filter(SettingsGoodbitsMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Integrations\Goodbits\SettingsGoodbitsMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
// 	$this->assertSame(10, \has_filter(SettingsGoodbitsMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Integrations\Goodbits\SettingsGoodbitsMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
// 	$this->assertSame(10, \has_filter(SettingsGoodbitsMock::FILTER_SETTINGS_IS_VALID_NAME, 'Tests\Unit\Integrations\Goodbits\SettingsGoodbitsMock->isSettingsValid()'), 'The callback isSettingsValid should be hooked to custom filter hook with priority 10.');
// });

// test('isSettingsGlobalValid returns false when Goodbits is not used', function() {
// 	expect($this->goodbitsSettings->isSettingsGlobalValid())->toBe(false);
// });

// test('isSettingsGlobalValid returns true when Goodbits is configured correctly', function() {
// 	putenv('test_force_option_es-forms-goodbits-use-HR=goodbits-use');
// 	expect($this->goodbitsSettings->isSettingsGlobalValid())->toBe(true);
// 	putenv('test_force_option_es-forms-goodbits-use-HR');
// });
