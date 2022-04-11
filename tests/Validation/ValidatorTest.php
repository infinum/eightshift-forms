<?php

namespace Tests\Unit\Validation;

use Brain\Monkey;
use Brain\Monkey\Functions;

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

	$labels = new Labels();
	$this->validator = new ValidatorMock($labels);
});

afterAll(function() {
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
})->with([
	[
		[
			'required-text-input' => 'This field is required.',
		], // expected
		[
			'required-text-input' => [
				'name' => 'required-text-input',
				'value' => '',
				'type' => 'text'
			],
		], // params
		[], //files
		'', // form ID
		[ // form data
			[
				'component' => 'input',
				'inputName' => 'required-text-input',
				'inputId' => 'required-text-input',
				'inputIsRequired' => true
			],
		],  
	],
	// ------ //
	[
		[
			'text-min-length' => 'This field value has less characters than expected. We expect minimum 6 characters.',
		],
		[
			'text-min-length' => [
				'name' => 'text-min-length',
				'value' => ':(',
				'type' => 'text'
			],
		],
		[], //files
		'', // form ID,
		[
			[
				'component' => 'input',
				'inputName' => 'text-min-length',
				'inputId' => 'text-min-length',
				'inputIsRequired' => true,
				'inputMinLength' => 6,
			],
		]
	],
	// ------ //
	[
		[
			'text-max-length' => 'This field value has more characters than expected. We expect maximum 6 characters.',
		],
		[
			'text-max-length' => [
				'name' => 'text-max-length',
				'value' => 'Oh no, this string is too large.',
				'type' => 'text'
			],
		],
		[],
		'',
		[
			[
				'component' => 'input',
				'inputName' => 'text-max-length',
				'inputId' => 'text-max-length',
				'inputIsRequired' => true,
				'inputMaxLength' => 6,
			],
		]
	],
	// ------ //
	[
		[
			'number-passed-string' => 'This field should only contain numbers.',
		],
		[
			'number-passed-string' => [
				'name' => 'number-passed-string',
				'value' => 'I am not a number.',
				'type' => 'number'
			],
		],
		[], 
		'',
		[
			[
				'component' => 'input',
				'inputType' => 'number',
				'inputIsNumber' => true,
				'inputName' => 'number-passed-string',
				'inputId' => 'number-passed-string',
			]
		],
	],
	// ------ //
	[
		[
			'number-passed-string' => 'This field value has less characters than expected. We expect minimum 6 characters.',
		],
		[
			'number-passed-string' => [
				'name' => 'number-passed-string',
				'value' => '12345',
				'type' => 'number'
			],
		],
		[], 
		'',
		[
			[
				'component' => 'input',
				'inputType' => 'number',
				'inputIsNumber' => true,
				'inputName' => 'number-passed-string',
				'inputId' => 'number-passed-string',
				'inputMinLength' => 6
			]
		],
	],
	// ------ //
	[
		[
			'number-max-length' => 'This field value has more characters than expected. We expect maximum 3 characters.',
		],
		[
			'number-max-length' => [
				'name' => 'number-max-length',
				'value' => '1234',
				'type' => 'number'
			],
		],
		[],
		'',
		[
			[
				'component' => 'input',
				'inputType' => 'number',
				'inputIsNumber' => true,
				'inputName' => 'number-max-length',
				'inputId' => 'number-max-length',
				'inputMaxLength' => 3,
			],
		]
	],
	// ------ //
	[
		[
			'textarea' => 'This field is required.',
		],
		[
			'textarea' => [
				'name' => 'textarea',
				'value' => '',
				'type' => 'textarea'
			],
		],
		[],
		'',
		[
			[
				'component' => 'textarea',
				'textareaId' => 'textarea',
				'textareaName' => 'textarea',
				'textareaIsRequired' => true,
			],
		]
	],
	// ------ //
	[
		[],
		[
			'nonexistent-field' => []
		],
		[],
		'', 
		[
			[
				'component' => 'textarea',
				'textareaId' => 'textarea',
				'textareaName' => 'textarea',
			]
		]
	],
]);

test('validate yields no false negatives for basic validation', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with([
	[
		[
		], // expected
		[
			'required-text-input' => [
				'name' => 'required-text-input',
				'value' => 'I have some input.',
				'type' => 'text'
			],
		], // params
		[], //files
		'', // form ID
		[ // form data
			[
				'component' => 'input',
				'inputName' => 'required-text-input',
				'inputId' => 'required-text-input',
				'inputIsRequired' => true
			],
		],  
	],
	// ------ //
	[
		[
		],
		[
			'text-min-length' => [
				'name' => 'text-min-length',
				'value' => 'This has more than six chars.',
				'type' => 'text'
			],
		],
		[], //files
		'', // form ID,
		[
			[
				'component' => 'input',
				'inputName' => 'text-min-length',
				'inputId' => 'text-min-length',
				'inputIsRequired' => true,
				'inputMinLength' => 6,
			],
		]
	],
	// ------ //
	[
		[
		],
		[
			'text-max-length' => [
				'name' => 'text-max-length',
				'value' => 'works',
				'type' => 'text'
			],
		],
		[],
		'',
		[
			[
				'component' => 'input',
				'inputName' => 'text-max-length',
				'inputId' => 'text-max-length',
				'inputIsRequired' => true,
				'inputMaxLength' => 6,
			],
		]
	],
	// ------ //
	[
		[
		],
		[
			'number-passed-string' => [
				'name' => 'number-passed-string',
				'value' => '42',
				'type' => 'number'
			],
		],
		[], 
		'',
		[
			[
				'component' => 'input',
				'inputType' => 'number',
				'inputIsNumber' => true,
				'inputName' => 'number-passed-string',
				'inputId' => 'number-passed-string',
			]
		],
	],
	// ------ //
	[
		[
		],
		[
			'number-passed-string' => [
				'name' => 'number-passed-string',
				'value' => '123456',
				'type' => 'number'
			],
		],
		[], 
		'',
		[
			[
				'component' => 'input',
				'inputType' => 'number',
				'inputIsNumber' => true,
				'inputName' => 'number-passed-string',
				'inputId' => 'number-passed-string',
				'inputMinLength' => 6
			]
		],
	],
	// ------ //
	[
		[
		],
		[
			'number-max-length' => [
				'name' => 'number-max-length',
				'value' => '123',
				'type' => 'number'
			],
		],
		[],
		'',
		[
			[
				'component' => 'input',
				'inputType' => 'number',
				'inputIsNumber' => true,
				'inputName' => 'number-max-length',
				'inputId' => 'number-max-length',
				'inputMaxLength' => 3,
			],
		]
	],
	// ------ //
	[
		[
		],
		[
			'textarea' => [
				'name' => 'textarea',
				'value' => 'I have content.',
				'type' => 'textarea'
			],
		],
		[],
		'',
		[
			[
				'component' => 'textarea',
				'textareaId' => 'textarea',
				'textareaName' => 'textarea',
				'textareaIsRequired' => true,
			],
		]
	]
]);