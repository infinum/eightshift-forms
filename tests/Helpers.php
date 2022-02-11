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

	// Mock locale.
	Functions\when('get_locale')->justReturn('HR');

	// Mock options.
	Functions\when('get_option')->returnArg();

	// Mock the template dir location.
	Functions\when('get_stylesheet_directory')->justReturn(dirname(__FILE__) . '/');

	// Mock the template dir location.
	Functions\when('get_template_directory')->justReturn(dirname(__FILE__) . '/data');

	// Mock the get_edit_post_link.
	Functions\when('get_edit_post_link')->alias(function($id) {
		return "/wp-admin/post.php?post={$id}&action=edit";
	});
}


function mock(string $class): MockInterface
{
	return Mockery::mock($class);
}

