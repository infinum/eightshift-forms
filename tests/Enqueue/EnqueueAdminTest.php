<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use Brain\Monkey\Actions;
use EightshiftForms\Enqueue\Admin\EnqueueAdmin;
use EightshiftForms\Manifest\Manifest;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();
	$this->manifest = new Manifest;
	$this->enqueueAdmin = new EnqueueAdmin($this->manifest);
});

afterEach(function() {
	unset($this->enqueueAdmin);
	unset($this->manifest);
	Monkey\tearDown();
});

test('EnqueueAdmin service class registers hooks for enqueueing styles properly', function() {
	Actions\expectAdded('login_enqueue_scripts', [$this->enqueueAdmin, 'enqueueStyles']);
	Actions\expectAdded('admin_enqueue_scripts', [$this->enqueueAdmin, 'enqueueStyles']);

	$this->enqueueAdmin->register();
});

test('EnqueueAdmin service class registers hook for enqueueing scripts properly', function() {
	Actions\expectAdded('admin_enqueue_scripts', [$this->enqueueAdmin, 'enqueueScripts']);

	$this->enqueueAdmin->register();
});

test('EnqueueAdmin returns the correct assets prefix', function() {
	expect($this->enqueueAdmin->getAssetsPrefix())->toBe('eightshift-forms');
});

test('EnqueueAdmin returns the correct assets version', function() {
	expect($this->enqueueAdmin->getAssetsVersion())->toBe('1.0.0');
});
