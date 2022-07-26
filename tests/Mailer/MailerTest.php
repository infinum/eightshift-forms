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
})->with('various mailer calls');

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
