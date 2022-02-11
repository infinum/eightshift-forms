<?php

namespace Tests\Unit\Block;

use EightshiftForms\Geolocation\Geolocation;
use Mockery;

use function Brain\Monkey\setUp;
use Brain\Monkey\Functions;
use Brain\Monkey\Hook\HookExpectationExecutor;

use function Brain\Monkey\tearDown;
use function Tests\setupMocks;
use function Tests\mock;


/**
 * Mock before tests.
 */
beforeEach(function () {
	setUp();
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
	tearDown();

	putenv('TEST');
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
