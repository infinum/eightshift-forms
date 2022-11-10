<?php

namespace Tests\Unit\Validation;

use Brain\Monkey;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Validation\SettingsValidation;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Labels\LabelsInterface;

use function Tests\setupMocks;

class SettingsValidationMock extends SettingsValidation {

	public function __construct(LabelsInterface $labels) {
		parent::__construct($labels);
	}
};

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$labels = new Labels();

	$this->validationSettings = new SettingsValidationMock($labels);
});

afterAll(function() {
	Monkey\tearDown();
});

test('Register method will call sidebar hook', function () {
	$this->validationSettings->register();

	$this->assertSame(10, \has_filter(SettingsValidationMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Validation\SettingsValidationMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsValidationMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Validation\SettingsValidationMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
});

test('getSettingsData returns expected values', function($formId, $expected) {
	expect($this->validationSettings->getSettingsData($formId))->toBe($expected);
})->with([
	[
		1,
		[
			[
				'component' => 'intro',
				'introTitle' => 'Validation messages',
			],
			[
				'component' => 'input',
				'inputName' => 'es-forms-mailerSuccess-HR',
				'inputId' => 'es-forms-mailerSuccess-HR',
				'inputFieldLabel' => 'MailerSuccess',
				'inputPlaceholder' => 'E-mail was sent successfully.',
				'inputValue' => 'es-forms-mailerSuccess-HR',
			]
		]
	]
]);

test('getSettingsGlobalData returns expected values', function() {
	$expected = [
		[
			'component' => 'intro',
			'introTitle' => 'Validation',
			'introSubtitle' => 'In these settings, you can change all options regarding form validation.'
		],
		'tabsContent' => [
			[
				'component' => 'tab',
				'tabLabel' => \__('Patterns', 'eightshift-forms'),
				'tabContent' => [
					'component' => 'textarea',
					'textareaId' => 'es-forms-validation-patterns-HR',
					'textareaIsMonospace' => true,
					'textareaFieldLabel' => 'Validation patterns',
					'textareaFieldHelp' => " Custom validation patterns can be defined in this field so it can be selected inside the Form editor.<br /> If you need help with writing regular expressions (<i>regex</i>), <a href='https://regex101.com/' target='_blank' rel='noopener noreferrer'>click here</a>.<br /><br /> Validation patterns should be provided each in its own line and in the following format:<br /> <code>pattern-name : pattern </code><br /><br /> Here are some examples: <ul> <li><code>MM/DD : ^(1[0-2]|0[1-9])\/(3[01]|[12][0-9]|0[1-9])$</code></li><li><code>DD/MM : ^(3[01]|[12][0-9]|0[1-9])\/(1[0-2]|0[1-9])$</code></li> </ul>",
					'textareaValue' => 'es-forms-validation-patterns-HR',
				],
			],
			[
				'component' => 'tab',
				'tabLabel' => \__('Messages', 'eightshift-forms'),
				'tabContent' => [
					[
						'component' => 'input',
						'inputName' => 'es-forms-mailerSuccess-HR',
						'inputId' => 'es-forms-mailerSuccess-HR',
						'inputFieldLabel' => 'MailerSuccess',
						'inputPlaceholder' => 'E-mail was sent successfully.',
						'inputValue' => 'es-forms-mailerSuccess-HR',
					],
				],
			],
		],
	];

	expect($this->validationSettings->getSettingsGlobalData())->toBe($expected);
});
