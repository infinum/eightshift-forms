<?php

namespace Tests;

use Brain\Monkey\Functions;
use Exception;
use Mockery;
use Mockery\MockInterface;
use EightshiftForms\Blocks\Blocks;

use function Symfony\Component\String\b;

/**
 * Helper function that will setup some repeating mocks in every tests.
 *
 * This is a way to circumvent the issue I was having described here:
 * https://github.com/pestphp/pest/issues/259
 */
function setupMocks() {
	// Mock WP functions
	Functions\stubTranslationFunctions();
	Functions\stubEscapeFunctions();

	if (!defined('DAY_IN_SECONDS')) {
		define('DAY_IN_SECONDS', 3600);
	}

	Functions\when('get_locale')->justReturn('HR');

	Functions\when('get_option')->alias(function (string $option, $default = false) {
		$envValue = getenv("test_force_option_{$option}");

		if ($envValue === false) {
			return $option;
		}

		if ($envValue === 'unset') {
			return $default;
		}

		if ($envValue === 'bool_true') {
			return true;
		}

		if ($envValue === 'bool_false') {
			return false;
		}

		try {
			return json_decode($envValue, false, 512, JSON_THROW_ON_ERROR);
		} catch (Exception $e) {
			return $envValue;
		}
	});

	Functions\when('get_stylesheet_directory')->justReturn(dirname(__FILE__) . '/');

	Functions\when('get_permalink')->justReturn('');

	Functions\when('get_template_directory')->justReturn(dirname(__FILE__) . '/data');

	Functions\when('get_edit_post_link')->alias(function($id) {
		$url = "/wp-admin/post.php?post={$id}&action=edit";

		$envValue = getenv("test_force_admin_url_prefix");
		if ($envValue !== false) {
			$url = "/{$envValue}{$url}";
		}
		return $url;
	});

	Functions\when('get_delete_post_link')->alias(function ($id = 0, $deprecated = '', $force_delete = false) {
		if (!empty($deprecated)) {
			throw new Exception('Deprecated argument used in get_delete_post_link call');
		}

		$force_delete = $force_delete ? 'true' : 'false';
		return "id: {$id}, force: {$force_delete}";
	});

	Functions\when('get_the_content')->alias(
		function ($more_link_text = null, bool $strip_teaser = false, $post = null)
		{
			$posts = [
				[
					'post_content' => 'aa',
				],
				[
					'post_content' => '{"eightshiftFormsInputFieldName":"myFirstField"}, {"anotherFieldName":"someField"}',
				]
			];

			return $posts[$post]['post_content'] ?? '';
		}
	);

	Functions\when('wp_nonce_url')->alias(function ($actionurl, $action = -1, $name = '_wpnonce') {
		return "{$actionurl}&{$name}={$action}";
	});

	Functions\when('wp_salt')->alias(function (string $scheme = 'auth') {
		return $scheme;
	});

	Functions\when('wp_get_mime_types')->alias(function () {
		return [
			'jpg|jpeg|jpe' => 'image/jpeg',
			'pdf' => 'application/pdf',
		];
	});

	Functions\when('wp_mail')->alias(function ($to, $subject, $message, $headers = '', $attachments = []) {
		$mail = json_encode([
			'to' => $to,
			'subject' => $subject,
			'message' => $message,
			'headers' => $headers,
			'attachments' => $attachments,
		]);

		putenv("test_wp_mail_last_call={$mail}");

		return (bool)filter_var($to, FILTER_VALIDATE_EMAIL);
	});

	Functions\when('get_post_meta')->alias(function ($post, $key = '', $single = false) {
		$envValue = getenv("test_force_post_meta_{$key}");
		if ($envValue === false) {
			return $key;
		}

		if ($envValue === 'bool_true') {
			return true;
		}

		if ($envValue === 'bool_false') {
			return false;
		}

		if ($envValue === 'unset') {
			return '';
		}

		try {
			return json_decode($envValue, false, 512, JSON_THROW_ON_ERROR);
		} catch (Exception $e) {
			return $envValue;
		}
	});

	Functions\when('get_admin_url')->alias(function ($blog_id = null, string $path = '', string $scheme = 'admin') {
		$url = '/wp-admin/';
		$envValue = getenv("test_force_admin_url_prefix");
	
		if ($envValue !== false) {
			$url = "/{$envValue}{$url}";
		}

		if ($path && is_string($path)) {
			$url .= ltrim($path, '/');
		}

		return $url;
	});

	Functions\when('get_rest_url')->alias(function ($blog_id = null, string $path = '/', string $scheme = 'rest') {
		$prefix = getenv("test_force_rest_url_prefix") ? getenv("test_force_rest_url_prefix") : 'wp-json';
		$url = "{$prefix}/";

		if ($path && is_string($path)) {
			$url .= ltrim($path, '/');
		}

		return ltrim($url, '/');
	});

	Functions\when('wp_safe_redirect')->alias(function (string $location, int $status = 302, string $x_redirect_by = 'WordPress') {
		$call = json_encode([
			'location' => $location,
			'status' => $status,
			'x_redirect_by' => $x_redirect_by,
		]);

		putenv("test_wp_safe_redirect_last_call=$call");
		return;
	});

	Functions\when('is_wp_version_compatible')->alias(function ($version) {
		$envValue = getenv("test_force_unit_test_wp_version") ? getenv("test_force_unit_test_wp_version") : '5.8';
		return version_compare($envValue, $version, '>=');
	});

	Functions\when('is_admin')->alias(function () {
		$envValue = getenv("test_force_is_admin");

		if ($envValue === 'bool_false') {
			return false;
		}

		return true;
	});

	Functions\when('get_post_type')->alias(function ($post = '') {
		$envValue = getenv("test_force_get_post_type");
		if ($envValue === false) {
			return 'post';
		}

		if ($envValue === 'bool_false') {
			return false;
		}

		return $envValue;
	});

	Functions\when('get_current_blog_id')->alias(function() {
		return getenv('test_force_current_blog_id') ? getenv('test_force_current_blog_id') : 1;
	});

	Functions\when('setcookie')->alias(function(
		string $name,
		string $value = "",
		int $expires_or_options = 0,
		string $path = "",
		string $domain = "",
		bool $secure = false,
		bool $httponly = false
	) {
		$args = json_encode([
			'name' => $name,
			'value' => $value,
			'expires_or_options' => $expires_or_options,
			'path' => $path,
			'domain' => $domain,
			'secure' => $secure,
			'httponly' => $httponly,
		]);

		putenv("test_setcookie_last_call={$args}");

		$envValue = getenv('test_force_setcookie_return');
		
		if ($envValue === 'bool_false') {
			return false;
		}

		return true;
	});
}

function mock(string $class): MockInterface
{
	return Mockery::mock($class);
}

function mockFormField(string $component, array $props): array {
	$field = [];
	$field['component'] = $component;
	
	$component = \lcfirst(\str_replace('-', '', \ucwords($component, '-')));

	$field["{$component}Id"] = $props["{$component}Id"] ?? "$component-id";
	$field["{$component}FieldLabel"] = $props["{$component}FieldLabel"] ?? "$component-field-label";
	$field["{$component}FieldHelp"] = $props["{$component}FieldHelp"] ?? "$component-field-help";
	$field["{$component}Value"] = $props["{$component}Value"] ?? ($component === 'input' ? '' : null);
	$field["{$component}IsRequired"] = $props["{$component}IsRequired"] ?? false;
	$field["{$component}IsDisabled"] = $props["{$component}IsDisabled"] ?? false;

	if ($props["{$component}Type"] ?? false) {
		$field["{$component}Type"] = $props["{$component}Type"];
	}
	if ($component === 'select') {
		$field["{$component}Options"] = $props["{$component}Options"] ?? [mockFormField('select-option', [])];
		return $field;
	}

	if ($component === 'selectOption') {
		$field["{$component}Value"] = $props["{$component}Value"] ?? 'select-option-val';
		$field["{$component}Label"] = $props["{$component}Label"] ?? 'select-option-label';
		return $field;
	}

	if ($component === 'checkboxes') {
		$field["{$component}Name"] = $props["{$component}Name"] ?? 'checkboxes-name';
		$field["{$component}Content"] = $props["{$component}Content"] ?? [mockFormField('checkbox', [])];
		$field["{$component}IsRequiredCount"] = $props["{$component}IsRequiredCount"] ?? null;
		return $field;
	}

	if ($component === 'checkbox' || $component === 'radio') {
		$field["{$component}Value"] = $field["{$component}Value"] ?? 'checkbox-value';
		$field["{$component}IsReadOnly"] = $props["{$component}IsReadOnly"] ?? false;
		$field["{$component}IsChecked"] = $props["{$component}IsReadOnly"] ?? false;
		$field["{$component}SingleSubmit"] = $props["{$component}SingleSubmit"] ?? false;
		return $field;
	}

	if ($component === 'radios') {
		$field["{$component}Name"] = $props["{$component}Name"] ?? 'radios-name';
		$field["{$component}Content"] = $props["{$component}Content"] ?? [mockFormField('radio', [])];
		return $field;
	}

	if ($component === 'file') {
		$field["{$component}Name"] = $props["{$component}Name"] ?? 'file-name';
		$field["{$component}Accept"] = $props["{$component}Accept"] ?? null;
		$field["{$component}MinSize"] = $props["{$component}MinSize"] ?? null;
		$field["{$component}MaxSize"] = $props["{$component}MaxSize"] ?? null;
		$field["{$component}IsMultiple"] = $props["{$component}IsMultiple"] ?? false;
		$field["{$component}IsRequired"] = $props["{$component}IsRequired"] ?? false;
		$field["{$component}Tracking"] = $props["{$component}Tracking"] ?? null;
		$field["{$component}CustomInfoText"] = $props["{$component}CustomInfoText"] ?? null;
		$field["{$component}CustomInfoTextUse"] = $props["{$component}CustomInfoTextUse"] ?? true;
		$field["{$component}CustomInfoButtonText"] = $props["{$component}CustomInfoButtonText"] ?? null;
		$field["{$component}UseCustom"] = $props["{$component}UseCustom"] ?? false;
		$field["{$component}Meta"] = $props["{$component}Meta"] ?? null;
		$field["{$component}Attrs"] = $props["{$component}Attrs"] ?? null;
		return $field;
	}

	return $field;
}

function buildTestBlocks() {
	(new Blocks())->getBlocksDataFullRaw();
}

function destroyTestBlocks() {
	global $esBlocks;
	$esBlocks = null;
}
