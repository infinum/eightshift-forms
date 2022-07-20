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

afterAll(function() {
	Monkey\tearDown();
});

/**
 * As we can't undefine or redefine constants, this test suite consists of two parts.
 * In the first part, we test that variable methods return correctly when constant values
 * aren't set. In the second part, we test that variable methods return correctly when
 * constant values are set.
 */

test('isDevelopMode returns false when ES_DEVELOP_MODE is not set', function() {
	expect(Variables::isDevelopMode())->toBe(false);
});

test('skipFormValidation returns false when ES_DEVELOP_MODE_SKIP_VALIDATION is not set', function() {
	expect(Variables::skipFormValidation())->toBe(false);
});

test('isLogMode return false when ES_LOG_MODE is not set', function() {
	expect(Variables::isLogMode())->toBe(false);
});

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

test('getGeolocation returns an empty string when constant is not set', function() {
	expect(Variables::getGeolocation())->toBe('');
});

test('getApiKeyClearbit returns an empty string when constant is not set', function() {
	expect(Variables::getApiKeyClearbit())->toBe('');
});

//---------------------------------------------------------------------------------//

test('isDevelopMode returns true when ES_DEVELOP_MODE is set', function() {
	define('ES_DEVELOP_MODE', true);
	expect(Variables::isDevelopMode())->toBe(true);
});

test('skipFormValidation returns true when ES_DEVELOP_MODE_SKIP_VALIDATION is set', function() {
	define('ES_DEVELOP_MODE_SKIP_VALIDATION', true);
	expect(Variables::skipFormValidation())->toBe(true);
});

test('isLogMode return true when ES_LOG_MODE is set', function() {
	define('ES_LOG_MODE', true);
	expect(Variables::isLogMode())->toBe(true);
});

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

test('getGeolocation returns correctly when constant is set', function() {
	define('ES_GEOLOCAITON'	, 'test');
	expect(Variables::getGeolocation())->toBe('TEST');
});

test('getApiKeyClearbit returns correctly when constant is set', function() {
	define('ES_API_KEY_CLEARBIT', 'test');
	expect(Variables::getApiKeyClearbit())->toBe('test');
});