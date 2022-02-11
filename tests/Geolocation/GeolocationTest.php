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

	putenv('TEST=1');

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

	putenv('TEST');
});


test('Register method will call init hook', function () {
	$this->geolocation->register();

	$this->assertSame(10, has_filter('init', 'EightshiftForms\Geolocation\Geolocation->setLocationCookie()'), 'The callback setLocationCookie should be hooked to init hook with priority 10.');
});

test('Register method will call es_geolocation_is_use_located hook', function () {
	$this->geolocation->register();

	$this->assertSame(10, has_filter(Geolocation::GEOLOCATION_IS_USER_LOCATED, 'EightshiftForms\Geolocation\Geolocation->isUserGeolocated()'), 'The callback isUserGeolocated should be hooked to custom hook with priority 10 and 3 parameters.');
});

test('setLocationCookie will exit if is not frontend.', function () {
	$action = 'is_admin';
	Functions\when('is_admin')->justReturn(putenv("SIDEAFFECT_ADMIN={$action}"));

	$this->geolocation->setLocationCookie();

	$this->assertSame(getenv('SIDEAFFECT_ADMIN'), $action);

	// Cleanup.
	putenv('SIDEAFFECT_ADMIN=');
	Functions\when('is_admin')->justReturn(false);
});

test('setLocationCookie will exit if cookie is set.', function () {
	$action = 'is_cookie';

	$_COOKIE[Geolocation::GEOLOCATION_COOKIE] = 'HR';

	if (isset($_COOKIE[Geolocation::GEOLOCATION_COOKIE])) {
		putenv("SIDEAFFECT_COOKIE={$action}");
	}

	$this->geolocation->setLocationCookie();

	$this->assertSame(getenv('SIDEAFFECT_COOKIE'), $action);

	// Cleanup.
	unset($_COOKIE[Geolocation::GEOLOCATION_COOKIE]);
	putenv('SIDEAFFECT_COOKIE=');
});

test('isUserGeolocated will return new formId if additional locations finds match.', function () {
	putenv('TEST_GEOLOCATION=HR');

	$geo = $this->geolocation->isUserGeolocated('1', [], $this->additionalLocations['one']);

	$this->assertIsString($geo);
	$this->assertNotSame($geo, '1');
	$this->assertSame($geo, '111');
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
