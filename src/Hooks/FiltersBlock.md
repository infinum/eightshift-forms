# Filters Block
This document will provide you with the code examples for forms filters used in individual blocks.

## Add additional style options to forms block
This filter will add new options to the style select dropdown in the forms block. Forms style option selector will not show unless a filter is provided. This option is shown in Block Editor.

**Filter name:**
`es_forms_block_forms_style_options`

**Filter example:**
```php
// Set block forms style options.
add_filter('es_forms_block_forms_style_options', [$this, 'getBlockFormsStyleOptions']);

/**
 * Set block forms style options.
 *
 * @return array<string, mixed>
 */
public function getBlockFormsStyleOptions(): array
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
This filter will add new options to the style select dropdown in the field block. Field style option selector will not show unless a filter is provided. This option is shown in Block Editor.

**Filter name:**
`es_forms_block_field_style_options`

**Available options:**
 * input
 * textarea
 * checkboxes
 * radios
 * sender-email
 * select
 * file
 * submit

**Filter example:**
```php
// Set block field style options.
add_filter('es_forms_block_field_style_options', [$this, 'getBlockFieldStyleOptions']);

/**
 * Set block field style options.
 *
 * @return array<string, mixed>
 */
public function getBlockFieldStyleOptions(): array
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

## Add to custom data block
These filters will add the necessary data for the custom data block to work. Field data option selector will not be shown unless a filter is added. This option is shown in Block Editor.

**Filter name:**
`es_forms_block_custom_data_options`

**Filter example options:**
```php
// Set block custom data options.
add_filter('es_forms_block_custom_data_options', [$this, 'getBlockCustomDataOptions']);

/**
 * Set block custom data options.
 *
 * @return array<string, mixed>
 */
public function getBlockCustomDataOptions(): array
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

**Filter example option data:**
```php
// Set block custom data options data.
add_filter('es_forms_block_custom_data_options_data', [$this, 'getBlockCustomDataOptionsData']);

/**
 * Set block custom data options data.
 *
 * @param string $type Type of option selected in the Block editor.
 *
 * @return array<string, mixed>
 */
public function getBlockCustomDataOptionsData(string $type): array
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
This filter will remove the default forms submit button component and use your callback. This will not apply to form settings pages.

**Filter name:**
`es_forms_block_submit_component`

**Data values:**
```php
[
	'value' => 'Submit', // String.
	'isDisabled' => 1, // Boolean.
	'class' => 'es-submit', // String with spaces.
	'attrs' => [], // Key value pair for additional attributes like tracking, etc.
	'attributes' => {}, // This key gives you the full attributes data of the forms attributes.
]
```

**Filter example:**
```php
// Set block submit component.
add_filter('es_forms_block_submit_component', [$this, 'getBlockSubmitComponent']);

/**
 * Set block submit component.
 *
 * @param array<string, mixed> $data Data provided from the forms.
 *
 * @return string
 */
public function getBlockSubmitComponent(array $data): string
{
	return Components::render(
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

**Filter name:**
`es_forms_block_<block_name>_additional_content`

**Supported blocks:**
* form_selector
* field
* input
* textarea
* select
* file
* checkboxes
* radios
* submit

**Filter example:**
```php
// Set block form selector additional content.
add_filter('es_forms_block_form_selector_additional_content', [$this, 'getBlockFormSelectorAdditionalContent']);

/**
 * Set block form selector additional content.
 *
 * @param array<string, mixed> $attributes Block attributes.
 *
 * @return string
 */
public function getBlockFormSelectorAdditionalContent($attributes): string
{
	return 'custom string';
}
```

## Changing the default success redirection wait time
This filter will override our default wait time once the form returns success and it is redirected. The time is calculated in milliseconds. *Example: 1000ms = 1s*.

**Filter name:**
`es_forms_block_form_redirection_timeout`

**Default:**
```php
300 // 0.3 seconds.
```

**Filter example:**
```php
// Set block form redirection timeout.
add_filter('es_forms_block_form_redirection_timeout', [$this, 'getBlockFormRedirectionTimeout']);

/**
 * Set block form redirection timeout.
 *
 * @return string
 */
public function getBlockFormRedirectionTimeout(): string
{
	return '1000'; // 1 seconds.
}
```

## Changing the default success hide global message wait time
This filter will override our default wait time before the global message is removed. The time is calculated in milliseconds. *Example: 1000ms = 1s*.

**Filter name:**
`es_forms_block_form_hide_global_message_timeout`

**Default:**
```php
6000 // 6 seconds.
```

**Filter example:**
```php
// Set block form hide global msg timeout.
add_filter('es_forms_block_form_hide_global_message_timeout', [$this, 'getBlockFormHideGlobalMessageTimeout']);

/**
 * Set block form hide global msg timeout.
 *
 * @return string
 */
public function getBlockFormHideGlobalMessageTimeout(): string
{
	return '10000'; // 10 seconds.
}
```

## Changing the default hide loading state wait time
This filter will override our default wait time before the loading state is removed. The time is calculated in milliseconds. *Example: 1000ms = 1s*.

**Filter name:**
`es_forms_block_form_hide_loading_state_timeout`

**Default:**
```php
600 // 0.6 seconds.
```

**Filter example:**
```php
// Set block form hide loading state timeout.
add_filter('es_forms_block_form_hide_loading_state_timeout', [$this, 'getBlockFormHideLoadingStateTimeout']);

/**
 * Set block form hide loading state timeout.
 *
 * @return string
 */
public function getBlockFormHideLoadingStateTimeout(): string
{
	return '600'; // 0.6 seconds.
}
```

## Set success redirect url value
This filter will override settings for success redirect url.

**Filter name:**
`es_forms_block_form_success_redirect_url`

**Filter example:**
```php
// Set success redirect url value.
add_filter('es_forms_block_form_success_redirect_url', [$this, 'getBlockFormSuccessRedirectUrl'], 10, 2);

/**
 * Set success redirect url value.
 *
 * @param string $formType Type of form used like greenhouse, hubspot, etc.
 * @param string $formId Form ID.
 *
 * @return string
 */
public function getBlockFormSuccessRedirectUrl(string $formType, string $formId): string
{
	return 'https://infinum.com/';
}
```

## Set tracking event name value
This filter will override settings for tracking event name.

**Filter name:**
`es_forms_block_form_tracking_event_name`

**Filter example:**
```php
// Set tracking event name value.
add_filter('es_forms_block_form_tracking_event_name', [$this, 'getBlockFormTrackingEventName'], 10, 2);

/**
 * Set tracking event name value.
 *
 * @param string $formType Type of form used like greenhouse, hubspot, etc.
 * @param string $formId Form ID.
 *
 * @return string
 */
public function getBlockFormTrackingEventName(string $formType, string $formId): string
{
	return 'Event-Name';
}
```

## Changing the default custom file preview remove label
This filter will override our default file preview remove label.

**Filter name:**
`es_forms_block_file_preview_remove_label`

**Default:**
```php
'Remove'
```

**Filter example:**
```php
// Set block file preview remove label.
add_filter('es_forms_block_file_preview_remove_label', [$this, 'getBlockFilePreviewRemoveLabel']);

/**
 * Set block file preview remove label.
 *
 * @return string
 */
public function getBlockFilePreviewRemoveLabel(): string
{
	return 'Remove item'; // This can be string or svg.
}
```

## Changing the form type selector on render
This filter will override the attribute-provided type selector for a Form component.
Passes form component attributes to the callback function as well, so you can check all sorts of conditions when filtering.

In other words, you can use this filter to change the value of the `formDataTypeSelector` attribute during a form render.
The attribute is used to output a `data-type-selector` HTML attribute of the form element.

**Filter name:**
`es_forms_block_form_data_type_selector`

**Default:**
Attribute-provided value, editable in the Block Editor.

**Filter example:**
```php
// Change data type selector.
add_filter('es_forms_block_form_data_type_selector', [$this, 'setIntegrationGreenhouseTypeSelector'], 10, 2);

/**
 * Change data type selector.
 * 
 * @param string $selector The data type selector to filter.
 * @param array<mixed> $attr Form component attributes.
 *
 * @return string Filtered value.
 */
public function getBlockFilePreviewRemoveLabel(string $selector, array $attr): string
{
	if (($attr['formType'] ?? '') === 'mailchimp') {
		return;
	}
	
	return 'my-new-selector';
}
```
