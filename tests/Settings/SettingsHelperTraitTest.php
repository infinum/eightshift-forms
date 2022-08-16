<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use Brain\Monkey\Filters;
use EightshiftForms\Hooks\Filters as FormsFilters;
use EightshiftForms\Settings\SettingsHelper;

use function Tests\buildTestBlocks;
use function Tests\destroyTestBlocks;
use function Tests\mockFormField;
use function Tests\setupMocks;

class SettingsHelperTestClass {
	use SettingsHelper;
}

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();
	$this->settingsHelper = new SettingsHelperTestClass();
});

afterEach(function() {
	Monkey\tearDown();
});

test('getSettingsValue returns the correct post meta value', function() {
	putenv('test_force_post_meta_es-forms-test-HR=test');

	expect($this->settingsHelper->getSettingsValue('test', 1))->toBe('test');

	putenv('test_force_post_meta_es-forms-test-HR');
});

test('getSettingsValueGroup returns the correct post meta value', function() {
	putenv('test_force_post_meta_es-forms-test-HR=[1]');

	expect($this->settingsHelper->getSettingsValueGroup('test', 1))->toBe([1]);

	putenv('test_force_post_meta_es-forms-test-HR');
});

test('getOptionValue returns the correct option value', function() {
	putenv('test_force_option_es-forms-test-HR=test');

	expect($this->settingsHelper->getOptionValue('test', 1))->toBe('test');

	putenv('test_force_option_es-forms-test-HR');
});

test('getOptionValueGroup returns the correct option value', function() {
	putenv('test_force_option_es-forms-test-HR=[1]');

	expect($this->settingsHelper->getOptionValueGroup('test', 1))->toBe([1]);

	putenv('test_force_option_es-forms-test-HR');
});

test('getOptionCheckboxValues returns the correct option value', function() {
	putenv('test_force_option_es-forms-test-HR=test, best, rest');

	expect($this->settingsHelper->getOptionCheckboxValues('test', 1))->toBe(['test', 'best', 'rest']);

	putenv('test_force_option_es-forms-test-HR');
});

test('isCheckedSettings returns correctly when post meta matches key', function($key, $id, $formId, $return) {
	putenv("test_force_post_meta_es-forms-{$id}-HR={$key}");

	expect($this->settingsHelper->isCheckedSettings($key, $id, $formId))->toBe($return);

	putenv("test_force_post_meta_es-forms-{$id}-HR");
})->with([
	['key', 'test1', 1, true],
]);

test('isCheckedSettings returns correctly when post meta doesn\'t match key', function($key, $id, $formId, $return) {
	putenv("test_force_post_meta_es-forms-{$id}-HR=notreally{$key}");

	expect($this->settingsHelper->isCheckedSettings((string)$key, $id, $formId))->toBe($return);

	putenv("test_force_post_meta_es-forms-{$id}-HR");
})->with([
	['key', 'test1', 1, false],
]);

test('getSettingsValueGroup returns an empty array when an option isn\'t set', function() {
	putenv("test_force_post_meta_es-forms-nonexistent-HR=bool_false");

	expect($this->settingsHelper->getSettingsValueGroup('nonexistent', 1))->toBe([]);

	putenv("test_force_post_meta_es-forms-nonexistent-HR");
});

test('getOptionValueGroup returns an empty array when an option isn\'t set', function() {
	putenv("test_force_option_es-forms-nonexistent-HR=bool_false");

	expect($this->settingsHelper->getOptionValueGroup('nonexistent'))->toBe([]);

	putenv("test_force_option_es-forms-nonexistent-HR");
});

test('getOptionCheckboxValues returns an empty array when an option isn\'t set', function() {
	putenv("test_force_option_es-forms-nonexistent-HR=bool_false");

	expect($this->settingsHelper->getOptionCheckboxValues('nonexistent'))->toBe([]);

	putenv("test_force_option_es-forms-nonexistent-HR");
});

test('isCheckedOption returns correctly when option doesn\'t match key', function($key, $id, $formId, $return) {
	putenv("test_force_option_es-forms-{$id}-HR=notreally{$key}");

	expect($this->settingsHelper->isCheckedOption($key, $id))->toBe($return);

	putenv("test_force_option_es-forms-{$id}-HR");
})->with([
	['key', 'test1', 1, false],
]);

test('isCheckedOption returns correctly when option matches key', function($key, $id, $formId, $return) {
	putenv("test_force_option_es-forms-{$id}-HR={$key}");

	expect($this->settingsHelper->isCheckedOption($key, $id))->toBe($return);

	putenv("test_force_option_es-forms-{$id}-HR");
})->with([
	['key', 'test1', 1, true],
]);

test('getLocale calls apply_filters', function() {
	$localeClosure = function($locale) {
		return 'HRR';
	};

	add_filter('es_forms_set_locale', $localeClosure);
	Filters\expectApplied('es_forms_set_locale')->with('HR')->andReturn('HRR');

 	expect($this->settingsHelper->getLocale())->toBe('HRR');
	
	remove_filter('es_forms_set_locale', $localeClosure);
 });


 test('getIntegrationFieldsDetails returns correctly for input fields', function ($key, $type, $formFields, $formId, $additionalLabel, $return) {
	buildTestBlocks();

	putenv("test_force_post_meta_es-forms-{$key}-HR=bool_false");

	expect($this->settingsHelper->getIntegrationFieldsDetails($key, $type, $formFields, $formId, $additionalLabel))->toBe($return);

	putenv("test_force_post_meta_es-forms-{$key}-HR");

	destroyTestBlocks();
})->with('form markup for integration fields');

test('getIntegrationFieldsDetails returns correctly when custom styles are enabled ', function ($key, $type, $formFields, $formId, $additionalLabel, $return) {
	buildTestBlocks();

	putenv("test_force_post_meta_es-forms-{$key}-HR=bool_false");

	$styles = [
		'input' => [
			[
				'label' => 'Default',
				'value' => 'default'
			],
			[
				'label' => 'Custom Style',
				'value' => 'custom-style'
			],
		],
		'select' => [
			[
				'label' => 'Default',
				'value' => 'default'
			],
			[
				'label' => 'Custom Style',
				'value' => 'custom-style',
				'useCustom' => false, // This key can be used only on select, file and textarea and it removes the custom JS library from the component.
			],
		]
	];

	$styleOptionsClosure = function() use ($styles) {
		return $styles;
	};

	add_filter(FormsFilters::getBlockFilterName('field', 'styleOptions'), $styleOptionsClosure);
	Filters\expectApplied(FormsFilters::getBlockFilterName('field', 'styleOptions'))->with([])->andReturn($styles);

	expect($this->settingsHelper->getIntegrationFieldsDetails($key, $type, $formFields, $formId, $additionalLabel))->toBe($return);
	
	putenv("test_force_post_meta_es-forms-{$key}-HR");
	remove_filter(FormsFilters::getBlockFilterName('field', 'styleOptions'), $styleOptionsClosure);

	destroyTestBlocks();
})->with('form markup for integration fields with styles');

test('getIntegrationFieldsDetails returns correctly for submit fields', function ($key, $type, $formFields, $formId, $additionalLabel, $return) {
	buildTestBlocks();

	putenv("test_force_post_meta_es-forms-{$key}-HR=bool_false");

	expect($this->settingsHelper->getIntegrationFieldsDetails($key, $type, $formFields, $formId, $additionalLabel))->toBe($return);

	putenv("test_force_post_meta_es-forms-{$key}-HR");
	destroyTestBlocks();
})->with('form markup for submit fields');

test('getIntegrationFieldsDetails disables editing when a particular filter is used', function ($key, $type, $formFields, $formId, $additionalLabel, $return) {
	buildTestBlocks();

	putenv("test_force_post_meta_es-forms-{$key}-HR=bool_false");

	$styles = [
		'input' => [
			[
				'label' => 'Default',
				'value' => 'default'
			],
			[
				'label' => 'Custom Style',
				'value' => 'custom-style'
			],
		],
		'select' => [
			[
				'label' => 'Default',
				'value' => 'default'
			],
			[
				'label' => 'Custom Style',
				'value' => 'custom-style',
				'useCustom' => false, // This key can be used only on select, file and textarea and it removes the custom JS library from the component.
			],
		]
	];

	$styleOptionsClosure = function() use ($styles) {
		return $styles;
	};
	add_filter(FormsFilters::getBlockFilterName('field', 'styleOptions'), $styleOptionsClosure);

	$fieldSettingsIsEditableClosure = function () {
		return false;
	};
	add_filter(FormsFilters::getIntegrationFilterName('mailchimp', 'fieldsSettingsIsEditable'), $fieldSettingsIsEditableClosure);

	Filters\expectApplied(FormsFilters::getBlockFilterName('field', 'styleOptions'))->with([])->andReturn($styles);
	Filters\expectApplied(FormsFilters::getIntegrationFilterName('mailchimp', 'fieldsSettingsIsEditable'))->with('')->andReturn(false);

	expect($this->settingsHelper->getIntegrationFieldsDetails($key, $type, $formFields, $formId, $additionalLabel))->toBe($return);
	putenv("test_force_post_meta_es-forms-{$key}-HR");

	remove_filter(FormsFilters::getBlockFilterName('field', 'styleOptions'), $styleOptionsClosure);
	remove_filter(FormsFilters::getIntegrationFilterName('mailchimp', 'fieldsSettingsIsEditable'), $fieldSettingsIsEditableClosure);
	destroyTestBlocks();
})->with('form markup for integration fields with editing disabled');

test('getIntegrationFieldsDetails uses the formViewDetails filter properly to override settings', function($key, $type, $formFields, $formId, $additionalLabel, $return) {
	buildTestBlocks();
	putenv("test_force_post_meta_es-forms-{$key}-HR=bool_false");
	
	$filterValue = [
		'input-email' => [
			'desktop' => 4,
			'use' => false,
		],
		'input-id' => [
			'large' => 3,
		],
	];

	$fieldSettingsClosure = function () use ($filterValue) {
		return $filterValue;
	};
	add_filter(FormsFilters::getIntegrationFilterName('mailchimp', 'fieldsSettings'), $fieldSettingsClosure);
	Filters\expectApplied(FormsFilters::getIntegrationFilterName('mailchimp', 'fieldsSettings'))->with($formFields)->andReturn($filterValue);
	
	expect($this->settingsHelper->getIntegrationFieldsDetails($key, $type, $formFields, $formId, $additionalLabel))->toBe($return);
	
	putenv("test_force_post_meta_es-forms-{$key}-HR");
	remove_filter(FormsFilters::getIntegrationFilterName('mailchimp', 'fieldsSettings'), $fieldSettingsClosure);
	destroyTestBlocks();
})->with('form markup for integration fields when formViewDetails is used to override settings');

test('getIntegrationFieldsDetails handles an empty form', function($key, $type, $formFields, $formId, $additionalLabel, $return) {
	buildTestBlocks();
	putenv("test_force_post_meta_es-forms-{$key}-HR=bool_false");
	
	expect($this->settingsHelper->getIntegrationFieldsDetails($key, $type, $formFields, $formId, $additionalLabel))->toBe($return);
	
	putenv("test_force_post_meta_es-forms-{$key}-HR");
	destroyTestBlocks();
})->with([
	[
		'formtest1',
		'mailchimp',
		[
			[]
		],
		'1',
		[],
		[],
	],
]);

test('getIntegrationFieldsDetails handles hidden fields', function($key, $type, $formFields, $formId, $additionalLabel, $return) {
	buildTestBlocks();
	putenv("test_force_post_meta_es-forms-{$key}-HR=bool_false");
	
	expect($this->settingsHelper->getIntegrationFieldsDetails($key, $type, $formFields, $formId, $additionalLabel))->toBe($return);
	
	putenv("test_force_post_meta_es-forms-{$key}-HR");
	destroyTestBlocks();
})->with([
	[
		'formtest1',
		'mailchimp',
		[
			mockFormField('input', ['inputType' => 'hidden']),
		],
		'1',
		[],
		[],
	],
]);

test('getIntegrationFieldsDetails handles Greenhouse-specific fields', function($key, $type, $formFields, $formId, $additionalLabel, $return) {
	buildTestBlocks();
	putenv("test_force_post_meta_es-forms-{$key}-HR=bool_false");
	
	expect($this->settingsHelper->getIntegrationFieldsDetails($key, $type, $formFields, $formId, $additionalLabel))->toBe($return);
	
	putenv("test_force_post_meta_es-forms-{$key}-HR");
	destroyTestBlocks();
})->with('form markup for Greenhouse-specific integration fields');
