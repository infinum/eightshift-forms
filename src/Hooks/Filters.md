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
