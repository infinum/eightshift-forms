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
			'esf:text-base',
			'esf:text-black',
			'esf:h-46',
			'esf:shadow-none',
			'esf:text-black',
			'esf:placeholder:text-gray-400',
			'esf:disabled:bg-gray-100',
			'esf:disabled:text-gray-400',
			'esf-focus-ring',
			'esf:group-[&.es-form-has-error]/field:border-red-500',
		],
		'help' => [
			'esf:text-gray-400',
			'esf:text-xs',
			'esf:[&_a]:text-accent',
			'esf:[&_a]:underline',
			'esf:[&_a]:hover:text-accent-dark',
		],
		'help-extended' => [
			'esf:[&_ul]:list-disc',
			'esf:[&_ul]:list-inside',
			'esf:[&_ul]:m-0',
			'esf:[&_ul]:p-0',
			'esf:[&_ul]:gap-5',
			'esf:[&_ul]:flex',
			'esf:[&_ul]:flex-col',
			'esf:[&_li]:m-0',
			'esf:[&_code]:text-gray-400',
			'esf:[&_code]:text-xs',
			'esf:[&_code]:m-0',
			'esf:[&_code]:px-3',
			'esf:[&_code]:py-1',
			'esf:[&_code]:bg-gray-100',
		],
		'after-content' => [
			'esf:text-gray-600',
			'esf:text-xs',
		],
		'label' => [
			'esf:text-base',
			'esf:block',
			'esf:p-0',
		],
		'select' => [
			'base' => [
				'esf:group/select',
				'esf:relative',
				'esf:overflow-hidden',
				'esf:text-base',
				'esf:text-black',
				'esf:[&.is-open]:overflow-visible',
				'esf-focus-ring',

				'esf:data-[type=select-one]:[&_.choices\_\_inner]:h-46',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:flex',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:items-center',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:p-10',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:pr-55',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:[&_.choices\_\_placeholder]:text-gray-400',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:[&_.choices\_\_placeholder]:[&_.choices\_\_button]:hidden',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:[&_.choices\_\_list]:w-full',
				'esf:data-[type=select-one]:[&_.choices\_\_inner]:[&_.choices\_\_item]:truncate',

				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:w-full',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:min-h-46',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:inline-block',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:pt-6',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:pl-10',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:pr-5',
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
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:mb-6',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:[&_.choices\_\_button]:top-0',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:[&_.choices\_\_button]:right-0',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:[&_.choices\_\_button]:h-full',
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_item]:[&_.choices\_\_button]:w-30',
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
				'esf:data-[type=select-multiple]:[&_.choices\_\_inner]:[&_.choices\_\_input--cloned]:text-base',
			],
			'parts' => [
				'select-choices-inner' => [
					'esf:group/select-inner',
					'esf:relative',
					'esf:text-base',
					'esf:text-black',
					'esf:bg-white',
					'esf:border',
					'esf:border-border',
					'esf:rounded-md',
					'esf:overflow-hidden',
					'esf:cursor-pointer',

					'esf:[&_select]:absolute',
					'esf:[&_select]:inset-0',
					'esf:[&_select]:pointer-events-none',
					'esf:[&_select]:opacity-0',

					"esf:after:content-['']",
					"esf:after:h-0",
					"esf:after:w-0",
					"esf:after:border-solid",
					"esf:after:border-transparent",
					"esf:after:border-t-border",
					"esf:after:border-7",
					"esf:after:absolute",
					"esf:after:right-10",
					"esf:after:top-19",
					"esf:after:pointer-events-none",

					'esf:group-[&.es-form-has-error]/field:border-red-500!',
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
					'esf:top-8',
					'esf:right-30',
					'esf:text-lg',
					'esf:w-29',
					'esf:h-29',
					'esf:flex',
					'esf:items-center',
					'esf:justify-center',
					'esf:text-accent',
					'esf:hover:text-accent-dark',
					'esf-focus-ring',
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
				'form' => [
					'base' => [
						'esf:text-sm',
					],
					'parts' => [
						'picker' => [
							'esf:text-sm',
						],
					],
				],
				'input' => [
					'base' => [
						'esf:text-sm',
					],
				],
				'date' => [
					'base' => [
						'esf:text-sm',
					]
				],
				'textarea' => [
					'base' => [
						'esf:text-sm',
					]
				],
				'phone' => [
					'base' => [
						'esf:text-sm',
					],
					'parts' => [
						'select' => [
							'esf:text-sm',
						],
					],
				],
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
							'esf:group-[&.esf-input-with-suffix]/field:flex-row',
						],
						'content-wrap' => [
							'esf:group-[&.esf-input-with-suffix]/field:flex-grow-1',
						],
						'label' => [
							'esf:text-sm',
						],
						'after-content' => [
							'esf:group-[&.esf-input-with-suffix]/field:px-10',
							'esf:group-[&.esf-input-with-suffix]/field:flex',
							'esf:group-[&.esf-input-with-suffix]/field:items-center',
							'esf:group-[&.esf-input-with-suffix]/field:justify-center',
							'esf:group-[&.esf-input-with-suffix]/field:border',
							'esf:group-[&.esf-input-with-suffix]/field:border-border',
							'esf:group-[&.esf-input-with-suffix]/field:h-46',
							'esf:group-[&.esf-input-with-suffix]/field:rounded-e-md',
							'esf:group-[&.esf-input-with-suffix]/field:-mx-8',
							'esf:group-[&.esf-input-with-suffix]/field:bg-gray-50',
						],
						'help' => Theme::THEME_SELECTORS['help-extended'],
					],
				],
				'checkbox' => [
					'base' => [
						'esf:[&.es-checkbox-toggle]:bg-transparent',
					],
					'parts' => [
						'label' => [
							'esf:text-sm',

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
							...Theme::THEME_SELECTORS['help-extended'],
						],
					],
				],
				'radio' => [
					'parts' => [
						'label' => [
							'esf:text-sm',
						],
						'help' => [
							...Theme::THEME_SELECTORS['help-extended'],
						],
					],
				],
				'select' => [
					'base' => [
						'esf:text-sm',
					],
					'parts' => [
						'select-choices-inner' => [
							'esf:text-sm',
						],
					],
				],
				'country' => [
					'base' => [
						'esf:text-sm',
					],
					'parts' => [
						'select-choices-inner' => [
							'esf:text-sm',
						],
					],
				],
				'submit' => [
					'base' => [
						'esf-button-primary',
						'esf:text-sm',
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
						'esf:rounded-md',
						'esf:translate-x-32',
						'esf:[&.es-form-is-active]:translate-x-0',
						// Other statuses added by frontend.
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
					'esf:flex',
					'esf:flex-col',
					'esf:gap-10',
					'esf:text-base',
					'esf:[&_*]:transition-colors',
					'esf:[&_*]:duration-300',
				],
				'parts' => [
					'fields' => [
						'esf:flex',
						'esf:flex-col',
						'esf:gap-20',
					],
				],
			],
			'form-edit-actions' => [
				'base' => [
					'esf:absolute',
					'esf:top-20',
					'esf:right-20',
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
					'content' => [
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
					'suffix-content' => Theme::THEME_SELECTORS['after-content'],
					'before-content' => Theme::THEME_SELECTORS['after-content'],
					'after-content' => Theme::THEME_SELECTORS['after-content'],
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
						'esf:text-sm',
						'esf:hidden',
						'esf:group-[&.es-form-has-error]/field:block',
					],
				],
			],
			'input' => [
				'base' => Theme::THEME_SELECTORS['input'],
			],
			'date' => [
				'base' => Theme::THEME_SELECTORS['input'],
				'parts' => [
					'picker' => [
						// Picker is not part of the form.
						'esf:[&_*]:transition-colors',
						'esf:[&_*]:duration-300',
						'esf:[&_*]:focus:outline-2',
						'esf:[&_*]:focus:outline-offset-2',
						'esf:[&_*]:focus:outline-accent',
						'esf:[&_*]:focus:shadow-none',
						'esf:[&_*]:focus:rounded-md',
						'esf:text-black',
						'esf:shadow-none',
						'esf:rounded-md',
						'esf:border',
						'esf:border-border',
						'esf:text-base',
						'esf:[&_.flatpickr-day]:hover:bg-accent-30',
						'esf:[&_.flatpickr-day]:hover:border-accent-30',
						'esf:[&_.flatpickr-day]:focus:bg-accent-30',
						'esf:[&_.flatpickr-day]:focus:border-accent-30',
						'esf:[&_.selected]:bg-accent',
						'esf:[&_.selected]:border-accent',
						'esf:[&_.selected]:hover:bg-accent',
						'esf:[&_.selected]:hover:border-accent',
						'esf:[&_.selected]:focus:bg-accent',
						'esf:[&_.selected]:focus:border-accent',
						'esf:[&_.today]:border-border',
						'esf:[&_.today]:hover:bg-accent-30',
						'esf:[&_.today]:hover:border-accent-30',
						'esf:[&_.today]:hover:text-black',
						'esf:[&_.today]:focus:bg-accent-30',
						'esf:[&_.today]:focus:border-accent-30',
						'esf:[&_.today]:focus:text-black',
						'esf:[&_.flatpickr-next-month:hover>svg]:fill-accent',
						'esf:[&_.flatpickr-prev-month:hover>svg]:fill-accent',
					]
				]
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
						'esf:after:transition-opacity',
						'esf:after:duration-300',
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
						'esf:before:duration-300',
						'esf:peer-checked/checkbox:before:bg-accent',
						'esf:peer-checked/checkbox:before:border-accent',
						'esf:peer-focus/checkbox:before:outline-2',
						'esf:peer-focus/checkbox:before:outline-offset-2',
						'esf:peer-focus/checkbox:before:outline-accent',
						'esf:peer-focus/checkbox:before:shadow-none',
						'esf:peer-focus/checkbox:before:rounded-md',
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
						'esf:after:transition-opacity',
						'esf:after:duration-300',
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
						'esf:before:duration-300',
						'esf:peer-checked/radio:before:bg-accent',
						'esf:peer-checked/radio:before:border-accent',
						'esf:peer-focus/radio:before:outline-2',
						'esf:peer-focus/radio:before:outline-offset-2',
						'esf:peer-focus/radio:before:outline-accent',
						'esf:peer-focus/radio:before:shadow-none',
						'esf:peer-focus/radio:before:rounded-full',
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
						"esf:group-[[data-phone-disable-picker='']]/form:grid",
						"esf:group-[[data-phone-disable-picker='']]/form:gap-10",
						"esf:group-[[data-phone-disable-picker='']]/form:grid-cols-[min(120px)_1fr]",
					]
				],
			],
			'rating' => [
				'base' => [
					'esf:flex',
					'esf:flex-row',
					'esf:gap-2',
				],
				'parts' => [
					'star' => [
						'esf:sr-only',
					],
					'label' => [
						'esf:text-gray-300',
						'esf:cursor-pointer',
						'esf:[&:has(~_input:checked)]:text-accent',
						'esf:[input:checked_+_&]:text-accent',
						'esf:[input:focus-visible_+_&]:outline-2',
						'esf:[input:focus-visible_+_&]:outline-offset-2',
						'esf:[input:focus-visible_+_&]:outline-accent',
						'esf:[input:focus-visible_+_&]:shadow-none',
						'esf:[input:focus-visible_+_&]:rounded-full',
					],
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
						'esf:[&_.dz-success-mark]:hidden',
						'esf:[&_.dz-error-mark]:hidden',
						'esf:[&_.dz-progress]:col-span-2',
						'esf:[&_.dz-progress]:row-start-2',
						'esf:[&_.dz-progress]:bg-gray-100',
						'esf:[&_.dz-progress]:rounded-md',
						'esf:[&_.dz-progress]:h-5',
						'esf:[&_.dz-progress]:w-full',
						'esf:[&_.dz-progress]:overflow-hidden',
						'esf:[&_.dz-progress]:relative',
						'esf:[&_.dz-upload]:h-full',
						'esf:[&_.dz-upload]:absolute',
						'esf:[&_.dz-upload]:start-0',
						'esf:[&_.dz-upload]:top-0',
						'esf:[&_.dz-upload]:rounded-md',
						'esf:[&_.dz-upload]:bg-accent',
						'esf:[&_.dz-upload]:transition-width',
						'esf:[&_.dz-error-message]:row-start-3',
						'esf:[&_.dz-error-message]:col-span-2',
						'esf:[&_.dz-error-message]:text-red-500',
						'esf:[&_.dz-error-message]:text-base',
						'esf:[&_.dz-error-message]:pt-5',
						'esf:[&_.dz-details]:flex',
						'esf:[&_.dz-details]:flex-col',
						'esf:[&_.dz-details]:text-base',
						'esf:[&_.dz-details]:col-start-1',
						'esf:[&_.dz-details]:row-start-1',
						'esf:[&_.dz-filename]:order-1',
						'esf:[&_.dz-filename]:truncate',
						'esf:[&_.dz-size]:order-2',
						'esf:[&_.dz-preview]:grid',
						'esf:[&_.dz-preview]:gap-5',
						'esf:[&_.dz-remove]:col-start-2',
						'esf:[&_.dz-remove]:flex',
						'esf:[&_.dz-remove]:items-center',
						'esf:[&_.dz-remove]:justify-end',
						'esf:[&_.dz-remove]:text-accent',
						'esf:[&_.dz-remove]:underline',
						'esf:[&_.dz-remove]:text-xs',
						'esf:[&_.dz-remove]:hover:text-accent-dark',
						'esf:[&_.dz-remove]:focus:outline-2',
						'esf:[&_.dz-remove]:focus:outline-offset-2',
						'esf:[&_.dz-remove]:focus:outline-accent',
						'esf:[&_.dz-remove]:focus:shadow-none',
						'esf:[&_.dz-remove]:focus:rounded-md',
					],
					'button' => [
						'esf-button',
						'esf-button-primary-outline',
					],
					'custom-wrap' => [
						'esf:group-[&.dz-max-files-reached]/file:opacity-50',
						'esf:group-[&.dz-max-files-reached]/file:pointer-events-none',
						'esf:group-[&.es-form-has-error]/file:border-red-500!',
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
						'esf-focus-ring',
					],
					'info' => [
						'esf:text-gray-500',
					],
				],
			],
			'submit' => [
				'base' => [
					'esf-button',
					'esf-button-primary',
				],
			],
			'global-msg' => [
				'base' => [
					'esf:w-full',
					'esf:rounded-md',
					'esf:flex',
					'esf:flex-col',
					'esf:gap-2',
					'esf:invisible',
					'esf:border',
					'esf:[&>div]:px-20',
					'esf:[&>div]:py-10',
					'esf:[&>div>div]:font-bold',
					"esf:[&.es-form-is-active]:visible",
					"esf:data-[status='error']:bg-red-100",
					"esf:data-[status='error']:border-red-500",
					"esf:data-[status='error']:text-red-950",
					"esf:data-[status='success']:bg-green-100",
					"esf:data-[status='success']:border-green-500",
					"esf:data-[status='success']:text-green-950",
				],
			],
		];
	}
}
