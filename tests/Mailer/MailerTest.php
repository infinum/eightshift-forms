<?php

namespace Tests\Unit\Mailer;

use Brain\Monkey;
use EightshiftForms\Mailer\Mailer;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$this->mailer = new Mailer;
});

afterAll(function() {
	Monkey\tearDown();
});

afterEach(function() {
	putenv('test_wp_mail_last_call');
	putenv('test_force_post_meta_es-forms-mailer-sender-email');
	putenv('test_force_post_meta_es-forms-mailer-sender-name');
});

test('Mailer calls wp_mail correctly', function ($expected, $formId, $to, $subject, $template = '', $files = [], $fields = []) {
	expect($this->mailer->sendFormEmail($formId, $to, $subject, $template, $files, $fields))->toBe($expected['return']);
	expect(getenv('test_wp_mail_last_call'))->toBe($expected['wp_mail_call']);
})->with([
	[ // Invalid call
		[
			'return' => false,
			'wp_mail_call' => '{"to":"","subject":"","message":"","headers":["Content-Type: text\/html; charset=UTF-8","From: es-forms-mailer-sender-name-HR <es-forms-mailer-sender-email-HR>"],"attachments":[]}'
		],
		'', '', '',
	],
	[ // Email only with subject
		[
			'return' => true,
			'wp_mail_call' => '{"to":"some.pretty.valid.email@example.com","subject":"My subject","message":"","headers":["Content-Type: text\/html; charset=UTF-8","From: es-forms-mailer-sender-name-HR <es-forms-mailer-sender-email-HR>"],"attachments":[]}'
		],
		1, 'some.pretty.valid.email@example.com', 'My subject'
	],
	[ // Templates
		[
			'return' => true,
			'wp_mail_call' => '{"to":"some.pretty.valid.email@example.com","subject":"Your form submission is here","message":"Hello John Smith! You said that your favourite band is Arctic Monkeys and indicated that it is 1 you are basic.","headers":["Content-Type: text\/html; charset=UTF-8","From: es-forms-mailer-sender-name-HR <es-forms-mailer-sender-email-HR>"],"attachments":[]}',
		],
		1,
		'some.pretty.valid.email@example.com', 
		'Your form submission is here',
		'Hello {name}! You said that your favourite band is {favouriteBand} and indicated that it is {areYouBasicCheckbox} you are basic.',
		[
			[] // Tests that we don't try to access undefined indexes on this array.
		],
		[
			[
				'name' => 'name',
				'value' => 'John Smith',
			],
			[
				'name' => 'favouriteBand',
				'value' => 'Arctic Monkeys',
			],
			[
				'name' => 'areYouBasicCheckbox',
				'value' => true,
			],
		]
	],
	[ // Attachments and missing template parameters
		[
			'return' => true,
			'wp_mail_call' => '{"to":"some.pretty.valid.email@example.com","subject":"A PDF of your form submission is here","message":"Hello John Smith! You said that your favourite band is Fleetwood Mac and indicated that it is {areYouBasicCheckbox} you are basic. Find a copy of your submission attached.","headers":["Content-Type: text\/html; charset=UTF-8","From: es-forms-mailer-sender-name-HR <es-forms-mailer-sender-email-HR>"],"attachments":["\/tmp\/formuploads\/test.pdf"]}',
		],
		1,
		'some.pretty.valid.email@example.com', 
		'A PDF of your form submission is here',
		'Hello {name}! You said that your favourite band is {favouriteBand} and indicated that it is {areYouBasicCheckbox} you are basic. Find a copy of your submission attached.',
		[
			[
				[
					'path' => '/tmp/formuploads/test.pdf',
				],
				[
					'path' => '', // Tests that an empty path is skipped when constructing the wp_mail call.
				]
			],
		],
		[
			[
				'name' => 'name',
				'value' => 'John Smith',
			],
			[
				'name' => 'favouriteBand',
				'value' => 'Fleetwood Mac',
			],
		]
	]
]);

test('getFrom returns correctly when sender email is not configured', function ($expected, $formId, $to, $subject, $template = '', $files = [], $fields = []) {
	putenv('test_force_post_meta_es-forms-mailer-sender-email-HR=unset');
	expect($this->mailer->sendFormEmail($formId, $to, $subject, $template, $files, $fields))->toBe($expected['return']);
	expect(getenv('test_wp_mail_last_call'))->toBe($expected['wp_mail_call']);
})->with([
	[
		[
			'return' => false,
			'wp_mail_call' => '{"to":"","subject":"","message":"","headers":["Content-Type: text\/html; charset=UTF-8",""],"attachments":[]}'
		],
		'', '', '',
	],
]);

test('getFrom returns correctly when sender name is not configured', function ($expected, $formId, $to, $subject, $template = '', $files = [], $fields = []) {
	putenv('test_force_post_meta_es-forms-mailer-sender-email-HR=sender@example.com');
	putenv('test_force_post_meta_es-forms-mailer-sender-name-HR=unset');
	expect($this->mailer->sendFormEmail($formId, $to, $subject, $template, $files, $fields))->toBe($expected['return']);
	expect(getenv('test_wp_mail_last_call'))->toBe($expected['wp_mail_call']);
})->with([
	[
		[
			'return' => false,
			'wp_mail_call' => '{"to":"","subject":"","message":"","headers":["Content-Type: text\/html; charset=UTF-8","From: sender@example.com"],"attachments":[]}'
		],
		'', '', '',
	],
]);