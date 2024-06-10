<?php

namespace Tests\Unit;

use EightshiftForms\Main\Main;

use function Tests\getTestTypeUnit;
use function Tests\testAfterEach;
use function Tests\testBeforeEach;

uses()->group(getTestTypeUnit());

beforeEach(function() {
	testBeforeEach();

	$this->main = new Main([], '');
});

afterEach(function() {
	testAfterEach();
});

test('Register method will call init hook', function () {
	$this->main->register();

	$this->assertSame(10, has_action('plugins_loaded', 'EightshiftForms\Main\Main->registerServices()'));
});
