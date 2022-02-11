<?php

namespace Tests\Unit\Validation;

use Brain\Monkey;
use EightshiftForms\Validation\SettingsValidation;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Labels\LabelsInterface;

use function Tests\setupMocks;

class SettingsValidationMock extends SettingsValidation {

	public function __construct(LabelsInterface $labels) {
		parent::__construct($labels);
	}
};

/**
 * Mock before tests.
 */
beforeEach(function () {
	Monkey\setUp();
	setupMocks();

	$labels = new Labels();

	$this->validationSettings = new SettingsValidationMock($labels);
});

afterAll(function() {
	Monkey\tearDown();
});

test('Register method will call sidebar hook', function () {
	$this->validationSettings->register();

	$this->assertSame(10, has_filter(SettingsValidationMock::FILTER_SETTINGS_SIDEBAR_NAME, 'Tests\Unit\Validation\SettingsValidationMock->getSettingsSidebar()'), 'The callback getSettingsSidebar should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, has_filter(SettingsValidationMock::FILTER_SETTINGS_NAME, 'Tests\Unit\Validation\SettingsValidationMock->getSettingsData()'), 'The callback getSettingsData should be hooked to custom filter hook with priority 10.');
	$this->assertSame(10, has_filter(SettingsValidationMock::FILTER_SETTINGS_GLOBAL_NAME, 'Tests\Unit\Validation\SettingsValidationMock->getSettingsGlobalData()'), 'The callback getSettingsGlobalData should be hooked to custom filter hook with priority 10.');
});
