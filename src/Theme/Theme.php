<?php

/**
 * Theme class.
 *
 * @package EightshiftForms\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Theme;

use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Settings\SettingsSettings;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Theme class.
 */
class Theme implements ServiceInterface
{
	/**
	 * @var array<string, mixed>|null
	 */
	private ?array $adminSelectorsCache = null;

	/**
	 * @var array<string, mixed>|null
	 */
	private ?array $frontendSelectorsCache = null;

	/**
	 * Admin selectors.
	 *
	 * @var array<string, array<string, array<string, string>>>
	 */
	public const THEME_SELECTORS = [
		'input' => [
			'esf:w-full',
			'esf:border',
			'esf:border-border',
			'esf:bg-white',
			'esf:p-10',
			'esf:rounded-md',
			'esf:text-sm',
			'esf:h-42',
			'esf:shadow-none',
			'esf:text-black',
			'esf:placeholder:text-gray-400',
			'esf:disabled:bg-gray-100',
			'esf:disabled:text-gray-400',
			'esf:read-only:bg-gray-100',
			'esf:read-only:text-gray-600',
			'esf:focus:outline-2',
			'esf:focus:outline-offset-2',
			'esf:focus:outline-accent',
			'esf:focus:shadow-none',
			'esf:group-[&.es-form-has-error]/field:border-red-500',
		],
		'help' => [
			'esf:text-gray-400 esf:text-xs/17',
			'esf:mt-5',
			'esf:[&_code]:text-gray-400',
			'esf:[&_code]:text-xs/17',
			'esf:[&_code]:m-0',
			'esf:[&_code]:px-3',
			'esf:[&_code]:py-1',
			'esf:[&_code]:bg-gray-100',
			'esf:[&_a]:text-accent',
			'esf:[&_a]:underline',
			'esf:[&_a]:hover:text-accent-dark',
			'esf:[&_a]:transition-colors',
			'esf:[&_a]:duration-300',
			'esf:[&_ul]:list-disc',
			'esf:[&_ul]:list-inside',
			'esf:[&_ul]:m-0',
			'esf:[&_ul]:p-0',
			'esf:[&_ul]:gap-5',
			'esf:[&_ul]:flex',
			'esf:[&_ul]:flex-col',
			'esf:[&_li]:m-0',
		],
		'label' => [
			'esf:text-sm',
			'esf:block',
			'esf:p-0',
			'esf:transition-colors',
			'esf:duration-300',
		],
		'select' => [
			'base' => [
				'esf:group/select',
				'esf:relative',
				'esf:overflow-hidden',
				'esf:text-sm',
				'esf:text-black',
				'esf:[&.is-open]:overflow-visible',

				'esf:data-[type=select-one]:[&_.choices\_\_inner]:bg-white',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:border',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:border-border',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:rounded-md',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:h-44',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:overflow-hidden',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:flex',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:items-center',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:p-10',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:pr-40',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:cursor-pointer',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:[&_.choices\_\_placeholder]:text-gray-400',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:[&_.choices\_\_placeholder]:[&_.choices\_\_button]:hidden',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:[&_.choices\_\_list]:w-full',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:[&_.choices\_\_item]:truncate',

				'esf:data-[type=select-one]:[&_.choices\_\_inner]:group-[&.es-form-has-error]/field:border-red-500',

				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:w-full',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:bg-white',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:border',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:border-border',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:rounded-md',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:min-h-44',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:overflow-hidden',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:inline-block',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:pt-7',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:pl-10',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:pr-5',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:cursor-pointer',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:align-bottom',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_list]:inline',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_list]:align-top',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:inline-block',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:align-middle',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:rounded-full',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:py-4',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:pl-10',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:pr-35',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:bg-accent',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:text-white',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:relative',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:mr-7',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:mb-7',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:[&_.choices\_\_button]:text-white',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:[&_.choices\_\_button]:hover:text-accent-dark',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:border-0',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:min-h-28',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:h-28',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:p-0',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:m-0',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:focus:outline-none',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:focus:shadow-none',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:placeholder:text-gray-400',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:text-sm',
			],
			'parts' => [
				'select-choices-inner' => [
					'esf:group/select-inner',
					'esf:text-sm',
					'esf:text-black',

					'esf:[&_select]:absolute',
					'esf:[&_select]:inset-0',
					'esf:[&_select]:pointer-events-none',
					'esf:[&_select]:opacity-0',
				],
				'select-list' => [
					'esf:m-0',
					'esf:pl-0',
					'esf:list-none',
				],
				'select-list-dropdown' => [
					'esf:hidden',
					'esf:z-100',
					'esf:absolute',
					'esf:w-full',
					'esf:bg-white',
					'esf:border',
					'esf:border-border',
					'esf:top-full',
					'esf:inset-x-0',
					'esf:mt-2',
					'esf:rounded-md',
					'esf:overflow-hidden',
					'esf:break-all',

					'esf:group-[&.is-open]/select:block',

					'esf:[&_.choices\_\_list]:relative',
					'esf:[&_.choices\_\_list]:max-h-300',
					'esf:[&_.choices\_\_list]:overflow-auto',
					'esf:[&_.choices\_\_list]:will-change-scroll',

					'esf:[&_.choices\_\_item]:relative',
					'esf:[&_.choices\_\_item]:p-10',
					'esf:[&_.choices\_\_item]:transition-colors',
					'esf:[&_.choices\_\_item]:duration-300',

					'esf:[&_.choices\_\_placeholder]:hidden',

					'esf:[&_.choices\_\_input]:w-full!',
					'esf:[&_.choices\_\_input]:min-w-full!',
					'esf:[&_.choices\_\_input]:p-10',
					'esf:[&_.choices\_\_input]:border-0',
					'esf:[&_.choices\_\_input]:border-b',
					'esf:[&_.choices\_\_input]:border-border',
					'esf:[&_.choices\_\_input]:rounded-none',
					'esf:[&_.choices\_\_input]:bg-transparent',
					'esf:[&_.choices\_\_input]:focus:outline-none',
					'esf:[&_.choices\_\_input]:focus:shadow-none',

					'esf:[&_.choices\_\_item--selectable]:cursor-pointer',
					'esf:[&_.choices\_\_item--selectable]:hover:bg-accent-30',
					'esf:[&_.choices\_\_item--selectable]:[&.is-highlighted]:bg-accent-30',
					'esf:[&_.choices\_\_item--selectable]:[&.is-selected]:bg-accent',
					'esf:[&_.choices\_\_item--selectable]:[&.is-selected]:text-white',
				],
				'select-item-disabled' => [
					'esf:opacity-50',
					'esf:cursor-not-allowed',
				],
				'select-button' => [
					'esf:appearance-none',
					'esf:border-0',
					'esf:bg-transparent',
					'esf:bg-no-repeat',
					'esf:bg-center',
					'esf:cursor-pointer',
					'esf:p-0',
					'esf:absolute',
					'esf:inset-y-0',
					'esf:right-0',
					'esf:text-lg',
					'esf:w-40',
					'esf:h-full',
					'esf:flex',
					'esf:items-center',
					'esf:justify-center',
					'esf:text-accent',
					'esf:rounded-full',
					'esf:transition-colors',
					'esf:duration-300',
					'esf:hover:text-accent-dark',
				],
			],
		],
	];

	/**
	 * Register the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(HooksHelpers::getFilterName(['blocks', 'tailwindSelectorsAdmin']), [$this, 'getBlockFormsTailwindSelectorsAdmin']);
		\add_filter(HooksHelpers::getFilterName(['blocks', 'tailwindSelectors']), [$this, 'getBlockFormsTailwindSelectorsFrontend']);
	}

	/**
	 * Get the block forms tailwind selectors for admin.
	 *
	 * @return array<string, mixed>
	 */
	public function getBlockFormsTailwindSelectorsAdmin(): array
	{
		if ($this->adminSelectorsCache !== null) {
			return $this->adminSelectorsCache;
		}

		$this->adminSelectorsCache = \array_merge_recursive(
			$this->getTheme(),
			[
				'checkboxes' => [
					'parts' => [
						'field-label' => [
							'esf:font-semibold',
						],
					],
				],
				'field' => [
					'parts' => [
						'content' => [
							'esf:group-[&.esf-input-with-suffix]/field:flex',
							'esf:group-[&.esf-input-with-suffix]/field:items-center',
						],
						'content-wrap' => [
							'esf:group-[&.esf-input-with-suffix]/field:flex-grow-1',
						],
						'after-content' => [
							'esf:group-[&.esf-input-with-suffix]/field:px-10',
							'esf:group-[&.esf-input-with-suffix]/field:flex',
							'esf:group-[&.esf-input-with-suffix]/field:items-center',
							'esf:group-[&.esf-input-with-suffix]/field:justify-center',
							'esf:group-[&.esf-input-with-suffix]/field:border',
							'esf:group-[&.esf-input-with-suffix]/field:border-border',
							'esf:group-[&.esf-input-with-suffix]/field:h-42',
							'esf:group-[&.esf-input-with-suffix]/field:rounded-e-md',
							'esf:group-[&.esf-input-with-suffix]/field:-mx-5',
							'esf:group-[&.esf-input-with-suffix]/field:bg-gray-50',
							'esf:group-[&.esf-input-with-suffix]/field:transition-colors',
							'esf:group-[&.esf-input-with-suffix]/field:duration-300',
						],
					],
				],
				'checkbox' => [
					'base' => [
						'esf:[&.es-checkbox-toggle]:bg-transparent',
					],
					'parts' => [
						'label' => [
							'esf:[.es-checkbox-toggle\_\_label]:relative',
							'esf:[.es-checkbox-toggle\_\_label]:block',
							'esf:[.es-checkbox-toggle\_\_label]:w-full',
							'esf:[.es-checkbox-toggle\_\_label]:pl-0',
							'esf:[.es-checkbox-toggle\_\_label]:pr-44',
							'esf:[.es-checkbox-toggle\_\_label]:min-h-24',
							"esf:[.es-checkbox-toggle\_\_label]:before:content-['']",
							'esf:[.es-checkbox-toggle\_\_label]:before:absolute',
							'esf:[.es-checkbox-toggle\_\_label]:before:top-0',
							'esf:[.es-checkbox-toggle\_\_label]:before:start-auto',
							'esf:[.es-checkbox-toggle\_\_label]:before:end-0',
							'esf:[.es-checkbox-toggle\_\_label]:before:bg-white',
							'esf:[.es-checkbox-toggle\_\_label]:before:border-1',
							'esf:[.es-checkbox-toggle\_\_label]:before:border-border',
							'esf:[.es-checkbox-toggle\_\_label]:before:w-44',
							'esf:[.es-checkbox-toggle\_\_label]:before:h-22',
							'esf:[.es-checkbox-toggle\_\_label]:before:rounded-full',
							'esf:[.es-checkbox-toggle\_\_label]:before:transition-colors',
							'esf:[.es-checkbox-toggle\_\_label]:peer-checked/checkbox:before:bg-accent',
							'esf:[.es-checkbox-toggle\_\_label]:peer-checked/checkbox:before:border-accent',

							"esf:[.es-checkbox-toggle\_\_label]:after:content-['']",
							'esf:[.es-checkbox-toggle\_\_label]:after:opacity-100',
							'esf:[.es-checkbox-toggle\_\_label]:after:absolute',
							'esf:[.es-checkbox-toggle\_\_label]:after:top-3',
							'esf:[.es-checkbox-toggle\_\_label]:after:start-auto',
							'esf:[.es-checkbox-toggle\_\_label]:after:end-25',
							'esf:[.es-checkbox-toggle\_\_label]:after:rounded-full',
							'esf:[.es-checkbox-toggle\_\_label]:after:bg-gray-300',
							'esf:[.es-checkbox-toggle\_\_label]:after:h-16',
							'esf:[.es-checkbox-toggle\_\_label]:after:w-16',
							'esf:[.es-checkbox-toggle\_\_label]:after:transition-all',
							'esf:[.es-checkbox-toggle\_\_label]:peer-checked/checkbox:after:translate-x-22',
							'esf:[.es-checkbox-toggle\_\_label]:peer-checked/checkbox:after:bg-white',
							'esf:[.es-checkbox-toggle\_\_label]:peer-checked/checkbox:after:transition-all',
							'esf:[.es-checkbox-toggle\_\_label]:peer-checked/checkbox:after:duration-300',
							'esf:[.es-checkbox-toggle\_\_label]:peer-checked/checkbox:after:bg-accent',
						],
						'help' => [
							'esf:[.es-checkbox-toggle\_\_help]:pr-50',
						],
					],
				],
				'submit' => [
					'base' => [
						'esf-button-primary',
					],
					'parts' => [
						'field-content-wrap' => [
							'esf:items-end',
						],
					],
				],
				'global-msg' => [
					'base' => [
						'esf:fixed',
						'esf:right-32',
						'esf:bottom-32',
						'esf:z-100',
						'esf:max-w-288',
						'esf:max-h-[80vh]',
						'esf:overflow-x-hidden',
						'esf:border',

						'esf:rounded-md',
						'esf:px-16',
						'esf:py-12',
						'esf:opacity-0',
						'esf:translate-x-32',

						'esf:[&.es-form-is-active]:translate-x-0',
						'esf:[&.es-form-is-active]:opacity-100',
						'esf:data-[status="error"]:bg-red-100',
						'esf:data-[status="error"]:border-red-500',
						'esf:data-[status="error"]:text-red-950',
						'esf:data-[status="success"]:bg-green-100',
						'esf:data-[status="success"]:border-green-500',
						'esf:data-[status="success"]:text-green-950',
						'esf:data-[status="warning"]:bg-yellow-100',
						'esf:data-[status="warning"]:border-yellow-500',
						'esf:data-[status="warning"]:text-yellow-950',
						'esf:data-[status="info"]:bg-sky-100',
						'esf:data-[status="info"]:border-sky-500',
						'esf:data-[status="info"]:text-sky-950',
					],
				],
				'loader' => [
					'base' => [
						'esf:z-99999',
						'esf:fixed!',
						'esf:w-screen!',
						'esf:h-screen!',
					],
					'parts' => [
						'overlay' => [
							'esf:fixed!',
						],
					],
				],
			]
		);

		return $this->adminSelectorsCache;
	}

	/**
	 * Get the block forms tailwind selectors for frontend.
	 *
	 * @return array<string, mixed>
	 */
	public function getBlockFormsTailwindSelectorsFrontend(): array
	{
		if ($this->frontendSelectorsCache !== null) {
			return $this->frontendSelectorsCache;
		}

		if (SettingsHelpers::isOptionCheckboxChecked(SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SELECTORS_KEY, SettingsSettings::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY)) {
			return [];
		}

		$this->frontendSelectorsCache = $this->getTheme();

		return $this->frontendSelectorsCache;
	}

	/**
	 * Get the theme selectors for admin and frontend.
	 *
	 * @return array<string, mixed>
	 */
	public function getTheme(): array
	{
		return [
			'forms' => [
				'base' => [
					'esf:relative',
				],
			],
			'form' => [
				'base' => [
					'esf:group/form',
				],
				'parts' => [
					'fields' => [
						'esf:flex esf:flex-col esf:gap-20',
					],
				],
			],
			'form-edit-actions' => [
				'base' => [
					'esf:absolute',
					'esf:top-0',
					'esf:right-0',
					'esf:z-100',
					'esf:bg-white',
					'esf:rounded-md',
					'esf:border',
					'esf:border-border',
					'esf:overflow-hidden',
					'esf:invisible',
					'esf:group-hover/form:visible',
				],
				'parts' => [
					'link' => [
						'esf:w-40',
						'esf:h-40',
						'esf:flex',
						'esf:items-center',
						'esf:justify-center',
						'esf:text-accent',
						'esf:cursor-pointer',
						'esf:transition-colors',
						'esf:duration-300',
						'esf:hover:bg-accent-dark',
						'esf:hover:text-white',
					],
				],
			],
			'field' => [
				'base' => [
					'esf:group/field',
					'esf:relative',
					'esf:flex',
					'esf:flex-col',
					'esf:gap-10',
					'esf:border-none',
					'esf:mx-0',
					'esf:p-0',
				],
				'parts' => [
					'inner' => [
						'esf:flex',
						'esf:flex-col',
						'esf:gap-5',
					],
					'content-wrap' => [
						'esf:flex',
						'esf:flex-col',
						'esf:gap-10',
					],
					'label' => Theme::THEME_SELECTORS['label'],
					'help' => Theme::THEME_SELECTORS['help'],
					'debug' => [
						'esf:opacity-0',
						'esf:text-xs',
						'esf:text-gray-500',
						'esf:p-10',
						'esf:border',
						'esf:border-border',
						'esf:rounded-md',
						'esf:bg-gray-100',
						'esf:pointer-events-none',
						'esf:absolute',
						'esf:right-0',
						'esf:top-0',
						'esf:z-10',
						'esf:bg-white',
						'esf:transition-opacity',
						'esf:duration-300',
						'esf:group-hover/field:opacity-100',
					],
					'error' => [
						'esf:text-red-500',
						'esf:text-xs',
						'esf:pt-5',
					],
				],
			],
			'input' => [
				'base' => Theme::THEME_SELECTORS['input'],
			],
			'textarea' => [
				'base' => [
					...Theme::THEME_SELECTORS['input'],
					'esf:min-h-200',
					'esf:h-auto',
				],
			],
			'checkbox' => [
				'parts' => [
					'input' => [
						'esf:peer/checkbox',
						'esf:sr-only',
					],
					'label' => [
						...Theme::THEME_SELECTORS['label'],
						'esf:cursor-pointer',

						'esf:relative',
						'esf:block',
						'esf:pl-30',
						'esf:min-h-20',
						'esf:after:content-["✕"]',
						'esf:after:absolute',
						'esf:after:top-0',
						'esf:after:start-0',
						'esf:after:w-20',
						'esf:after:h-20',
						'esf:after:flex',
						'esf:after:items-center',
						'esf:after:justify-center',
						'esf:after:text-white',
						'esf:after:text-xs',
						'esf:after:font-semibold',
						'esf:after:transition-opacity',
						'esf:after:opacity-0',
						'esf:peer-checked/checkbox:after:opacity-100',
						"esf:before:content-['']",
						'esf:before:absolute',
						'esf:before:top-0',
						'esf:before:start-0',
						'esf:before:bg-white',
						'esf:before:border-1',
						'esf:before:border-border',
						'esf:before:w-20',
						'esf:before:h-20',
						'esf:before:rounded-md',
						'esf:before:transition-colors',
						'esf:peer-checked/checkbox:before:bg-accent',
						'esf:peer-checked/checkbox:before:border-accent',
					],
					'help' => Theme::THEME_SELECTORS['help'],
				],
			],
			'radio' => [
				'parts' => [
					'input' => [
						'esf:peer/radio',
						'esf:sr-only',
					],
					'label' => [
						...Theme::THEME_SELECTORS['label'],
						'esf:cursor-pointer',

						'esf:relative',
						'esf:block',
						'esf:pl-30',
						'esf:min-h-20',
						'esf:after:absolute',
						'esf:after:top-4',
						'esf:after:start-4',
						'esf:after:w-12',
						'esf:after:h-12',
						'esf:after:flex',
						'esf:after:items-center',
						'esf:after:justify-center',
						'esf:after:text-white',
						'esf:after:text-xs',
						'esf:after:font-semibold',
						'esf:after:transition-opacity',
						'esf:after:opacity-0',
						'esf:after:bg-white',
						'esf:after:rounded-full',
						'esf:peer-checked/radio:after:opacity-100',
						"esf:before:content-['']",
						'esf:before:absolute',
						'esf:before:top-0',
						'esf:before:start-0',
						'esf:before:bg-white',
						'esf:before:border-1',
						'esf:before:border-border',
						'esf:before:w-20',
						'esf:before:h-20',
						'esf:before:rounded-full',
						'esf:before:transition-colors',
						'esf:peer-checked/radio:before:bg-accent',
						'esf:peer-checked/radio:before:border-accent',
					],
					'help' => Theme::THEME_SELECTORS['help'],
				],
			],
			'select' => [
				'base' => Theme::THEME_SELECTORS['select']['base'],
				'parts' => Theme::THEME_SELECTORS['select']['parts'],
			],
			'country' => [
				'base' => Theme::THEME_SELECTORS['select']['base'],
				'parts' => Theme::THEME_SELECTORS['select']['parts'],
			],
			'phone' => [
				'base' => [
					...Theme::THEME_SELECTORS['input'],
				],
				'parts' => [
					...Theme::THEME_SELECTORS['select']['parts'],
					'select' => Theme::THEME_SELECTORS['select']['base'],
					'field-content-wrap' => [
						'esf:grid',
						'esf:gap-10',
						'esf:grid-cols-[min(120px)_1fr]',
					]
				],
			],
			'loader' => [
				'base' => [
					'esf:hidden',
					'esf:absolute',
					'esf:inset-0',
					'esf:w-full',
					'esf:h-full',
					'esf:[&.es-form-is-active]:block',
					'esf:[&.es-form-is-active]:[&_.es-loader\_\_spinner]:animate-spin',
					'esf:[&.es-form-is-active]:[&_.es-loader\_\_spinner]:duration-500',
				],
				'parts' => [
					'overlay' => [
						'esf:absolute',
						'esf:inset-0',
						'esf:w-full',
						'esf:h-full',
						'esf:bg-white',
						'esf:opacity-50',
						'esf:z-10',
						'esf:transition-opacity',
						'esf:duration-300',
					],
					'spinner' => [
						'esf:sticky',
						'esf:left-1/2',
						'esf:top-1/2',
						'esf:-translate-x-1/2',
						'esf:-translate-y-1/2',
						'esf:z-20',
						'esf:w-40',
						'esf:h-40',
						'esf:flex',
						'esf:border-3',
						'esf:border-x-accent',
						'esf:border-y-transparent',
						'esf:rounded-full',
					],
				],
			],
			'file' => [
				'base' => [
					'esf:sr-only',
				],
				'parts' => [
					'field' => [
						'esf:group/file',
						'esf:[&_.dz-image]:hidden',
						'esf:[&_.dz-progress]:hidden',
						'esf:[&_.dz-success-mark]:hidden',
						'esf:[&_.dz-error-mark]:hidden',
						'esf:[&_.dz-progress]:hidden',
						'esf:[&_.dz-error-message]:text-red-500',
						'esf:[&_.dz-remove]:text-accent',
						'esf:[&_.dz-remove]:underline',
						'esf:[&_.dz-remove]:hover:text-accent-dark',
						'esf:[&_.dz-remove]:cursor-pointer',
						'esf:[&_.dz-remove]:transition-colors',
						'esf:[&_.dz-remove]:duration-300',
					],
					'button' => [
						'esf-button-primary-outline',
					],
					'custom-wrap' => [
						'esf:group-[&.dz-max-files-reached]/file:opacity-50',
						'esf:group-[&.dz-max-files-reached]/file:pointer-events-none',
						'esf:w-full',
						'esf:rounded-md',
						'esf:p-20',
						'esf:cursor-pointer',
						'esf:flex',
						'esf:flex-col',
						'esf:justify-center',
						'esf:items-center',
						'esf:gap-10',
						'esf:bg-white',
						'esf:border',
						'esf:border-border',
						'esf:border-dashed',
					],
					'info' => [
						'esf:text-gray-500',
					],
				],
			],
			'submit' => [
				'base' => [
					'esf-button-primary',
				],
			],
		];
	}
}
