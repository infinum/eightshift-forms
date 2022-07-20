<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use Brain\Monkey\Filters;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Hooks\Filters as FormsFilters;

use function Tests\buildTestBlocks;
use function Tests\destroyTestBlocks;
use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();
	$this->settingsCache = new SettingsCache();

});

afterEach(function() {
	Monkey\tearDown();
});

test('SettingsCache service class registers hooks properly', function() {
	Filters\expectAdded('es_forms_settings_sidebar_cache', [$this->settingsCache, 'getSettingsSidebar']);
	Filters\expectAdded('es_forms_settings_global_cache', [$this->settingsCache, 'getSettingsGlobalData']);
	$this->settingsCache->register();
});

test('SettingsCache getSettingsSidebar works as expected', function() {
	expect($this->settingsCache->getSettingsSidebar())->toBe([
		'label' => 'Clear cache',
		'value' => 'cache',
		'icon' => FormsFilters::ALL['cache']['icon'],
	]);
});

test('SettingsCache getSettingsGlobalData works as expected', function() {
	buildTestBlocks();

	expect($this->settingsCache->getSettingsGlobalData())->toBe([
		[
			'component' => 'intro',
			'introTitle' => 'Clear cache',
			'introSubtitle' => 'Use the buttons below to clear the cache if the entry you\'re looking for isn\'t available or has changed.',
		],
		[
			'component' => 'submit',
			'submitFieldWidthLarge' => 2,
			'submitValue' => 'Clear Mailchimp cache',
			'submitIcon' => 'mailchimp',
			'submitAttrs' => [
				'data-type' => 'mailchimp',
			],
			'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
		],
		[
			'component' => 'submit',
			'submitFieldWidthLarge' => 2,
			'submitValue' => 'Clear Greenhouse cache',
			'submitIcon' => 'greenhouse',
			'submitAttrs' => [
				'data-type' => 'greenhouse',
			],
			'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
		],
		[
			'component' => 'submit',
			'submitFieldWidthLarge' => 2,
			'submitValue' => 'Clear Hubspot cache',
			'submitIcon' => 'hubspot',
			'submitAttrs' => [
				'data-type' => 'hubspot',
			],
			'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
		],
		[
			'component' => 'submit',
			'submitFieldWidthLarge' => 2,
			'submitValue' => 'Clear Mailerlite cache',
			'submitIcon' => 'mailerlite',
			'submitAttrs' => [
				'data-type' => 'mailerlite',
			],
			'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
		]
	]);

	destroyTestBlocks();
});
