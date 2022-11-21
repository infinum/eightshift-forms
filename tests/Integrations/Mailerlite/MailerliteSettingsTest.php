<?php

namespace Tests\Unit\Integrations\Mailerlite;

use Brain\Monkey;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Mailerlite\Mailerlite;
use EightshiftForms\Integrations\Mailerlite\MailerliteClient;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftForms\Troubleshooting\SettingsFallbackDataInterface;
use EightshiftForms\Validation\Validator;

use function Tests\setupMocks;

class SettingsMailerliteMock extends SettingsMailerlite {

	public function __construct(
		ClientInterface $mailerliteClient,
		MapperInterface $mailerlite,
		SettingsFallbackDataInterface $settingsFallback
	) {
		parent::__construct($mailerliteClient, $mailerlite, $settingsFallback);
	}
};

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$mailerliteClient = new MailerliteClient();
	$labels = new Labels();
	$validator = new Validator($labels);
	$mailerlite = new Mailerlite($mailerliteClient, $validator);
	$settingsFallback = new SettingsFallback();

	$this->mailerliteSettings = new SettingsMailerliteMock($mailerliteClient, $mailerlite, $settingsFallback);
});

afterAll(function() {
	Monkey\tearDown();
});

test('Register method will call sidebar hook', function () {
	$this->mailerliteSettings->register();

	$this->assertSame(10, \has_filter(SettingsMailerliteMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Integrations\Mailerlite\SettingsMailerliteMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsMailerliteMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Integrations\Mailerlite\SettingsMailerliteMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsMailerliteMock::FILTER_SETTINGS_IS_VALID_NAME, 'Tests\Unit\Integrations\Mailerlite\SettingsMailerliteMock->isSettingsValid()'), 'The callback isSettingsValid should be hooked to custom filter hook with priority 10.');
});
