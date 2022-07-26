<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use EightshiftForms\Enqueue\Blocks\EnqueueBlocks;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Manifest\Manifest;
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
	$this->manifest = new Manifest;
	$this->labels = new Labels;
	$this->validator = new Validator($this->labels);

	$this->enqueueBlocks = new EnqueueBlocks($this->manifest, $this->validator);
});

afterEach(function () {
	Monkey\tearDown();
});

test('Block editor scripts can be enqueued', function () {
	Actions\expectAdded('enqueue_block_editor_assets', [$this->enqueueBlocks, 'enqueueBlockEditorScript']);

	$this->enqueueBlocks->register();
});

test('Block editor local styles can be enqueued', function () {
	Actions\expectAdded('enqueue_block_editor_assets', [$this->enqueueBlocks, 'enqueueBlockEditorStyleLocal']);

	$this->enqueueBlocks->register();
});

test('Block editor options styles can be enqueued', function () {
	Actions\expectAdded('enqueue_block_editor_assets', [$this->enqueueBlocks, 'enqueueBlockEditorOptionsStyles']);

	$this->enqueueBlocks->register();
});

test('Block styles can be enqueued', function () {
	Actions\expectAdded('enqueue_block_assets', [$this->enqueueBlocks, 'enqueueBlockStyleLocal']);

	$this->enqueueBlocks->register();
});


test('Frontend-only block scripts can be enqueued', function () {
	Actions\expectAdded('enqueue_block_assets', [$this->enqueueBlocks, 'enqueueBlockFrontendScript']);

	$this->enqueueBlocks->register();
});

test('Frontend-only block styles can be enqueued', function () {
	Actions\expectAdded('enqueue_block_assets', [$this->enqueueBlocks, 'enqueueBlockFrontendStyle']);

	$this->enqueueBlocks->register();
});

test('Block editor local styles can be disabled', function () {
	putenv('test_force_option_es-forms-general-disable-default-enqueue-HR=styles');

	expect($this->enqueueBlocks->enqueueBlockEditorStyleLocal())->toBe(null);

	putenv('test_force_option_es-general-disable-default-enqueue-HR');
});

test('Block styles can be disabled', function () {
	putenv('test_force_option_es-forms-general-disable-default-enqueue-HR=styles');

	expect($this->enqueueBlocks->enqueueBlockStyleLocal())->toBe(null);

	putenv('test_force_option_es-general-disable-default-enqueue-HR');
});

test('Block editor styles are enqueued when not disabled', function () {
	buildTestBlocks();

	putenv('ES_TEST=true');
	putenv('test_force_option_es-forms-general-disable-default-enqueue-HR=none');

	Functions\expect('wp_register_style')->once();
	Functions\expect('wp_enqueue_style')->once()->with('eightshift-forms-block-editor-style');
	
	$this->enqueueBlocks->enqueueBlockEditorStyleLocal();
	
	putenv('ES_TEST');
	putenv('test_force_option_es-forms-general-disable-default-enqueue-HR');

	destroyTestBlocks();
});

test('Block styles are enqueued when not disabled', function () {
	buildTestBlocks();

	putenv('ES_TEST=true');

	Functions\expect('wp_register_style')->once();
	Functions\expect('wp_enqueue_style')->once()->with('eightshift-forms-block-style');

	expect($this->enqueueBlocks->enqueueBlockStyleLocal());

	putenv('ES_TEST');

	destroyTestBlocks();
});

test('Block frontend scripts are enqueued when not disabled', function () {
	buildTestBlocks();

	putenv('ES_TEST=true');

	Functions\expect('wp_register_script')->once();
	Functions\expect('wp_enqueue_script')->once()->with('eightshift-forms-block-frontend-scripts');
	Functions\expect('wp_localize_script')->atLeast()->once()->andReturn(true);

	expect($this->enqueueBlocks->enqueueBlockFrontendScript());

	putenv('ES_TEST');

	destroyTestBlocks();
});

test('Block editor option styles are enqueued', function () {
	buildTestBlocks();

	putenv('ES_TEST=true');

	Functions\expect('wp_register_style')->once();
	Functions\expect('wp_enqueue_style')->once()->with('eightshift-forms-editor-style');

	expect($this->enqueueBlocks->enqueueBlockEditorOptionsStyles());

	putenv('ES_TEST');

	destroyTestBlocks();
});
