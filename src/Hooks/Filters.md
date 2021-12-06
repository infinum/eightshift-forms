# Filters

This document will provide you with the code examples for forms filters.

## Adding additional blocks in the custom forms block

This filter is used if you want to add your custom or core blocks to the custom form builder.

**Default values:**
```php
[]
```

**Filter:**
```php
// Provide additional blocks in the custom forms block.
add_filter('es_forms_additional_blocks', [$this, 'getAdditionalBlocks']);

/**
 * Provide additional block to the custom forms block.
 *
 * @return array<string>
 */
public function getAdditionalBlocks(): array
{
	return [
		'core/heading',
		'core/paragraph',
	];
}
```

## Changing the default media breakpoints

This filter will override our default media breakpoints used for responsive selectors like widths of the form fields.
You must provide all 4 breakpoints in order for this to work properly.

**Default values:**
```php
[
	'mobile' => 480,
	'tablet' => 960,
	'desktop' => 1920,
	'large' => 1921,
]
```

**Filter:**
```php
// Provide custom media breakpoints.
add_filter('es_forms_media_breakpoints', [$this, 'getMediaBreakpoints']);

/**
 * Provide custom media breakpoints.
 *
 * @return array<string, int>
 */
public function getMediaBreakpoints(): array
{
	return [
		'mobile' => 200,
		'tablet' => 500,
		'desktop' => 800,
		'large' => 1200
	];
}
```

## Add additional style options to forms block

This filter will add new options to the style select dropdown in the forms block.

Forms style option selector will not show unless a filter is provided.

**Default values:**
```php
[]
```

**Filter:**
```php
// Provide custom forms style options.
add_filter('es_forms_block_forms_style_options', [$this, 'getFormsStyleOptions']);

/**
 * Provide custom forms style options.
 *
 * @return array<string, mixed>
 */
public function getFormsStyleOptions(): array
{
	return [
		[
			'label' => 'Default',
			'value' => 'default'
		],
		[
			'label' => 'Custom Style',
			'value' => 'custom-style'
		],
	];
}
```

## Add additional style options to field block

This filter will add new options to the style select dropdown in the field block.

Field style option selector will not show unless a filter is provided.

**Default values:**
```php
[]
```

**Filter:**
```php
// Provide custom field style options.
add_filter('es_forms_block_field_style_options', [$this, 'getFieldStyleOptions']);

/**
 * Provide custom field style options.
 * Available options:
 * - input
 * - textarea
 * - checkboxes
 * - radios
 * - sender-email
 * - select
 * - file
 * - submit
 *
 * @return array<string, mixed>
 */
public function getFieldStyleOptions(): array
{
	return [
		'input' => [
			[
				'label' => 'Default',
				'value' => 'default'
			],
			[
				'label' => 'Custom Style',
				'value' => 'custom-style'
			],
		],
		'select' => [
			[
				'label' => 'Default',
				'value' => 'default'
			],
			[
				'label' => 'Custom Style',
				'value' => 'custom-style',
				'useCustom' => false, // This key can be used only on select, file and textarea and it removes the custom JS library from the component.
			],
		]
	];
}
```

## Add data to custom data block

These filters will add the necessary data for the custom data block to work.

Field data option selector will not be shown unless a filter is added.

**Default values:**
```php
[]
```

**Filter for providing Block editor options:**
```php
// Provide custom data block options.
add_filter('es_forms_block_custom_data_options', [$this, 'getCustomDataBlockOptions']);

/**
 * Provide custom data block options.
 *
 * @return array<string, mixed>
 */
public function getCustomDataBlockOptions(): array
{
	return [
		[
			'label' => '',
			'value' => ''
		],
		[
			'label' => 'Blog posts',
			'value' => 'blog-posts'
		],
		[
			"label" => "Jobs",
			"value" => "jobs"
		],
	];
}
```

**Filter for providing option data:**
```php
// Provide custom data block options.
add_filter('es_forms_block_custom_data_options_data', [$this, 'getCustomDataBlockOptionsData']);

/**
 * Provide custom data block options data.
 *
 * @param string $type Type of option selected in the Block editor.
 *
 * @return array<string, mixed>
 */
public function getCustomDataBlockOptionsData(string $type): array
{
	switch ($type) {
		case 'blog-posts':
			return [
				[
					'label' => '',
					'value' => ''
				],
				[
					'label' => 'Post 1',
					'value' => 'post1'
				],
				[
					"label" => "Post 2",
					"value" => "post2"
				],
			];
		case 'jobs':
			return [
				[
					'label' => '',
					'value' => ''
				],
				[
					'label' => 'Job 1',
					'value' => 'job1'
				],
				[
					"label" => "Job 2",
					"value" => "job2"
				],
			];
		default:
			return [];
	}
}
```

## Override default submit button with your own component

These filter will remove the default forms submit button component and use your callback string. This will not apply to form settings pages.

**Data values example:**
```php
[
	'value' => 'Submit' // String.
	'isDisabled' => 1, // Boolean.
	'class' => 'es-submit' // String with spaces.
	'attrs' => [] // Key value pair for additional attributes like tracking, etc.
]
```

**Filter**
```php
// Provide project submit button component.
add_filter('es_forms_block_submit', [$this, 'getFormsSubmitComponent']);

/**
 * Provide project submit button component.
 *
 * @param array<string, mixed> $data Data provided from the forms.
 *
 * @return string
 */
public function getFormsSubmitComponent(array $data): string
{
	return Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'button',
		Components::props('button', [
			'buttonTypographyContent' => $data['value'] ?? '',
			'additionalClass' => $data['class'] ?? '',
			'buttonAttrs' => $data['attrs'] ?? [],
			'buttonIsDisabled' => $data['isDisabled'] ?? false,
		]),
		'',
		true
	);
}
```

## Adding additional content in blocks

This filter is used if you want to add some custom string/component/css variables, etc. to the block. By changing the name of the filter you will target different blocks.

**Supported blocks:**
* form selector - `es_forms_block_form_selector_additional_content`
* input - `es_forms_block_input_additional_content`
* textarea - `es_forms_block_textarea_additional_content`
* select - `es_forms_block_select_additional_content`
* file - `es_forms_block_file_additional_content`
* checkboxes - `es_forms_block_checkboxes_additional_content`
* radios - `es_forms_block_radios_additional_content`
* submit - `es_forms_block_submit_additional_content`

**Default value:**
```php
''
```

**Filter example for form selector:**
```php
// Provide additional content in form selector block.
add_filter('es_forms_block_form_selector_additional_content', [$this, 'getFormSelectorAdditionalContent']);

/**
 * Provide additional content in form selector block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 *
 * @return string
 */
public function getFormSelectorAdditionalContent($attributes): string
{
	return 'custom string';
}
```

## Change integration form data before output.

This filter is used if you want to change form data before output. By changing the name of the filter you will target different blocks.

**Supported blocks:**
* goodbits - `es_forms_integration_goodbits_form_data`
* greenhouse - `es_forms_integration_greenhouse_form_data`
* hubspot - `es_forms_integration_hubspot_form_data`
* mailchimp - `es_forms_integration_mailchimp_form_data`
* mailerlite - `es_forms_integration_mailerlite_form_data`


**Filter example for Greenhouse integration:**
```php
// Provide integration from data.
add_filter('es_forms_integration_greenhouse_form_data', [$this, 'getIntegrationFormData']);

/**
 * Provide integration form data changes - Greenhouse.
 *
 * @param array<string, mixed> $data Array of component/attributes data.
 *
 * @return array<string, mixed>
 */
public function getIntegrationFormData(array $data): array
{
	return $data;
}
```
