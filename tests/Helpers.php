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

	if (!defined('DAY_IN_SECONDS')) {
		define('DAY_IN_SECONDS', 3600);
	}

	Functions\when('get_locale')->justReturn('HR');

	Functions\when('get_option')->returnArg();

	Functions\when('get_stylesheet_directory')->justReturn(dirname(__FILE__) . '/');

	Functions\when('get_permalink')->justReturn('');

	Functions\when('get_template_directory')->justReturn(dirname(__FILE__) . '/data');

	Functions\when('get_edit_post_link')->alias(function($id) {
		return "/wp-admin/post.php?post={$id}&action=edit";
	});
}


function mock(string $class): MockInterface
{
	return Mockery::mock($class);
}

