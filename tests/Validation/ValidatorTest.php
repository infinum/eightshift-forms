<?php

namespace Tests\Unit\Validation;

use Brain\Monkey;
use function Tests\setupMocks;

use EightshiftForms\Validation\Validator;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Validation\SettingsValidation;

class ValidatorMock extends Validator {
	public function __construct(LabelsInterface $labels) {
		parent::__construct($labels);
	}

	public function getOptionValue(string $name): string {
		switch($name) {
			case 'validation-patterns': 
				return "Numbers : [0-9]+\nLetters : [a-zA-Z]+\nInvalid-value";
			default:
				return $name;
		}
	}
};


/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();
	putenv('TEST=true');
	$labels = new Labels();
	$this->validator = new ValidatorMock($labels);
});

afterEach(function() {
	putenv('test_force_option_eightshift_forms_force_mimetype_from_fs');
	putenv('TEST');
	unset($this->validator);
	unset($this->labels);
	Monkey\tearDown();
});

test('Validator skips validation on single submit', function() {
	expect($this->validator->validate(['es-form-single-submit' => true]))->toBe([]);
});

test('getValidationPatterns returns local and user patterns', function() {
	$preparedValidationPatterns = [];

	foreach(SettingsValidation::VALIDATION_PATTERNS as $key => $value) {
		$preparedValidationPatterns[] = [
			'value' => $value,
			'label' => $key,
		];
	}

	$preparedValidationPatterns[] = [
		'value' => '[0-9]+',
		'label' => 'Numbers'
	];

	$preparedValidationPatterns[] = [
		'value' => '[a-zA-Z]+',
		'label' => 'Letters'
	];

	expect($this->validator->getValidationPatterns())->toBe(
		array_merge(
			[['value' => '', 'label' => '---']],
			$preparedValidationPatterns,
		));
});

test('getValidationPatternName returns correct name for user pattern', function() {
	expect($this->validator->getValidationPatternName('[0-9]+'))->toBe('Numbers');
});

test('getValidationPatternName returns pattern for non-existent pattern', function() {
	expect($this->validator->getValidationPatternName('IAmNotARegex'))->toBe('IAmNotARegex');
});

test('getValidationPatternName returns correct name for local patterns', function() {
	foreach (SettingsValidation::VALIDATION_PATTERNS as $key => $value) {
		expect($this->validator->getValidationPatternName($value))->toBe($key);
		break;
	}
});

test('getValidationPattern returns correct pattern for user patterns', function($name, $pattern) {
	expect($this->validator->getValidationPattern($name))->toBe($pattern);
})->with([
	['Numbers', '[0-9]+'],
	['Letters', '[a-zA-Z]+'],
]);

test('getValidationPattern returns correct pattern for local patterns', function() {
	foreach (SettingsValidation::VALIDATION_PATTERNS as $key => $value) {
		expect($this->validator->getValidationPattern($key))->toBe($value);
		break;
	}
});

test('getValidationPattern returns name for non-existent pattern', function() {
	expect($this->validator->getValidationPattern('I do not exist'))->toBe('I do not exist');
});

test('validate yields no false positives for basic validation', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with('wrongly filled fields with basic validation');

test('validate yields no false negatives for basic validation', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with('correctly filled fields with basic validation');

test('validate yields correct values for URL validation', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with('one correctly filled URL field and one incorrectly filled URL field');

test('validate yields correct values for email validation', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with('one correctly filled email field and one incorrectly filled email field');

test('validate yields correct values for custom pattern', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with('custom pattern validation');

test('validate yields valid params for checkboxes', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with('checkbox validation');

test('validate yields valid results for files', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with('file validation');


test('validate denies unuploaded files when a particular option is set', function ($expected, $params, $files, $formId, $formData) {
	putenv('test_force_option_eightshift_forms_force_mimetype_from_fs=true');
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with('file validation with mimetype checks');

