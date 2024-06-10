<?php

namespace Tests\Unit\Permissions;

use Brain\Monkey;
use EightshiftForms\Permissions\Permissions;

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

test('getPermissions returns correct list of permissions', function() {
    expect(Permissions::getPermissions())->toBe([
        'eightshift_forms',
        'eightshift_forms_adminu_menu',
        'eightshift_forms_global_settings',
        'eightshift_forms_listing',
        'eightshift_forms_form_settings',
        'edit_eightshift_forms',
        'read_eightshift_forms',
        'delete_eightshift_forms',
        'edit_eightshift_formss',
        'edit_others_eightshift_formss',
        'delete_eightshift_formss',
        'publish_eightshift_formss',
        'read_private_eightshift_formss',
    ]);
});
