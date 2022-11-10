<?php

namespace Tests\Unit\Integrations\Greenhouse;

use Brain\Monkey;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Greenhouse\Greenhouse;
use EightshiftForms\Integrations\Greenhouse\GreenhouseClient;
use EightshiftForms\Integrations\Greenhouse\SettingsGreenhouse;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms\Validation\Validator;

use function Tests\setupMocks;

class SettingsGreenhouseMock extends SettingsGreenhouse {

	public function __construct(
		ClientInterface $greenhouseClient,
		MapperInterface $greenhouse,
		SettingsFallbackDataInterface $settingsFallback
	) {
		parent::__construct($greenhouseClient, $greenhouse, $settingsFallback);
	}
};

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$greenhouseClient = new GreenhouseClient();
	$labels = new Labels();
	$validator = new Validator($labels);
	$greenhouse = new Greenhouse($greenhouseClient, $validator);
	$settingsFallback = new SettingsFallback();

	$this->greenhouseSettings = new SettingsGreenhouseMock($greenhouseClient, $greenhouse, $settingsFallback);
});

afterAll(function() {
	Monkey\tearDown();
});

test('Register method will call sidebar hook', function () {
	$this->greenhouseSettings->register();

	$this->assertSame(10, \has_filter(SettingsGreenhouseMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Integrations\Greenhouse\SettingsGreenhouseMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsGreenhouseMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Integrations\Greenhouse\SettingsGreenhouseMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsGreenhouseMock::FILTER_SETTINGS_IS_VALID_NAME, 'Tests\Unit\Integrations\Greenhouse\SettingsGreenhouseMock->isSettingsValid()'), 'The callback isSettingsValid should be hooked to custom filter hook with priority 10.');
});
