<?php

namespace Tests\Unit\Manifest;

use Brain\Monkey;
use EightshiftForms\Manifest\Manifest;

use function Tests\mock;
use function Tests\setupMocks;

beforeEach(function() {
	Monkey\setUp();
	setupMocks();

	// Setup manifest mock.
	$this->manifest = new Manifest();
});

afterEach(function() {
	Monkey\tearDown();
});

test('Register method will call init hook', function () {
	$this->manifest->register();

	$this->assertSame(10, has_action('init', 'EightshiftForms\Manifest\Manifest->setAssetsManifestRaw()'));
	$this->assertSame(10, has_filter(Manifest::MANIFEST_ITEM, 'EightshiftForms\Manifest\Manifest->getAssetsManifestItem()'));
});
