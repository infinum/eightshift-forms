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

test('Blocks service class registers hooks properly on WP >= 5.8', function () {
	Actions\expectAdded('init');
	Filters\expectRemoved('the_content', 'wpautop');
	Filters\expectAdded('block_categories_all', [$this->blocks, 'getCustomCategory']);
	Filters\expectAdded('es_blocks_string_to_filter', [$this->blocks, 'getStringToValue']);
	Filters\expectAdded('es_blocks_options_checkbox_is_checked_filter', [$this->blocks, 'isCheckboxOptionChecked']);


	$this->blocks->register();
	
	expect(has_action('init', [$this->blocks, 'getBlocksDataFullRaw']))->toBe(10);
	expect(has_action('init', [$this->blocks, 'registerBlocks']))->toBe(11);
	expect(has_filter('the_content', 'wpautop'))->toBe(false);
	expect(has_filter('block_categories_all', [$this->blocks, 'getCustomCategory']))->toBe(10);
	expect(has_filter('es_blocks_string_to_filter', [$this->blocks, 'getStringToValue']))->toBe(10);
	expect(has_filter('es_blocks_options_checkbox_is_checked_filter', [$this->blocks, 'isCheckboxOptionChecked']))->toBe(10);
});
