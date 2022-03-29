<?php

namespace Tests\Unit\Integrations\Hubspot;

use Brain\Monkey;
use EightshiftForms\Integrations\Hubspot\Hubspot;
use EightshiftForms\Integrations\Hubspot\HubspotClient;
use EightshiftForms\Integrations\Hubspot\HubspotClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Validation\Validator;

use function Tests\setupMocks;

class SettingsHubspotMock extends SettingsHubspot {

	public function __construct(
		HubspotClientInterface $hubspotClient,
		MapperInterface $hubspot
	) {
		parent::__construct($hubspotClient, $hubspot);
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
	$hubspot = new Hubspot($hubspotClient, $validator);

	$this->hubspotSettings = new SettingsHubspotMock($hubspotClient, $hubspot);
});

afterAll(function() {
	Monkey\tearDown();
});

test('Register method will call sidebar hook', function () {
	$this->hubspotSettings->register();

	$this->assertSame(10, has_filter(SettingsHubspotMock::FILTER_SETTINGS_SIDEBAR_NAME, 'Tests\Unit\Integrations\Hubspot\SettingsHubspotMock->getSettingsSidebar()'), 'The callback getSettingsSidebar should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, has_filter(SettingsHubspotMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Integrations\Hubspot\SettingsHubspotMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, has_filter(SettingsHubspotMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Integrations\Hubspot\SettingsHubspotMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, has_filter(SettingsHubspotMock::FILTER_SETTINGS_IS_VALID_NAME, 'Tests\Unit\Integrations\Hubspot\SettingsHubspotMock->isSettingsValid()'), 'The callback isSettingsValid should be hooked to custom filter hook with priority 10.');
});
