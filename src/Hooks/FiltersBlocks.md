# Filters Blocks
This document will provide you with the code examples for forms filters used in blocks.

## Adding additional blocks in the custom forms block
This filter is used if you want to add your custom or core blocks to the custom form builder.

**Filter name:**
`es_forms_additional_blocks`

**Filter example:**
```php
// Set additional blocks in the custom forms block.
add_filter('es_forms_blocks_additional_blocks', [$this, 'getAdditionalBlocks']);

/**
 * Set additional blocks in the custom forms block.
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
This filter will override our default media breakpoints used for responsive selectors like widths of the form fields. You must provide all 4 breakpoints in order for this to work properly and you must follow our breakpoint names.

**Filter name:**
`es_forms_blocks_media_breakpoints`

**Default values:**
```php
[
	'mobile' => 480,
	'tablet' => 960,
	'desktop' => 1920,
	'large' => 1921,
]
```

**Filter example:**
```php
// Set custom media breakpoints.
add_filter('es_forms_media_breakpoints', [$this, 'getMediaBreakpoints']);

/**
 * Set custom media breakpoints.
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
