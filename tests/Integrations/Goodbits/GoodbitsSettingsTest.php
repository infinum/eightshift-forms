<?php

namespace Tests\Unit\Integrations\Goodbits;

use Brain\Monkey;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Goodbits\Goodbits;
use EightshiftForms\Integrations\Goodbits\GoodbitsClient;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms\Validation\Validator;

use function Tests\setupMocks;

class SettingsGoodbitsMock extends SettingsGoodbits {

	public function __construct(
		ClientInterface $goodbitsClient,
		MapperInterface $goodbits,
		SettingsFallbackDataInterface $settingsFallback
	) {
		parent::__construct($goodbitsClient, $goodbits, $settingsFallback);
	}
};

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$goodbitsClient = new GoodbitsClient();
	$labels = new Labels();
	$validator = new Validator($labels);
	$goodbits = new Goodbits($goodbitsClient, $validator);
	$settingsFallback = new SettingsFallback();

	$this->goodbitsSettings = new SettingsGoodbitsMock($goodbitsClient, $goodbits, $settingsFallback);
});

afterEach(function() {
	unset($this->goodbitsSettings);
	unset($goodbits);
	unset($validator);
	unset($labels);
	unset($goodbitsClient);

	Monkey\tearDown();
});

test('Register method will call sidebar hook', function () {
	$this->goodbitsSettings->register();

	$this->assertSame(10, \has_filter(SettingsGoodbitsMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Integrations\Goodbits\SettingsGoodbitsMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsGoodbitsMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Integrations\Goodbits\SettingsGoodbitsMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsGoodbitsMock::FILTER_SETTINGS_IS_VALID_NAME, 'Tests\Unit\Integrations\Goodbits\SettingsGoodbitsMock->isSettingsValid()'), 'The callback isSettingsValid should be hooked to custom filter hook with priority 10.');
});

test('isSettingsGlobalValid returns false when Goodbits is not used', function() {
	expect($this->goodbitsSettings->isSettingsGlobalValid())->toBe(false);
});

test('isSettingsGlobalValid returns true when Goodbits is configured correctly', function() {
	putenv('test_force_option_es-forms-goodbits-use-HR=goodbits-use');
	expect($this->goodbitsSettings->isSettingsGlobalValid())->toBe(true);
	putenv('test_force_option_es-forms-goodbits-use-HR');
});
