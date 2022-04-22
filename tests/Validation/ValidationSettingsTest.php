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

	$this->assertSame(10, \has_filter(SettingsValidationMock::FILTER_SETTINGS_SIDEBAR_NAME, 'Tests\Unit\Validation\SettingsValidationMock->getSettingsSidebar()'), 'The callback getSettingsSidebar should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsValidationMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Validation\SettingsValidationMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, \has_filter(SettingsValidationMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Validation\SettingsValidationMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
});

test('getSettingsSidebar returns expected values', function () {
	expect($this->validationSettings->getSettingsSidebar())->toBe([
		'label' => 'Validation',
		'value' => 'validation',
		'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5.25 9.813 3.818 3.937 8.182-9" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M15.579 2.621A9.21 9.21 0 0 0 10 .75a9.25 9.25 0 1 0 8.758 6.266" stroke="#29A3A3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
	]);
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
			'introTitle' => 'Form validation',
		],
		[
			'component' => 'textarea',
			'textareaId' => 'es-forms-validation-patterns-HR',
			'textareaIsMonospace' => true,
			'textareaFieldLabel' => 'Validation patterns',
			'textareaFieldHelp' => Helper::minifyString("
				These patterns can be selected inside the Form editor.
				<br /> <br />
				Each pattern should be in its own line and in the following format:
				<br />
				<code>pattern-name : pattern </code>
				<br /> <br />
				If you need help with writing regular expressions (<i>regex</i>), <a href='https://regex101.com/' target='_blank' rel='noopener noreferrer'>click here</a>.
				<br /> <br /> <br />
				Use these patterns as an example:
				<ul>
				<li><code>MM/DD : ^(1[0-2]|0[1-9])\/(3[01]|[12][0-9]|0[1-9])$</code></li><li><code>DD/MM : ^(3[01]|[12][0-9]|0[1-9])\/(1[0-2]|0[1-9])$</code></li>
				</ul>"),
			'textareaValue' => 'es-forms-validation-patterns-HR'
		],
		[
			'component' => 'divider'
		],
		[
			'component' => 'intro',
			'introTitle' => 'Validation messages',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-submitWpError-HR',
			'inputId' => 'es-forms-submitWpError-HR',
			'inputFieldLabel' => 'SubmitWpError',
			'inputPlaceholder' => 'Something went wrong while submitting your form. Please try again.',
			'inputValue' => 'es-forms-submitWpError-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationRequired-HR',
			'inputId' => 'es-forms-validationRequired-HR',
			'inputFieldLabel' => 'ValidationRequired',
			'inputPlaceholder' => 'This field is required.',
			'inputValue' => 'es-forms-validationRequired-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationRequiredCount-HR',
			'inputId' => 'es-forms-validationRequiredCount-HR',
			'inputFieldLabel' => 'ValidationRequiredCount',
			'inputPlaceholder' => 'This field is required, with at least %s items selected.',
			'inputValue' => 'es-forms-validationRequiredCount-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationEmail-HR',
			'inputId' => 'es-forms-validationEmail-HR',
			'inputFieldLabel' => 'ValidationEmail',
			'inputPlaceholder' => 'This e-mail is not valid.',
			'inputValue' => 'es-forms-validationEmail-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationUrl-HR',
			'inputId' => 'es-forms-validationUrl-HR',
			'inputFieldLabel' => 'ValidationUrl',
			'inputPlaceholder' => 'This URL is not valid.',
			'inputValue' => 'es-forms-validationUrl-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationMinLength-HR',
			'inputId' => 'es-forms-validationMinLength-HR',
			'inputFieldLabel' => 'ValidationMinLength',
			'inputPlaceholder' => 'This field value has less characters than expected. We expect minimum %s characters.',
			'inputValue' => 'es-forms-validationMinLength-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationMaxLength-HR',
			'inputId' => 'es-forms-validationMaxLength-HR',
			'inputFieldLabel' => 'ValidationMaxLength',
			'inputPlaceholder' => 'This field value has more characters than expected. We expect maximum %s characters.',
			'inputValue' => 'es-forms-validationMaxLength-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationNumber-HR',
			'inputId' => 'es-forms-validationNumber-HR',
			'inputFieldLabel' => 'ValidationNumber',
			'inputPlaceholder' => 'This field should only contain numbers.',
			'inputValue' => 'es-forms-validationNumber-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationPattern-HR',
			'inputId' => 'es-forms-validationPattern-HR',
			'inputFieldLabel' => 'ValidationPattern',
			'inputPlaceholder' => 'This field doesn\'t satisfy the validation pattern: %s.',
			'inputValue' => 'es-forms-validationPattern-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationAccept-HR',
			'inputId' => 'es-forms-validationAccept-HR',
			'inputFieldLabel' => 'ValidationAccept',
			'inputPlaceholder' => 'The file type is not supported. Only %s are allowed.',
			'inputValue' => 'es-forms-validationAccept-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationAcceptMime-HR',
			'inputId' => 'es-forms-validationAcceptMime-HR',
			'inputFieldLabel' => 'ValidationAcceptMime',
			'inputPlaceholder' => 'The file seems to be corrupted. Only %s are allowed.',
			'inputValue' => 'es-forms-validationAcceptMime-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationMinSize-HR',
			'inputId' => 'es-forms-validationMinSize-HR',
			'inputFieldLabel' => 'ValidationMinSize',
			'inputPlaceholder' => 'The file is smaller than allowed. Minimum file size is %s kB.',
			'inputValue' => 'es-forms-validationMinSize-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-validationMaxSize-HR',
			'inputId' => 'es-forms-validationMaxSize-HR',
			'inputFieldLabel' => 'ValidationMaxSize',
			'inputPlaceholder' => 'The file is larger than allowed. Maximum file size is %s kB.',
			'inputValue' => 'es-forms-validationMaxSize-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-mailerSuccessNoSend-HR',
			'inputId' => 'es-forms-mailerSuccessNoSend-HR',
			'inputFieldLabel' => 'MailerSuccessNoSend',
			'inputPlaceholder' => 'E-mail was sent successfully.',
			'inputValue' => 'es-forms-mailerSuccessNoSend-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-mailerErrorSettingsMissing-HR',
			'inputId' => 'es-forms-mailerErrorSettingsMissing-HR',
			'inputFieldLabel' => 'MailerErrorSettingsMissing',
			'inputPlaceholder' => 'Form settings are not configured correctly. Please try again.',
			'inputValue' => 'es-forms-mailerErrorSettingsMissing-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-mailerErrorEmailSend-HR',
			'inputId' => 'es-forms-mailerErrorEmailSend-HR',
			'inputFieldLabel' => 'MailerErrorEmailSend',
			'inputPlaceholder' => 'E-mail was not sent due to an unknown issue. Please try again.',
			'inputValue' => 'es-forms-mailerErrorEmailSend-HR',
		],
		[
			'component' => 'input',
			'inputName' => 'es-forms-mailerErrorEmailConfirmationSend-HR',
			'inputId' => 'es-forms-mailerErrorEmailConfirmationSend-HR',
			'inputFieldLabel' => 'MailerErrorEmailConfirmationSend',
			'inputPlaceholder' => 'Confirmation e-mail was not sent due to unknown issue. Please try again.',
			'inputValue' => 'es-forms-mailerErrorEmailConfirmationSend-HR',
		]
	];
	expect($this->validationSettings->getSettingsGlobalData())->toBe($expected);
});
