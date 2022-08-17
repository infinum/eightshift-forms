<?php

dataset('default theme localizations', [
	[
		'eightshift-forms-scripts',
		'esFormsLocalization',
		[
			'formSubmitRestApiUrl' => 'wp-json/eightshift-forms/v1/form-submit',
			'hideGlobalMessageTimeout' => 6000,
			'redirectionTimeout' => 300,
			'hideLoadingStateTimeout' => 600,
			'fileCustomRemoveLabel' => 'Remove',
			'formDisableScrollToFieldOnError' => false,
			'formDisableScrollToGlobalMessageOnSuccess' => false,
			'formDisableAutoInit' => false,
			'formResetOnSuccess' => true,
			'captcha' => 'nevershareyourkeys',
			'storageConfig' => '{"allowed":["gh_src","gh_jid","_hsq","utm"],"expiration":"30"}',
		],
	],
]);
