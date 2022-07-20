<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use EightshiftForms\Config\Config;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

});

afterEach(function() {
	Monkey\tearDown();
});

test('getProjectName gives the correct project name', function() {
	expect(Config::getProjectName())->toBe('eightshift-forms');
});

test('getProjectVersion gives the correct version', function() {
	expect(Config::getProjectVersion())->toBe('1.0.0');
});

test('getProjectRoutesNamespace gives the correct route namespace', function() {
	expect(Config::getProjectRoutesNamespace())->toBe('eightshift-forms');
});
test('getProjectRoutesVersion gives the correct REST API version', function() {
	expect(Config::getProjectRoutesVersion())->toBe('v1');
});
