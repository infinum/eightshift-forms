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

test('Block service class registers the old custom category hook on WP < 5.8', function() {
	putenv('test_force_unit_test_wp_version=5.7');
	Filters\expectAdded('block_categories', [$this->blocks, 'getCustomCategoryOld']);
	
	$this->blocks->register();

	expect(has_filter('block_categories', [$this->blocks, 'getCustomCategoryOld']))->toBe(10);
	putenv('test_force_unit_test_wp_version');
});

test('getCustomCategoryOld adds the custom category for Eightshift Forms', function() {
	expect($this->blocks->getCustomCategoryOld([
		[
			'slug' => 'existing-block-category',
			'title' => 'Existing block category',
			'icon' => 'admin-settings',
		],
		], $this->wpFaker->post()))->toBe([
			[
				'slug' => 'existing-block-category',
				'title' => 'Existing block category',
				'icon' => 'admin-settings',
			],
			[
				'slug' => 'eightshift-forms',
				'title' => 'Eightshift Forms',
				'icon' => 'admin-settings',
			]
		]);
});

test('getCustomCategory adds the custom category for Eightshift Forms', function() {
	expect($this->blocks->getCustomCategory([
		[
			'slug' => 'existing-block-category',
			'title' => 'Existing block category',
			'icon' => 'admin-settings',
		],
		], Mockery::mock('WP_Block_Editor_Context')))->toBe([
			[
				'slug' => 'existing-block-category',
				'title' => 'Existing block category',
				'icon' => 'admin-settings',
			],
			[
				'slug' => 'eightshift-forms',
				'title' => 'Eightshift Forms',
				'icon' => 'admin-settings',
			]
		]);
});

test('getStringToValue works as expected', function ($string, $return) {
	expect($this->blocks->getStringToValue($string))->toBe($return);
})->with([
	[
		'ALLUPPERCASE',
		'alluppercase'
	],
	[
		'SomeAreUpper And There Are Spaces',
		'someareupper-and-there-are-spaces'
	],
	[
		'SomeAreUpper_And_There_Are_Underscores',
		'someareupper-and-there-are-underscores'
	],
	[
		'Everything apart from letters, numbers and underscores, such as !$%&)=?*žšć gets removed',
		'everything-apart-from-letters-numbers-and-underscores-such-as--gets-removed'
	],
]);
