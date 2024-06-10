<?php

namespace Tests\Unit\Mailer;

use Brain\Monkey;
use EightshiftForms\Mailer\SettingsMailer;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$this->mailerSettings = new SettingsMailer();
});

afterAll(function() {
	Monkey\tearDown();
});

test('Register method will call sidebar hook', function () {
	$this->mailerSettings->register();
	expect(\has_filter(SettingsMailer::FILTER_SETTINGS_NAME, 'EightshiftForms\Mailer\SettingsMailer->getSettingsData()'))->toBe(10);
	expect(\has_filter(SettingsMailer::FILTER_SETTINGS_GLOBAL_NAME, 'EightshiftForms\Mailer\SettingsMailer->getSettingsGlobalData()'))->toBe(10);
	expect(\has_filter(SettingsMailer::FILTER_SETTINGS_IS_VALID_NAME, 'EightshiftForms\Mailer\SettingsMailer->isSettingsValid()'))->toBe(10);
});

test('isSettingsValid method returns correct values', function () {
	expect($this->mailerSettings->isSettingsValid('1234'))->toBeFalse();
});

test('getSettingsData method returns correct values', function () {
	expect($this->mailerSettings->getSettingsData('1234'))->toBeArray();
});

