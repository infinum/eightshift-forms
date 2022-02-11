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
}


function mock(string $class): MockInterface
{
	return Mockery::mock($class);
}

