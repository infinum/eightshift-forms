<?php

namespace Tests\Unit\Integrations\Hubspot;

use Brain\Monkey;
use EightshiftForms\Integrations\Clearbit\ClearbitClient;
use EightshiftForms\Integrations\Clearbit\ClearbitClientInterface;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
use EightshiftForms\Integrations\Clearbit\SettingsClearbitDataInterface;
use EightshiftForms\Integrations\Hubspot\Hubspot;
use EightshiftForms\Integrations\Hubspot\HubspotClient;
use EightshiftForms\Integrations\Hubspot\HubspotClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms\Validation\Validator;

use function Tests\setupMocks;

class SettingsHubspotMock extends SettingsHubspot {

	public function __construct(
		ClearbitClientInterface $clearbitClient,
		SettingsClearbitDataInterface $clearbitSettings,
		HubspotClientInterface $hubspotClient,
		MapperInterface $hubspot,
		SettingsFallbackDataInterface $settingsFallback
	) {
		parent::__construct($clearbitClient, $clearbitSettings, $hubspotClient, $hubspot, $settingsFallback);
	}
};

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$hubspotClient = new HubspotClient();
	$labels = new Labels();
	$validator = new Validator($labels);
	$clearbitClient = new ClearbitClient();
	$clearbitSettings = new SettingsClearbit($clearbitClient);
	$settingsFallback = new SettingsFallback();

	$hubspot = new Hubspot($hubspotClient, $validator);

	$this->hubspotSettings = new SettingsHubspotMock($clearbitClient, $clearbitSettings, $hubspotClient, $hubspot, $settingsFallback);
});

afterAll(function() {
	Monkey\tearDown();
});

test('Register method will call sidebar hook', function () {
	$this->hubspotSettings->register();

	$this->assertSame(10, \has_filter(SettingsHubspotMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Integrations\Hubspot\SettingsHubspotMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsHubspotMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Integrations\Hubspot\SettingsHubspotMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsHubspotMock::FILTER_SETTINGS_IS_VALID_NAME, 'Tests\Unit\Integrations\Hubspot\SettingsHubspotMock->isSettingsValid()'), 'The callback isSettingsValid should be hooked to custom filter hook with priority 10.');
});
