<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use Brain\Monkey\Filters;

use EightshiftForms\Integrations\Goodbits\Goodbits;
use EightshiftForms\Integrations\Goodbits\GoodbitsClient;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Validation\Validator;

use function Tests\buildTestBlocks;
use function Tests\destroyTestBlocks;
use function Tests\setupMocks;


/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();
	$this->goodbitsClient = new GoodbitsClient();
	$this->labels = new Labels;
	$this->validator = new Validator($this->labels);
	$this->goodbits = new Goodbits($this->goodbitsClient, $this->validator);
});

afterEach(function() {
	Monkey\tearDown();
});

test('Goodbits service interface registers filters properly', function() {
	Filters\expectAdded('es_goodbits_mapper_filter')->with([$this->goodbits, 'getForm'], 10, 3);
	Filters\expectAdded('es_goodbits_form_fields_filter')->with([$this->goodbits, 'getFormFields'], 11, 2);
	
	$this->goodbits->register();
});

test('Goodbits getForm return HTML markup for the form', function() {
	buildTestBlocks();
	putenv("test_force_post_meta_es-forms-goodbits-integration-fields-HR=[]");
	
	expect(str_replace(["\n", "\t"], " ", $this->goodbits->getForm(123)))->toContain('<input   class="es-input"   name="email"   id="email"   type="text"          data-tracking=\'email\'  />');
	
	putenv("test_force_post_meta_es-forms-goodbits-integration-fields-HR");
	destroyTestBlocks();
});

test('Goodbits getFormFields returns an empty array if Goodbits list is not set', function() {
	buildTestBlocks();
	putenv('test_force_post_meta_es-forms-goodbits-list-HR=""');
	
	expect($this->goodbits->getFormFields(1234))->toBe([]);
	
	putenv("test_force_post_meta_es-forms-goodbits-list-HR");
	destroyTestBlocks();
});
