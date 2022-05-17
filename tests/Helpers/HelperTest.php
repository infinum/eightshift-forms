<?php

namespace Tests\Unit\Helper;

use Brain\Monkey;
use Brain\Monkey\Functions;
use EightshiftForms\Helpers\Helper;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();
});

afterAll(function() {
	Monkey\tearDown();
});

test('getListingPageUrl returns the correct url', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-forms', Helper::getListingPageUrl());
});

test('getListingPageUrl returns the correct url on multisite', function () {
	putenv('test_force_admin_url_prefix=test');
	$this->assertSame('/test/wp-admin/admin.php?page=es-forms', Helper::getListingPageUrl());
	putenv('test_force_admin_url_prefix');
});

//---------------------------------------------------------------------------------//

test('getSettingsPageUrl returns the correct url with type not provided.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-settings&formId=1&type=general', Helper::getSettingsPageUrl('1'));
});

test('getSettingsPageUrl returns the correct url on multisite.', function () {
	putenv('test_force_admin_url_prefix=test');
	$this->assertSame('/test/wp-admin/admin.php?page=es-settings&formId=1&type=general', Helper::getSettingsPageUrl('1'));
	putenv('test_force_admin_url_prefix');
});

test('getSettingsPageUrl returns the correct url with type provided.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-settings&formId=1&type=test', Helper::getSettingsPageUrl('1', 'test'));
});

//---------------------------------------------------------------------------------//

test('getSettingsGlobalPageUrl returns the correct url with type not provided.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-settings-global&type=general', Helper::getSettingsGlobalPageUrl());
});

test('getSettingsGlobalPageUrl returns the correct url on multisite.', function () {
	putenv('test_force_admin_url_prefix=test');
	$this->assertSame('/test/wp-admin/admin.php?page=es-settings-global&type=general', Helper::getSettingsGlobalPageUrl());
	putenv('test_force_admin_url_prefix');
});

test('getSettingsGlobalPageUrl returns the correct url with type provided.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-settings-global&type=test', Helper::getSettingsGlobalPageUrl('test'));
});

//---------------------------------------------------------------------------------//

test('getNewFormPageUrl returns the correct url.', function () {
	$this->assertSame('/wp-admin/post-new.php?post_type=eightshift-forms', Helper::getNewFormPageUrl());
});

test('getNewFormPageUrl returns the correct url on multisite.', function () {
	putenv('test_force_admin_url_prefix=test');
	$this->assertSame('/test/wp-admin/post-new.php?post_type=eightshift-forms', Helper::getNewFormPageUrl());
	putenv('test_force_admin_url_prefix');
});


//---------------------------------------------------------------------------------//

test('getFormsTrashPageUrl returns the correct url.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-forms&type=trash', Helper::getFormsTrashPageUrl());
});

test('getFormsTrashPageUrl returns the correct url on multisite.', function () {
	putenv('test_force_admin_url_prefix=test');
	$this->assertSame('/test/wp-admin/admin.php?page=es-forms&type=trash', Helper::getFormsTrashPageUrl());
	putenv('test_force_admin_url_prefix');
});

//---------------------------------------------------------------------------------//

test('getFormEditPageUrl returns the correct url.', function () {
	$this->assertSame('/wp-admin/post.php?post=1&action=edit', Helper::getFormEditPageUrl('1'));
});

test('getFormEditPageUrl returns the correct url on multisite.', function () {
	putenv('test_force_admin_url_prefix=test');
	$this->assertSame('/test/wp-admin/post.php?post=1&action=edit', Helper::getFormEditPageUrl('1'));
	putenv('test_force_admin_url_prefix');

});

//---------------------------------------------------------------------------------//

test('getFormTrashActionUrl calls get_delete_post_link with correct arguments.', function ($expected, $formId, $permanent = null) {
	!is_null($permanent)
	? expect(Helper::getFormTrashActionUrl($formId, $permanent))->toBe($expected) 
	: expect(Helper::getFormTrashActionUrl($formId))->toBe($expected);
})->with([
	['id: 0, force: false', '0'],
	['id: 0, force: false', '0', 0],
	['id: 0, force: true', '0', true],
	['id: 0, force: true', '0', 1],
	['id: 75, force: true', '75', 1],
]);

//---------------------------------------------------------------------------------//

test('getFormTrashRestoreActionUrl returnes a properly nonced URL.', function () {
	$this->assertSame('/wp-admin/post.php?post=7&action=untrash&_wpnonce=untrash-post_7', Helper::getFormTrashRestoreActionUrl('7'));
});

test('getFormTrashRestoreActionUrl returnes a properly nonced URL on multisite.', function () {
	putenv('test_force_admin_url_prefix=test');
	$this->assertSame('/test/wp-admin/post.php?post=7&action=untrash&_wpnonce=untrash-post_7', Helper::getFormTrashRestoreActionUrl('7'));
	putenv('test_force_admin_url_prefix');
});
//---------------------------------------------------------------------------------//

test('getFormNames returns field names properly.', function () {
	$this->assertSame('', Helper::getFormNames(0));
	$this->assertSame('<code>{myFirstField}</code>, <code>{someField}</code>', Helper::getFormNames(1));
});

//---------------------------------------------------------------------------------//

test('logger does not log if log mode is turned off', function () {
	// Unfortunately, because Pest doesn't support running tests in isolation,
	// there isn't test coverage for correct logging when log mode is turned on.
	Functions\when('EightshiftForms\\Hooks\\Variables\\isLogMode', function() {
		return false;
	});

	Functions\expect('error_log')->never();

	expect(Helper::logger(['data' => 'Really dangerous error']))->toBeNull();
});

//---------------------------------------------------------------------------------//

test('minifyString returns expected values', function($expected, $input) {
	expect(Helper::minifyString($input))->toEqual($expected);
})->with([
	['A string that uses PHP_EOL', 'A string that uses'.PHP_EOL.'PHP_EOL'],
	["A string that uses \n \n multiple Windows line breaks", "A string that uses \r\n\r\n multiple Windows line breaks"],
	["A string that uses tabs", "A string that\tuses\t\ttabs"],
]);

//---------------------------------------------------------------------------------//

test('convertInnerBlocksToArray returns an empty array for unsupported types', function() {
	expect(Helper::convertInnerBlocksToArray('<option value="1">First</option>', 'unsupported'))->toEqual([]);
});

//---------------------------------------------------------------------------------//

test('convertInnerBlocksToArray returns a properly sorted array of options for select', function($expected, $markup) {
	expect(Helper::convertInnerBlocksToArray($markup, 'select'))->toEqual($expected);
})->with([
	[
		[
			[
				'label' => ' First',
				'value' => '1',
				'original' => '<option value="1" > First</option>'
			],
			[
				'label' => ' Second',
				'value' => '2',
				'original' => '<option value="2" > Second</option>'
			]
		],
		'<select><option value="1" > First</option><option value="2" > Second</option></select>'
	],
	[
		[], ''
	],
	[
		[
			[
				'label' => 'First',
				'value' => '1',
				'original' => '<option value="1">First</option>'
			],
			[
				'label' => 'Second',
				'value' => '2',
				'original' => '<option value="2">Second</option>'
			],
			[
				'label' => ' Third option ',
				'value' => '3',
				'original' => '<option id="third-option" value="3" aria-hidden="true">  Third  option  </ option>'
			]
		],
		'
			<select>
				<option value="1">First</option>
				<option value="2">Second</option>
				<option id="third-option" value="3" aria-hidden="true">  Third  option  </ option>
			</select>
		',
	],
]);

//---------------------------------------------------------------------------------//
test('encryptor helper uses openssl_encrypt properly', function () {
	expect(Helper::encryptor('encrypt', 'my ultimate secret'))
		->toEqual(
			base64_encode(openssl_encrypt(
				'my ultimate secret',
				'AES-256-CBC',
				hash('sha256', wp_salt()),
				0,
				substr(hash('sha256', wp_salt('SECURE_AUTH_KEY')), 0, 16)
			))
		);
});

//---------------------------------------------------------------------------------//
test('encryptor helper uses openssl_decrypt properly', function () {
	expect(Helper::encryptor('decrypt', 'NTFhQXZwZmhVVFMvQUUvaURUbUU0WUk5Rzd6b1I1ZG1oSkc5SzMxTzUxdz0='))
		->toEqual('very confidential secret');
});
