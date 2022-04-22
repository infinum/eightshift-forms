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
	putenv('TEST=true');
	$labels = new Labels();
	$this->validator = new ValidatorMock($labels);
});

afterAll(function() {
	Monkey\tearDown();
});

afterEach(function() {
	putenv('test_force_option_eightshift_forms_force_mimetype_from_fs');
	putenv('TEST');
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
			[],
			[
				'component' => '',
				'textareaId' => 'noComponentName',
			]
		]
	]
]);

test('validate yields correct values for URL validation', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with([
	[
		[
			'url-is-invalid' => 'This URL is not valid.',
		], // expected
		[
			'url-is-valid' => [
				'name' => 'url-is-valid',
				'value' => 'https://infinum.com',
				'type' => 'text'
			],
			'url-is-invalid' => [
				'name' => 'url-is-invalid',
				'value' => 'https,,,infinum.com',
				'type' => 'text'
			],
		], // params
		[], // files
		'', // form ID
		[ // form data
			[
				'component' => 'input',
				'inputName' => 'url-is-valid',
				'inputId' => 'url-is-valid',
				'inputIsRequired' => true,
				'inputIsUrl' => true,
			],
			[
				'component' => 'input',
				'inputName' => 'url-is-invalid',
				'inputId' => 'url-is-invalid',
				'inputIsRequired' => true,
				'inputIsUrl' => true,
			],
		],  
	],
]);

test('validate yields correct values for email validation', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with([
	[
		[
			'email-is-invalid' => 'This e-mail is not valid.',
		], // expected
		[
			'email-is-valid' => [
				'name' => 'email-is-valid',
				'value' => 'i.am.valid@example.com',
				'type' => 'text'
			],
			'email-is-invalid' => [
				'name' => 'email-is-invalid',
				'value' => 'this-is-not-really-an-email-i-mean-technically-it-could-be-but-irl-its-not',
				'type' => 'text'
			],
		], // params
		[], // files
		'', // form ID
		[ // form data
			[
				'component' => 'input',
				'inputName' => 'email-is-valid',
				'inputId' => 'email-is-valid',
				'inputIsRequired' => true,
				'inputIsEmail' => true,
			],
			[
				'component' => 'input',
				'inputName' => 'email-is-invalid',
				'inputId' => 'email-is-invalid',
				'inputIsRequired' => true,
				'inputIsEmail' => true,
			],
		],  
	],
]);

test('validate yields correct values for custom pattern', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with([
	[
		[
			'pattern-is-invalid' => 'This field doesn\'t satisfy the validation pattern: Test\\s[0-9]{1,3}.',
			'pattern-is-invalid2' => 'This field doesn\'t satisfy the validation pattern: \\s[0-9]{1,3}.',
		], // expected
		[
			'pattern-is-valid' => [
				'name' => 'email-is-valid',
				'value' => 'Test 123',
				'type' => 'text'
			],
			'pattern-is-invalid' => [
				'name' => 'Test',
				'value' => 'Test 1234',
				'type' => 'text'
			],
			'pattern-is-invalid2' => [
				'name' => 'Test',
				'value' => 'Test 1234',
				'type' => 'text'
			],
		], // params
		[], // files
		'', // form ID
		[ // form data
			[
				'component' => 'input',
				'inputName' => 'pattern-is-valid',
				'inputId' => 'pattern-is-valid',
				'inputIsRequired' => true,
				'inputValidationPattern' => 'Test\\s[0-9]{1,3}',
			],
			[
				'component' => 'input',
				'inputName' => 'pattern-is-invalid',
				'inputId' => 'pattern-is-invalid',
				'inputIsRequired' => true,
				'inputValidationPattern' => 'Test\\s[0-9]{1,3}',
			],
			[
				'component' => 'input',
				'inputName' => 'pattern-is-invalid2',
				'inputId' => 'pattern-is-invalid2',
				'inputIsRequired' => true,
				'inputValidationPattern' => '\\s[0-9]{1,3}',
			],
		],  
	],
]);

test('validate yields valid params for checkboxes', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with([
	[
		[
			'checkboxes-have-the-wrong-count' => 'This field is required, with at least 2 items selected.',
		], // expected
		[
			'checkboxes-are-valid' => [
				'name' => 'checkboxes-are-valid',
				'value' => 'c1, c2',
				'type' => 'text'
			],
			'checkboxes-have-the-wrong-count' => [
				'name' => 'checkboxes-have-the-wrong-count',
				'value' => 'c1',
				'type' => 'text'
			],
		], // params
		[], // files
		'', // form ID
		[ // form data
			[
				'component' => 'checkboxes',
				'checkboxesName' => 'checkboxes-are-valid',
				'checkboxesId' => 'checkboxes-are-valid',
				'checkboxesIsRequired' => true,
				'checkboxesIsRequiredCount' => 2,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => 'c1',
						'checkboxValue' => 'c1'
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => 'c2',
						'checkboxValue' => 'c2'
					]
				]
			],
			[
				'component' => 'checkboxes',
				'checkboxesName' => 'checkboxes-have-the-wrong-count',
				'checkboxesId' => 'checkboxes-have-the-wrong-count',
				'checkboxesIsRequired' => true,
				'checkboxesIsRequiredCount' => 2,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => 'c1',
						'checkboxValue' => 'c1'
					],
					[
						'component' => 'checkbox',
						'checkboxLabel' => 'c2',
						'checkboxValue' => 'c2'
					]
				]
			],
		],  
	],
]);

test('validate yields valid results for files', function ($expected, $params, $files, $formId, $formData) {
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with([
	[
		[
			'file-wrong-type' => 'The file type is not supported. Only .pdf are allowed.',
			'file-too-small' => 'The file is smaller than allowed. Minimum file size is 100 kB.',
			'file-too-large' => 'The file is larger than allowed. Maximum file size is 400 kB.',
			'file-wrong-mimetype' => 'The file seems to be corrupted. Only .pdf are allowed.',
			'file-wrong-mimetype2' => 'The file seems to be corrupted. Only .jpg are allowed.',
		], // expected
		[], // params
		[
			'file-wrong-type' => [
				'name' => ['wrong.jpg'],
				'type' => ['image/jpeg'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [34798]
			],
			'file-too-small' => [
				'name' => ['wrong.pdf'],
				'type' => ['application/pdf'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [1]
			],
			'file-too-large' => [
				'name' => ['wrong.pdf'],
				'type' => ['application/pdf'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [500000]
			],
			'file-wrong-mimetype' => [
				'name' => ['wrong.pdf'],
				'type' => ['image/jpeg'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [300000]
			],
			'file-wrong-mimetype2' => [
				'name' => ['wrong.jpg'],
				'type' => ['application/pdf'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [300000]
			],
			'file-valid' => [
				'name' => ['right.pdf'],
				'type' => ['application/pdf'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [300000]
			],
			'file-no-validation' => [
				'name' => ['whocares.pdf'],
				'type' => ['image/jpeg'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [300000]
			]
		], // files
		'', // form ID
		[ // form data
			[
				'component' => 'file',
				'fileName' => 'file-wrong-type',
				'fileId' => 'file-wrong-type',
				'fileAccept' => '.pdf',
				'fileIsRequired' => true,
			],
			[
				'component' => 'file',
				'fileName' => 'file-too-small',
				'fileId' => 'file-too-small',
				'fileAccept' => '.pdf',
				'fileMinSize' => 100,
				'fileIsRequired' => true,
			],
			[
				'component' => 'file',
				'fileName' => 'file-too-large',
				'fileId' => 'file-too-large',
				'fileAccept' => '.pdf',
				'fileMinSize' => 100,
				'fileMaxSize' => 400,
				'fileIsRequired' => true,
			],
			[
				'component' => 'file',
				'fileName' => 'file-wrong-mimetype',
				'fileId' => 'file-wrong-mimetype',
				'fileAccept' => '.pdf',
				'fileMinSize' => 100,
				'fileMaxSize' => 400,
				'fileIsRequired' => true,
			],
			[
				'component' => 'file',
				'fileName' => 'file-wrong-mimetype2',
				'fileId' => 'file-wrong-mimetype2',
				'fileAccept' => '.jpg',
				'fileMinSize' => 100,
				'fileMaxSize' => 400,
				'fileIsRequired' => true,
			],
			[
				'component' => 'file',
				'fileName' => 'file-valid',
				'fileId' => 'file-valid',
				'fileAccept' => '.pdf',
				'fileMinSize' => 100,
				'fileMaxSize' => 400,
				'fileIsRequired' => true,
			],
			[
				'component' => 'file',
				'fileName' => 'file-no-validation',
				'fileId' => 'file-no-validation',
			],
		],  
	],
]);


test('validate denies unuploaded files when a particular option is set', function ($expected, $params, $files, $formId, $formData) {
	putenv('test_force_option_eightshift_forms_force_mimetype_from_fs=true');
	expect($this->validator->validate($params, $files, $formId, $formData))->toBe($expected);
})->with([
	[
		[
			'file-not-uploaded' => 'The file seems to be corrupted. Only .pdf are allowed.',
			'file-missing' => 'The file seems to be corrupted. Only .pdf are allowed.',
		], // expected
		[], // params
		[
			'file-not-uploaded' => [
				'name' => ['wrong.pdf'],
				'type' => ['application/pdf'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [300000]
			],
			'file-missing' => [
				'name' => ['wrong.pdf'],
				'type' => ['application/executable'],
				'tmp_name' => ['/tmp/i_hopefully_dont_exist'],
				'error' => [0],
				'size' => [300000]
			],
			'file-no-validation' => [
				'name' => ['whocares.pdf'],
				'type' => ['image/jpeg'],
				'tmp_name' => [''],
				'error' => [0],
				'size' => [300000]
			]
		], // files
		'', // form ID
		[ // form data
			[
				'component' => 'file',
				'fileName' => 'file-not-uploaded',
				'fileId' => 'file-not-uploaded',
				'fileAccept' => '.pdf',
				'fileMinSize' => 100,
				'fileMaxSize' => 400,
				'fileIsRequired' => true,
			],
			[
				'component' => 'file',
				'fileName' => 'file-missing',
				'fileId' => 'file-missing',
				'fileAccept' => '.pdf',
				'fileMinSize' => 100,
				'fileMaxSize' => 400,
				'fileIsRequired' => true,
			],
			[
				'component' => 'file',
				'fileName' => 'file-no-validation',
				'fileId' => 'file-no-validation',
			],
		],  
	],
]);
