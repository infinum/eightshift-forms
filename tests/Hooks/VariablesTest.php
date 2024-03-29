<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use EightshiftForms\Hooks\Variables;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$this->variables = new Variables();
});

afterEach(function() {
	unset($this->variables);
	Monkey\tearDown();
});

/**
 * As we can't undefine or redefine constants, this test suite consists of two parts.
 * In the first part, we test that variable methods return correctly when constant values
 * aren't set. In the second part, we test that variable methods return correctly when
 * constant values are set.
 */

test('getApiKeyKeyHubspot returns an empty string when constant is not set', function() {
	expect(Variables::getApiKeyHubspot())->toBe('');
});

test('getApiKeyGreenhouse returns an empty string when constant is not set', function() {
	expect(Variables::getApiKeyGreenhouse())->toBe('');
});

test('getBoardTokenGreenhouse returns an empty string when constant is not set', function() {
	expect(Variables::getBoardTokenGreenhouse())->toBe('');
});

test('getApiKeyMailchimp returns an empty string when constant is not set', function() {
	expect(Variables::getApiKeyMailchimp())->toBe('');
});

test('getApiKeyMailerlite returns an empty string when constant is not set', function() {
	expect(Variables::getApiKeyMailerlite())->toBe('');
});

test('getApiKeyGoodbits returns an empty string when constant is not set', function() {
	expect(Variables::getApiKeyGoodbits())->toBe('');
});

test('getGoogleReCaptchaSiteKey returns an empty string when constant is not set', function() {
	expect(Variables::getGoogleReCaptchaSiteKey())->toBe('');
});

test('getGoogleReCaptchaSecretKey returns an empty string when constant is not set', function() {
	expect(Variables::getGoogleReCaptchaSiteKey())->toBe('');
});

test('getGeolocationIp returns an empty string when constant is not set', function() {
	expect(Variables::getGeolocationIp())->toBe('');
});

test('getApiKeyClearbit returns an empty string when constant is not set', function() {
	expect(Variables::getApiKeyClearbit())->toBe('');
});

//---------------------------------------------------------------------------------//

test('getApiKeyKeyHubspot returns correctly when constant is set', function() {
	define('ES_API_KEY_HUBSPOT', 'test');
	expect(Variables::getApiKeyHubspot())->toBe('test');
});

test('getApiKeyGreenhouse returns correctly when constant is set', function() {
	define('ES_API_KEY_GREENHOUSE', 'test');
	expect(Variables::getApiKeyGreenhouse())->toBe('test');
});

test('getBoardTokenGreenhouse returns correctly when constant is set', function() {
	define('ES_BOARD_TOKEN_GREENHOUSE', 'test');
	expect(Variables::getBoardTokenGreenhouse())->toBe('test');
});

test('getApiKeyMailchimp returns correctly when constant is set', function() {
	define('ES_API_KEY_MAILCHIMP', 'test');
	expect(Variables::getApiKeyMailchimp())->toBe('test');
});

test('getApiKeyMailerlite returns correctly when constant is set', function() {
	define('ES_API_KEY_MAILERLITE', 'test');
	expect(Variables::getApiKeyMailerlite())->toBe('test');
});

test('getApiKeyGoodbits returns correctly when constant is set', function() {
	define('ES_API_KEY_GOODBITS', 'test');
	expect(Variables::getApiKeyGoodbits())->toBe('test');
});

test('getGoogleReCaptchaSiteKey returns correctly when constant is set', function() {
	define('ES_GOOGLE_RECAPTCHA_SITE_KEY', 'test');
	expect(Variables::getGoogleReCaptchaSiteKey())->toBe('test');
});

test('getGoogleReCaptchaSecretKey returns correclty string when constant is set', function() {
	define('ES_GOOGLE_RECAPTCHA_SECRET_KEY', 'test');
	expect(Variables::getGoogleReCaptchaSecretKey())->toBe('test');
});

test('getGeolocationIp returns correctly when constant is set', function() {
	define('ES_GEOLOCAITON_IP', 'test');
	expect(Variables::getGeolocationIp())->toBe('test');
});

test('getApiKeyClearbit returns correctly when constant is set', function() {
	define('ES_API_KEY_CLEARBIT', 'test');
	expect(Variables::getApiKeyClearbit())->toBe('test');
});

test('getApiKeyActiveCampaign returns correctly when constant is set', function() {
	define('ES_API_KEY_ACTIVE_CAMPAIGN', 'test');
	expect(Variables::getApiKeyActiveCampaign())->toBe('test');
});

test('getApiUrlActiveCampaign returns correctly when constant is set', function() {
	define('ES_API_URL_ACTIVE_CAMPAIGN', 'test');
	expect(Variables::getApiUrlActiveCampaign())->toBe('test');
});
