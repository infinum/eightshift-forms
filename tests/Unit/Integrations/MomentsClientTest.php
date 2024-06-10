<?php

namespace Tests\Unit;

use EightshiftForms\Enrichment\Enrichment;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Moments\MomentsClient;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\when;
use function Tests\getTestTypeUnit;
use function Tests\testAfterEach;
use function Tests\testBeforeEach;

uses()->group(getTestTypeUnit());

beforeEach(function() {
	testBeforeEach();

	$this->momentsClient = new MomentsClient(new Enrichment());
});

afterEach(function() {
	testAfterEach();
});

// test('getItems will return integration data from the cache', function () {
// 	when('get_transient')->justReturn(['ivan']);
// 	expectApplied(UtilsConfig::FILTER_SETTINGS_IS_DEBUG_ACTIVE)->with('')->andReturn(false);
// 	$items = $this->momentsClient->getItems();

// 	expect($items)->toBe(['ivan']);

// 	remove_filter(UtilsConfig::FILTER_SETTINGS_IS_DEBUG_ACTIVE, 'key_exists');
// });

test('getItems get data from the external resource if not cached', function () {
	when('is_wp_error')->justReturn(false);
	expectApplied(UtilsConfig::FILTER_SETTINGS_IS_DEBUG_ACTIVE)->with('')->andReturn(true);
	define('ES_API_URL_MOMENTS', 'https://api.moments.test');
	when('wp_remote_get')->alias(function (string $url, array $args) {
		return array_merge([
			'body' => '{"forms":[{"id":"6b04df22","name":"DoNotDelete - Event Registration Form LinkedIn","elements":[{"component":"TEXT","fieldId":"first_name","personField":"firstName","label":"First Name"}]}]}',
			'response' => [
				'code' => 200,
				'message' => 'OK',
			],
			...$args,
		]);
	});

	$items = $this->momentsClient->getItems();

	var_dump($items);

	// expect($items)->toBe(['ivan']);

	remove_filter(UtilsConfig::FILTER_SETTINGS_IS_DEBUG_ACTIVE, 'key_exists');
});
