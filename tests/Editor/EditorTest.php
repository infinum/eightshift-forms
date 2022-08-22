<?php

namespace Tests\Unit\Hooks;

use Brain\Monkey;
use Brain\Monkey\Filters;
use EightshiftForms\Editor\Editor;

use function Tests\setupMocks;

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();
	$this->editor = new Editor();
});

afterEach(function() {
	unset($this->editor);
	Monkey\tearDown();
});

test('Editor service class registers hooks properly', function() {
	Filters\expectAdded('admin_init', [$this->editor, 'getEditorBackLink']);
	
	$this->editor->register();
});

test('getEditorBackLink redirects back properly', function() {
	putenv('test_force_request_uri=/wp-admin/edit.php?post_type=eightshift-forms');
	
	expect($this->editor->getEditorBackLink())->toBe(null);
	expect(json_decode(getenv('test_wp_safe_redirect_last_call'), true))->toBe([
		'location' => '/wp-admin/admin.php?page=es-forms',
		'status' => 302,
		'x_redirect_by' => 'WordPress',
	]);

	putenv('test_force_request_uri');
	putenv('test_wp_safe_redirect_last_call');
});

