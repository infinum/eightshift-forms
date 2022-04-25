<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Hooks\Filters;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$this->filters = new Filters();
});

afterAll(function() {
	Monkey\tearDown();
});

test('getIntegrationFilterName will return correct filter name', function () {
	$filter = $this->filters::getIntegrationFilterName('greenhouse', 'fieldsSettings');

	$this->assertSame('es_forms_integration_greenhouse_fields_settings', $filter);
});

test('getIntegrationFilterName will throw error if wrong filter name or name is provided.', function () {
	$this->filters::getIntegrationFilterName('greenHouse', 'fieldsaSettings');
})->throws(MissingFilterInfoException::class);

//---------------------------------------------------------------------------------//

test('getBlocksFilterName will return correct filter name', function () {
	$filter = $this->filters::getBlocksFilterName('additionalBlocks');

	$this->assertSame('es_forms_blocks_additional_blocks', $filter);
});

test('getBlocksFilterName will throw error if wrong filter name or name is provided.', function () {
	$this->filters::getBlocksFilterName('wrong');
})->throws(MissingFilterInfoException::class);

//---------------------------------------------------------------------------------//

test('getBlockFilterName will return correct filter name', function () {
	$filter = $this->filters::getBlockFilterName('input', 'additionalContent');

	$this->assertSame('es_forms_block_input_additional_content', $filter);
});

test('getBlockFilterName will throw error if wrong filter name or name is provided.', function () {
	$this->filters::getBlockFilterName('wrong', 'wrong');
})->throws(MissingFilterInfoException::class);

//---------------------------------------------------------------------------------//

test('getGeolocationFilterName will return correct filter name', function () {
	$filter = $this->filters::getGeolocationFilterName('disable');

	$this->assertSame('es_forms_geolocation_disable', $filter);
});

test('getGeolocationFilterName will throw error if wrong filter name or name is provided.', function () {
	$this->filters::getGeolocationFilterName('wrong');
})->throws(MissingFilterInfoException::class);

//---------------------------------------------------------------------------------//

test('getValidationSettingsFilterName will return correct filter name', function () {
	$filter = $this->filters::getValidationSettingsFilterName('failMimetypeValidationWhenFileNotOnFS');

	$this->assertSame('es_forms_validation_force_mimetype_from_fs', $filter);
});

test('getValidationSettingsFilterName will throw error if wrong filter name or name is provided.', function () {
	$this->filters::getGeolocationFilterName('wrong');
})->throws(MissingFilterInfoException::class);
