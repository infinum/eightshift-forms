<?php

namespace Tests\Unit\Geolocation;

use Brain\Monkey;
use EightshiftForms\Geolocation\SettingsGeolocation;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$this->geolocationSettings = new SettingsGeolocation();
});

afterAll(function() {
	Monkey\tearDown();
});

test('Register method will call custom hooks', function () {
	$this->geolocationSettings->register();

	$this->assertSame(10, \has_filter(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_NAME, 'EightshiftForms\Geolocation\SettingsGeolocation->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, 'EightshiftForms\Geolocation\SettingsGeolocation->isSettingsGlobalValid()'), 'The callback isSettingsGlobalValid should be hooked to custom filter hook with priority 10.');
});

test('isSettingsGlobalValid should return boolean', function () {
	$data = $this->geolocationSettings->isSettingsGlobalValid();

	$this->assertIsBool($data, 'The result should be a boolean');
});

test('getSettingsData should return empty array', function () {
	$data = $this->geolocationSettings->getSettingsData('1');

	$this->assertIsArray($data);
});

test('getSettingsGlobalData should return array', function () {
	$globalData = $this->geolocationSettings->getSettingsGlobalData();

	$this->assertIsArray($globalData);
});
