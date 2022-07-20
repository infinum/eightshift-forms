<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use Brain\Monkey\Filters;
use Brain\Monkey\Actions;
use EightshiftForms\Blocks\Blocks;
use Mockery;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();
	$this->blocks = new Blocks();
	$this->faker = \Brain\faker();
	$this->wpFaker = $this->faker->wp();
});

afterEach(function() {
	Monkey\tearDown();
});
