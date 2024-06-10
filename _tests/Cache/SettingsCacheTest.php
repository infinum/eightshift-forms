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
	unset($this->settingsCache);
	Monkey\tearDown();
});

test('SettingsCache service class registers hooks properly', function() {
	Filters\expectAdded('es_forms_settings_global_cache', [$this->settingsCache, 'getSettingsGlobalData']);

	$this->settingsCache->register();
});

test('SettingsCache getSettingsGlobalData works as expected', function() {
	buildTestBlocks();

	expect($this->settingsCache->getSettingsGlobalData())->toBe([
		[
			'component' => 'intro',
			'introTitle' => 'Cache',
			'introSubtitle' => 'In these settings, you can change all options regarding forms internal caching.',
		],
		[
			'component' => 'layout',
			'layoutItems' => [
				[
					'component' => 'submit',
					'submitFieldSkip' => true,
					'submitValue' => 'Clear Mailchimp cache',
					'submitIcon' => 'mailchimp',
					'submitAttrs' => [
						'data-type' => 'mailchimp',
					],
					'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
				],
				[
					'component' => 'submit',
					'submitFieldSkip' => true,
					'submitValue' => 'Clear Greenhouse cache',
					'submitIcon' => 'greenhouse',
					'submitAttrs' => [
						'data-type' => 'greenhouse',
					],
					'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
				],
				[
					'component' => 'submit',
					'submitFieldSkip' => true,
					'submitValue' => 'Clear HubSpot cache',
					'submitIcon' => 'hubspot',
					'submitAttrs' => [
						'data-type' => 'hubspot',
					],
					'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
				],
				[
					'component' => 'submit',
					'submitFieldSkip' => true,
					'submitValue' => 'Clear MailerLite cache',
					'submitIcon' => 'mailerlite',
					'submitAttrs' => [
						'data-type' => 'mailerlite',
					],
					'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
				],
				[
					'component' => 'submit',
					'submitFieldSkip' => true,
					'submitValue' => 'Clear Active Campaign cache',
					'submitIcon' => 'active-campaign',
					'submitAttrs' => [
						'data-type' => 'active-campaign',
					],
					'additionalClass' => 'js-es-cache-delete es-submit--cache-clear',
				],
			],
		],
	]);

	destroyTestBlocks();
});
