<?php

namespace Tests;

use Brain\Monkey\Functions;
use Mockery;
use Mockery\MockInterface;

/**
 * Helper function that will setup some repeating mocks in every tests.
 *
 * This is a way to circumvent the issue I was having described here:
 * https://github.com/pestphp/pest/issues/259
 */
function setupMocks() {
	// Mock WP functions
	Functions\stubTranslationFunctions();
	Functions\stubEscapeFunctions();

	// Mock days in sec constant.
	if (!defined('DAY_IN_SECONDS')) {
		define('DAY_IN_SECONDS', 3600);
	}
}


function mock(string $class): MockInterface
{
	return Mockery::mock($class);
}

