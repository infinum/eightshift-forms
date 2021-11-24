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
 * @return array
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
 * @return array
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
 * @return array
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
 * @return array
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
				'value' => 'custom-style'
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
 * @return array
 */
public function getCustomDataBlockOptions(): array
{
	return [
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
 * @return array
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
 * @return array
 */
public function getFormsSubmitComponent(array $data): array
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
