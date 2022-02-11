<?php

namespace Tests\Unit\Helper;

use Brain\Monkey;
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

//---------------------------------------------------------------------------------//

test('getSettingsPageUrl returns the correct url with type not provided.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-settings&formId=1&type=general', Helper::getSettingsPageUrl('1'));
});

test('getSettingsPageUrl returns the correct url with type provided.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-settings&formId=1&type=test', Helper::getSettingsPageUrl('1', 'test'));
});

//---------------------------------------------------------------------------------//

test('getSettingsGlobalPageUrl returns the correct url with type not provided.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-settings-global&type=general', Helper::getSettingsGlobalPageUrl());
});

test('getSettingsGlobalPageUrl returns the correct url with type provided.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-settings-global&type=test', Helper::getSettingsGlobalPageUrl('test'));
});

//---------------------------------------------------------------------------------//

test('getNewFormPageUrl returns the correct url.', function () {
	$this->assertSame('/wp-admin/post-new.php?post_type=eightshift-forms', Helper::getNewFormPageUrl());
});

//---------------------------------------------------------------------------------//

test('getFormsTrashPageUrl returns the correct url.', function () {
	$this->assertSame('/wp-admin/admin.php?page=es-forms&type=trash', Helper::getFormsTrashPageUrl());
});

//---------------------------------------------------------------------------------//

test('getFormEditPageUrl returns the correct url.', function () {
	$this->assertSame('/wp-admin/post.php?post=1&action=edit', Helper::getFormEditPageUrl('1'));
});
