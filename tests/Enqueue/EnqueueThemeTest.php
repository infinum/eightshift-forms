<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use EightshiftForms\Enqueue\Theme\EnqueueTheme;
use EightshiftForms\Manifest\Manifest;
use EightshiftForms\Tracking\Tracking;

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
	$this->tracking = new Tracking;

	$this->enqueueTheme = new EnqueueTheme($this->manifest, $this->tracking);
});

afterEach(function () {
	unset($this->enqueueTheme);
	unset($this->tracking);
	unset($this->manifest);
	Monkey\tearDown();
});

test('EnqueueTheme service class registers hooks for enqueueing styles properly', function() {
	Actions\expectAdded('wp_enqueue_scripts', [$this->enqueueTheme, 'enqueueStylesLocal']);
	$this->enqueueTheme->register();
});

test('EnqueueTheme service class registers hooks for enqueueing scripts properly', function() {
	Actions\expectAdded('wp_enqueue_scripts', [$this->enqueueTheme, 'enqueueScriptsLocal']);
	$this->enqueueTheme->register();
});

test('EnqueueTheme service class registers hooks for enqueueing ReCaptcha properly', function() {
	Actions\expectAdded('wp_enqueue_scripts', [$this->enqueueTheme, 'enqueueScriptsCaptcha']);
	$this->enqueueTheme->register();
});

test('Theme scripts and styles can be disabled', function () {
	putenv('test_force_option_es-forms-general-disable-default-enqueue-HR=scripts, styles');
	expect($this->enqueueTheme->enqueueScriptsLocal())->toBe(null);
	expect($this->enqueueTheme->enqueueStylesLocal())->toBe(null);

	putenv('test_force_option_es-forms-general-disable-default-enqueue-HR');
});

test('Theme styles are registered and enqueued when not disabled', function () {
	buildTestBlocks();

	$originalESTest = getenv('ES_TEST');
	putenv('ES_TEST=true');

	Functions\expect('wp_register_style')->once()->andReturn(true);
	Functions\expect('wp_enqueue_style')->once()->andReturn(true);

	$this->enqueueTheme->enqueueStylesLocal();

	if ($originalESTest === false) {
		putenv('ES_TEST');
	}

	destroyTestBlocks();
});

test('Theme scripts are registered and enqueued when not disabled', function () {
	buildTestBlocks();

	$originalESTest = getenv('ES_TEST');
	putenv('ES_TEST=true');

	Functions\expect('wp_register_script')->once()->andReturn(true);
	Functions\expect('wp_enqueue_script')->once()->andReturn(true);
	Functions\expect('wp_localize_script')->atLeast()->once()->andReturn(true);

	$this->enqueueTheme->enqueueScriptsLocal();

	if ($originalESTest === false) {
		putenv('ES_TEST');
	}

	destroyTestBlocks();
});

test('Theme scripts are localized properly when not disabled', function ($handle, $objectName, $dataArray) {
	buildTestBlocks();

	$originalESTest = getenv('ES_TEST');
	putenv('ES_TEST=true');
	
	Filters\expectApplied('es_forms_settings_global_is_valid_captcha')->with(false)->andReturn(true);
	putenv('test_force_option_es-forms-captcha-site-key-HR=nevershareyourkeys');
	
	Functions\expect('wp_register_script')->once()->andReturn(true);
	Functions\expect('wp_enqueue_script')->once()->andReturn(true);
	Functions\expect('wp_localize_script')->atLeast()->once()->with($handle, $objectName, $dataArray)->andReturn(true);

	$this->enqueueTheme->enqueueScriptsLocal();

	if ($originalESTest === false) {
		putenv('ES_TEST');
	}

	destroyTestBlocks();
})->with('default theme localizations');

test('Recaptcha script is not enqueued when captcha is disabled or settings are disabled', function() {
	Functions\expect('wp_register_script')->never();
	$this->enqueueTheme->enqueueScriptsCaptcha();
});

test('Recaptcha script is enqueued when captcha is enabled', function() {
	Filters\expectApplied('es_forms_settings_global_is_valid_captcha')->with(false)->andReturn(true);
	putenv('test_force_option_es-forms-captcha-site-key-HR=nevershareyourkeys');
	
	Functions\expect('wp_register_script')->once()->with(
		'eightshift-forms-captcha',
		'https://www.google.com/recaptcha/api.js?render=nevershareyourkeys',
		[],
		'1.0.0',
		false
	)->andReturn(true);
	
	Functions\expect('wp_enqueue_script')->once()->andReturn(true);

	$this->enqueueTheme->enqueueScriptsCaptcha();
});
