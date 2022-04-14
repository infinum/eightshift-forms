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
	putenv('test_wp_mail_last_call');
});

test('Mailer calls wp_mail correctly', function ($expected, $formId, $to, $subject, $template = '', $files = [], $fields = []) {
	expect($this->mailer->sendFormEmail($formId, $to, $subject, $template, $fields))->toBe($expected['return']);
	expect(getenv('test_wp_mail_last_call'))->toBe($expected['wp_mail_call']);
})->with([
	[
		[
			'return' => false,
			'wp_mail_call' => '{"to":"","subject":"","message":"","headers":["Content-Type: text\/html; charset=UTF-8","From: es-forms-mailer-sender-name-HR <es-forms-mailer-sender-email-HR>"],"attachments":[]}'
		],
		'', '', '',
	]
]);