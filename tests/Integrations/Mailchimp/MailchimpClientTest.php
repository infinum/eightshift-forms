<?php

namespace Tests\Unit\Integrations\Mailchimp;

use Brain\Monkey\Functions;
use EightshiftForms\Integrations\Mailchimp\MailchimpClient;

use function Tests\setupMocks;

test('Input items are correctly stored in a transient', function() {
	setupMocks();

	Functions\when('get_transient')->justReturn([]);
	Functions\when('wp_remote_get')->justReturn([]);
	Functions\when('wp_remote_retrieve_body')->justReturn('');
	Functions\when('has_filter')->justReturn(false);

	$mailchimpClient = new MailchimpClient();
	$output = $mailchimpClient->getItems(true);

	expect($output)
		->toBeArray();
})->only();

test('Input items are correctly stored in a transient when getting sample data from Mailchimp', function() {
	setupMocks();


	$mailchimpClient = new MailchimpClient();
	$output = $mailchimpClient->getItems(true);

	expect($output)
		->toBeArray();
})->only();


test('getItem function returns correct value type', function() {
	setupMocks();

	$mailchimpClient = new MailchimpClient();
	$output = $mailchimpClient->getItem('1');

	expect($output)
		->toBeArray();
})->only();

test('getTags function returns correct value type', function() {
	setupMocks();

	$mailchimpClient = new MailchimpClient();
	$output = $mailchimpClient->getTags('1');

	expect($output)
		->toBeArray();
})->only();
