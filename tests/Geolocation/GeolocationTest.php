<?php

namespace Tests\Unit\Geolocation;

use EightshiftForms\Geolocation\Geolocation;

use Brain\Monkey;
use Brain\Monkey\Functions;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$this->geolocation = new Geolocation();

	$this->additionalLocations = [
		'one' => [
			[
				'formId' => '111',
				'geoLocation' => [
					[
						'label' => 'Croatia',
						'value' => 'HR',
					],
				],
			],
		],
		'oneMissingGeo' => [
			[
				'formId' => '111',
			],
		],
		'multipleLocations' => [
			[
				'formId' => '111',
				'geoLocation' => [
					[
						'label' => 'Croatia',
						'value' => 'HR',
					],
					[
						'label' => 'Germany',
						'value' => 'DE',
					],
				],
			],
		],
		'multipleForms' => [
			[
				'formId' => '111',
				'geoLocation' => [
					[
						'label' => 'Croatia',
						'value' => 'HR',
					],
				],
			],
			[
				'formId' => '222',
				'geoLocation' => [
					[
						'label' => 'Germany',
						'value' => 'DE',
					],
				],
			],
		],
	];

	$this->locations = [
		[
			'label' => 'Germany',
			'value' => 'DE',
			'group' => [
				'DE',
			]
		],
		[
			'label' => 'Croatia',
			'value' => 'HR',
			'group' => [
				'HR',
			]
		]
	];
});

afterAll(function() {
	Monkey\tearDown();
});


test('Register method will call init hook', function () {
	$this->geolocation->register();

	$this->assertSame(10, has_filter('init', 'EightshiftForms\Geolocation\Geolocation->setLocationCookie()'), 'The callback setLocationCookie should be hooked to init hook with priority 10.');
});

test('Register method will call es_geolocation_is_use_located hook', function () {
	$this->geolocation->register();

	$this->assertSame(10, has_filter(Geolocation::GEOLOCATION_IS_USER_LOCATED, 'EightshiftForms\Geolocation\Geolocation->isUserGeolocated()'), 'The callback isUserGeolocated should be hooked to custom hook with priority 10 and 3 parameters.');
});

//---------------------------------------------------------------------------------//

test('setLocationCookie will exit if is not frontend.', function () {
	$action = 'is_admin';
	Functions\when('is_admin')->justReturn(putenv("SIDEAFFECT_GEOLOCATION_IS_ADMIN={$action}"));

	$this->geolocation->setLocationCookie();

	$this->assertSame(getenv('SIDEAFFECT_GEOLOCATION_IS_ADMIN'), $action);

	// Cleanup.
	putenv('SIDEAFFECT_GEOLOCATION_IS_ADMIN=');
	Functions\when('is_admin')->justReturn(false);
});

test('setLocationCookie will exit if disable filter is provided.', function () {
	$action = 'is_geo_disable_filter';
	
	add_filter('es_forms_geolocation_disable', function() {
		return true;
	});

	if (has_filter('es_forms_geolocation_disable')) {
		putenv("SIDEAFFECT_GEOLOCATION_DISABLED_FILTER={$action}");
	}

	$this->geolocation->setLocationCookie();

	$this->assertSame(getenv('SIDEAFFECT_GEOLOCATION_DISABLED_FILTER'), $action);

	// Cleanup.
	remove_filter('es_forms_geolocation_disable', function() {
		return false;
	});
	putenv('SIDEAFFECT_GEOLOCATION_DISABLED_FILTER=');
});

test('setLocationCookie will exit if cookie is set.', function () {
	$action = 'is_cookie';

	$_COOKIE[Geolocation::GEOLOCATION_COOKIE] = 'HR';

	if (isset($_COOKIE[Geolocation::GEOLOCATION_COOKIE])) {
		putenv("SIDEAFFECT_GEOLOCATION_COOKIE_EXIT={$action}");
	}

	$this->geolocation->setLocationCookie();

	$this->assertSame(getenv('SIDEAFFECT_GEOLOCATION_COOKIE_EXIT'), $action);

	// Cleanup.
	unset($_COOKIE[Geolocation::GEOLOCATION_COOKIE]);
	putenv('SIDEAFFECT_GEOLOCATION_COOKIE_EXIT=');
});

test('setLocationCookie will set cookie.', function () {
	Functions\when('setcookie')->alias(function ($args) {
		putenv("SIDEAFFECT_GEOLOCATION_COOKIE_SET={$args}");
	});

	$this->geolocation->setLocationCookie();

	$this->assertSame(getenv('SIDEAFFECT_GEOLOCATION_COOKIE_SET'), Geolocation::GEOLOCATION_COOKIE);

	// Cleanup.
	putenv('SIDEAFFECT_GEOLOCATION_COOKIE_SET=');
});

//---------------------------------------------------------------------------------//

test('isUserGeolocated will return formId if disable filter is provided.', function () {
	$action = 'is_geo_disable_filter';
	
	add_filter('es_forms_geolocation_disable', function() {
		return true;
	});

	if (has_filter('es_forms_geolocation_disable')) {
		putenv("SIDEAFFECT_GEOLOCATION_DISABLED_FILTER={$action}");
	}

	$geo = $this->geolocation->isUserGeolocated(1, [], []);

	$this->assertSame(getenv('SIDEAFFECT_GEOLOCATION_DISABLED_FILTER'), $action);
	$this->assertIsString($geo);
	$this->assertSame($geo, '1');

	// Cleanup.
	remove_filter('es_forms_geolocation_disable', function() {
		return false;
	});
	putenv('SIDEAFFECT_GEOLOCATION_DISABLED_FILTER=');
});

test('isUserGeolocated will return new formId if additional locations finds match.', function () {
	putenv('TEST_GEOLOCATION=HR');

	$geo = $this->geolocation->isUserGeolocated('1', [], $this->additionalLocations['one']);

	$this->assertIsString($geo);
	$this->assertNotSame($geo, '1');
	$this->assertSame($geo, '111');
});

test('isUserGeolocated will return formId if additional locations are missing geoLocation array key.', function () {
	putenv('TEST_GEOLOCATION=HR');

	$geo = $this->geolocation->isUserGeolocated('1', [], $this->additionalLocations['oneMissingGeo']);

	$this->assertIsString($geo);
	$this->assertSame($geo, '1');
});

test('isUserGeolocated will return formId if additional locations don\'t match but default locations match.', function () {
	putenv('TEST_GEOLOCATION=HR');

	$geo = $this->geolocation->isUserGeolocated('1', $this->locations, []);

	$this->assertIsString($geo);
	$this->assertSame($geo, '1');
});

test('isUserGeolocated will return empty string if additional locations don\'t match and default locations match but default locations exist.', function () {
	putenv('TEST_GEOLOCATION=AT');

	$geo = $this->geolocation->isUserGeolocated('1', $this->locations, []);

	$this->assertIsString($geo);
	$this->assertSame($geo, '');
});

test('isUserGeolocated will return formId if both additional parameters are empty.', function () {

	$geo = $this->geolocation->isUserGeolocated('1', [], []);

	$this->assertIsString($geo);
	$this->assertSame($geo, '1');
});

//---------------------------------------------------------------------------------//

test('getCountries will return countries array.', function () {

	$geo = $this->geolocation->getCountries();

	$this->assertIsArray($geo, 'Countries data must be an array.');
	$this->assertArrayHasKey(0, $geo, 'Countries data must have some keys.');
	$this->assertIsArray($geo[0], 'Countries data item must be array.');
	$this->assertArrayHasKey('label', $geo[0], 'Countries data item must contain label key.');
	$this->assertArrayHasKey('value', $geo[0], 'Countries data item must contain value key.');
	$this->assertArrayHasKey('group', $geo[0], 'Countries data item must contain group key.');
	$this->assertIsArray($geo[0]['group'], 'Countries data item group key must be an array.');
});

test('getCountries will return filter if countries filter is provided.', function () {
	$action = 'is_geo_countries_filter';
	
	add_filter('es_forms_geolocation_countries', function() {
		return true;
	});

	if (has_filter('es_forms_geolocation_countries')) {
		putenv("SIDEAFFECT_GEOLOCATION_COUNTRIESD_FILTER={$action}");
	}

	$geo = $this->geolocation->getCountries();

	$this->assertSame(getenv('SIDEAFFECT_GEOLOCATION_COUNTRIESD_FILTER'), $action);
	$this->assertIsArray($geo);

	// Cleanup.
	remove_filter('es_forms_geolocation_countries', function() {
		return false;
	});
	putenv('SIDEAFFECT_GEOLOCATION_COUNTRIESD_FILTER=');
});
