<?php

namespace Tests\Unit\Location;

use Brain\Monkey;
use EightshiftForms\Settings\Settings\SettingsLocation;

use function Tests\setupMocks;
use function Tests\mock;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$this->wpFaker = \Brain\faker()->wp();

	$this->locationSettings = new SettingsLocation();
});

afterAll(function() {
	\Brain\fakerReset();
	Monkey\tearDown();
});

test('Register method will call custom hooks', function () {
	$this->locationSettings->register();

	$this->assertSame(10, \has_filter(SettingsLocation::FILTER_SETTINGS_SIDEBAR_NAME, 'EightshiftForms\Settings\Settings\SettingsLocation->getSettingsSidebar()'), 'The callback getSettingsSidebar should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsLocation::FILTER_SETTINGS_NAME, 'EightshiftForms\Settings\Settings\SettingsLocation->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
});

test('getSettingsSidebar should return correct array keys', function () {
	$sidebar = $this->locationSettings->getSettingsSidebar();

	$this->assertArrayHasKey('label', $sidebar, 'Key label must be present in the array');
	$this->assertArrayHasKey('value', $sidebar, 'Key value must be present in the array');
	$this->assertArrayHasKey('icon', $sidebar, 'Key icon must be present in the array');
});

test('getSettingsData should return array of correct keys with data items.', function () {

	global $wpdb;
	$wpdb = mock('\\WPDB');

	$wpdb->shouldReceive('get_results')->andReturn($this->wpFaker->posts(2));
	$wpdb->shouldReceive('prepare')->andReturn('');
	$wpdb->posts = '';

	$data = $this->locationSettings->getSettingsData('1');

	$this->assertIsArray($data, 'Data must be array type.');
	$this->assertArrayHasKey('component', $data[0], 'Key component must be present in the array in first index.');
	$this->assertSame('intro', $data[0]['component'], 'Key component must equal intro string.');
	$this->assertArrayHasKey('component', $data[1], 'Key component must be present in the array in secound index.');
	$this->assertSame('admin-listing', $data[1]['component'], 'Key component must equal intro string.');
	$this->assertArrayHasKey('adminListingForms', $data[1], 'Key adminListingForms must be present in the array in secound index.');
	$this->assertIsArray($data[1]['adminListingForms'], 'Key adminListingForms must be array.');
	$this->assertArrayHasKey('id', $data[1]['adminListingForms'][0], 'Key id must be present in the array for adminListingForms.');
	$this->assertArrayHasKey('postType', $data[1]['adminListingForms'][0], 'Key postType must be present in the array for adminListingForms.');
	$this->assertArrayHasKey('title', $data[1]['adminListingForms'][0], 'Key title must be present in the array for adminListingForms.');
	$this->assertArrayHasKey('status', $data[1]['adminListingForms'][0], 'Key status must be present in the array for adminListingForms.');
	$this->assertArrayHasKey('editLink', $data[1]['adminListingForms'][0], 'Key editLink must be present in the array for adminListingForms.');
	$this->assertArrayHasKey('viewLink', $data[1]['adminListingForms'][0], 'Key viewLink must be present in the array for adminListingForms.');

	unset($wpdb);
});

test('getSettingsData should return array of correct keys for empty data items.', function () {

	global $wpdb;
	$wpdb = mock('\\WPDB');

	$wpdb->shouldReceive('get_results')->andReturn([]);
	$wpdb->shouldReceive('prepare')->andReturn('');
	$wpdb->posts = '';

	$data = $this->locationSettings->getSettingsData('1');

	$this->assertIsArray($data, 'Data must be array type.');
	$this->assertArrayHasKey('component', $data[0], 'Key component must be present in the array in first index.');
	$this->assertSame('intro', $data[0]['component'], 'Key component must equal intro string.');
	$this->assertArrayHasKey('component', $data[1], 'Key component must be present in the array in secound index.');
	$this->assertSame('highlighted-content', $data[1]['component'], 'Key component must equal highlighted-content string.');
	$this->assertArrayNotHasKey('adminListingForms', $data[1], 'Key adminListingForms must not be present in the array in secound index.');

	unset($wpdb);
});

test('getSettingsGlobalData should return array', function () {
	$globalData = $this->locationSettings->getSettingsGlobalData();

	$this->assertIsArray($globalData);
});
