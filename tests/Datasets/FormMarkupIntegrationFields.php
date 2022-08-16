<?php

use function Tests\mockFormField;

// Used in getIntegrationFieldsDetails tests.
dataset('form markup for integration fields', [
	[
		'formtest1', // key
		'mailchimp', // type
		[
			mockFormField('input', ['inputIsRequired' => true]),
			mockFormField('input', ['inputId' => 'input-email', 'inputIsEmail' => true]),
		], // formFields
		'1', // formId
		[], // additionalLabel
		[
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'input-id---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 1,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'input-id---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => true,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					]
				]
			],
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'input-email---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 2,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'input-email---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					]
				]
			]
		] // return
	],
	[
		'formtest-file', // key
		'mailchimp', // type
		[
			mockFormField('file', []),
		], // formFields
		'1', // formId
		[], // additionalLabel
		[
			[
				'component' => 'group',
				'groupLabel' => 'File-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'file-id---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'file-id---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'file-id---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'file-id---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'file-id---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 1,
						'inputMin' => 1,
						'inputMax' => 1,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'file-id---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					],
					[
						'component' => 'select',
						'selectId' => 'file-id---file-info-label',
						'selectFieldLabel' => 'Field label',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field file label is going to be default visible or hidden.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							]
						]
					]
				]
			],
		] // return
	],
]);

dataset('form markup for integration fields with styles', [
	[
		'formtest1', // key
		'mailchimp', // type
		[
			mockFormField('input', ['inputIsRequired' => true]),
			mockFormField('input', ['inputId' => 'input-email', 'inputIsEmail' => true]),
		], // formFields
		'1', // formId
		[], // additionalLabel
		[
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'input-id---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 1,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'input-id---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => true,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					],
					[
						'component' => 'select',
						'selectId' => 'input-id---field-style',
						'selectFieldLabel' => 'Style',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define different style for this field.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Default',
								'selectOptionValue' => 'default',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Custom Style',
								'selectOptionValue' => 'custom-style',
								'selectOptionIsSelected' => false,
							],
						]
					]
				]
			],
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'input-email---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 2,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'input-email---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					],
					[
						'component' => 'select',
						'selectId' => 'input-email---field-style',
						'selectFieldLabel' => 'Style',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define different style for this field.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Default',
								'selectOptionValue' => 'default',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Custom Style',
								'selectOptionValue' => 'custom-style',
								'selectOptionIsSelected' => false,
							],
						]
					]
				]
			]
		] // return
	]
]);

dataset('form markup for submit fields', [
	[
		'formtest1', // key
		'mailchimp', // type
		[
			mockFormField('submit', []),
		], // formFields
		'1', // formId
		[], // additionalLabel
		[
			[
				'component' => 'group',
				'groupLabel' => 'Submit-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'submit-id---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'submit-id---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'submit-id---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'submit-id---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'submit-id---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 1,
						'inputMin' => 1,
						'inputMax' => 1,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'submit-id---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					],
					[
						'component' => 'input',
						'inputId' => 'submit-id---label',
						'inputFieldLabel' => 'Label',
						'inputValue' => '',
						'inputIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define field label value.'
					]
				]
			],
		] // return
	]
]);

dataset('form markup for integration fields with editing disabled', [
	[
		'formtest1', // key
		'mailchimp', // type
		[
			mockFormField('input', ['inputIsRequired' => true]),
			mockFormField('input', ['inputId' => 'input-email', 'inputIsEmail' => true]),
		], // formFields
		'1', // formId
		[], // additionalLabel
		[
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'input-id---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 1,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'input-id---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => true,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					],
					[
						'component' => 'select',
						'selectId' => 'input-id---field-style',
						'selectFieldLabel' => 'Style',
						'selectValue' => '',
						'selectIsDisabled' => true,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define different style for this field.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Default',
								'selectOptionValue' => 'default',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Custom Style',
								'selectOptionValue' => 'custom-style',
								'selectOptionIsSelected' => false,
							],
						]
					]
				]
			],
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'input-email---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 2,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => true,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'input-email---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => true,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					],
					[
						'component' => 'select',
						'selectId' => 'input-email---field-style',
						'selectFieldLabel' => 'Style',
						'selectValue' => '',
						'selectIsDisabled' => true,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define different style for this field.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Default',
								'selectOptionValue' => 'default',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Custom Style',
								'selectOptionValue' => 'custom-style',
								'selectOptionIsSelected' => false,
							],
						]
					]
				]
			]
		] // return
	]
]);

dataset('form markup for integration fields when formViewDetails is used to override settings', [
	[
		'formtest1', // key
		'mailchimp', // type
		[
			mockFormField('input', ['inputIsRequired' => true]),
			mockFormField('input', ['inputId' => 'input-email', 'inputIsEmail' => true]),
		], // formFields
		'1', // formId
		[], // additionalLabel
		[
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'input-id---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => 3,
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-id---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 1,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'input-id---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => true,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							]
						]
					]
				]
			],
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'input-email---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => 4,
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.'
					],
					[
						'component' => 'input',
						'inputId' => 'input-email---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 2,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.'
					],
					[
						'component' => 'select',
						'selectId' => 'input-email---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => 'false',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => true,
							]
						]
					]
				]
			]
		] // return
	]
]);

dataset('form markup for Greenhouse-specific integration fields', [
	[
		'formtest1',
		'greenhouse',
		[
			mockFormField('input', [
				'inputType' => 'greenhouse',
				'inputId' => 'resume_text'
			]),
			mockFormField('input', [
				'inputType' => 'greenhouse',
				'inputId' => 'cover_letter_text'
			]),
		],
		'1',
		[],
		[
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label Text',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'resume_text---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.',
					],
					[
						'component' => 'input',
						'inputId' => 'resume_text---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.',
					],
					[
						'component' => 'input',
						'inputId' => 'resume_text---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.',
					],
					[
						'component' => 'input',
						'inputId' => 'resume_text---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.',
					],
					[
						'component' => 'input',
						'inputId' => 'resume_text---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 1,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.',
					],
					[
						'component' => 'select',
						'selectId' => 'resume_text---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							],
						],
					],
				],
			],
			[
				'component' => 'group',
				'groupLabel' => 'Input-field-label Text',
				'groupSaveOneField' => true,
				'groupStyle' => 'integration-inner',
				'groupContent' => [
					[
						'component' => 'input',
						'inputId' => 'cover_letter_text---mobile',
						'inputFieldLabel' => 'Width (mobile)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for mobile breakpoint.',
					],
					[
						'component' => 'input',
						'inputId' => 'cover_letter_text---tablet',
						'inputFieldLabel' => 'Width (tablet)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for tablet breakpoint.',
					],
					[
						'component' => 'input',
						'inputId' => 'cover_letter_text---desktop',
						'inputFieldLabel' => 'Width (desktop)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for desktop breakpoint.',
					],
					[
						'component' => 'input',
						'inputId' => 'cover_letter_text---large',
						'inputFieldLabel' => 'Width (large)',
						'inputType' => 'number',
						'inputValue' => '',
						'inputMin' => 0,
						'inputMax' => 12,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field width for large breakpoint.',
					],
					[
						'component' => 'input',
						'inputId' => 'cover_letter_text---order',
						'inputFieldLabel' => 'Order',
						'inputType' => 'number',
						'inputValue' => 2,
						'inputMin' => 1,
						'inputMax' => 2,
						'inputStep' => 1,
						'inputIsDisabled' => false,
						'inputPlaceholder' => 'auto',
						'inputFieldUseTooltip' => true,
						'inputFieldTooltipContent' => 'Define field order that is going to be used.',
					],
					[
						'component' => 'select',
						'selectId' => 'cover_letter_text---use',
						'selectFieldLabel' => 'Visibility',
						'selectValue' => '',
						'selectIsDisabled' => false,
						'selectFieldUseTooltip' => true,
						'selectFieldTooltipContent' => 'Define if field is going to be used or not by default.',
						'selectOptions' => [
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Visible',
								'selectOptionValue' => 'true',
								'selectOptionIsSelected' => false,
							],
							[
								'component' => 'select-option',
								'selectOptionLabel' => 'Hidden',
								'selectOptionValue' => 'false',
								'selectOptionIsSelected' => false,
							],
                  		],
					],
              ],
          ],
      ],
	],
]);
