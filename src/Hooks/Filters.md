# Filters

This document will provide you with the code examples for forms filters.

## Adding additional blocks in the custom forms block
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
