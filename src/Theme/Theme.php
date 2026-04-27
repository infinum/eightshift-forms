<?php

/**
 * Theme class.
 *
 * @package EightshiftForms\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Theme;

use EightshiftForms\Helpers\HooksHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Theme class.
 */
class Theme implements ServiceInterface
{
	/**
	 * Admin selectors.
	 *
	 * @var array<string, array<string>>
	 */
	public const THEME_ADMIN_SELECTORS = [
		'input' => [
			'esf:w-full!',
			'esf:border!',
			'esf:border-border!',
			'esf:bg-white!',
			'esf:p-10!',
			'esf:rounded-md!',
			'esf:text-sm!',
			'esf:h-42!',
			'esf:shadow-none!',
			'esf:text-black!',
			'esf:placeholder:text-gray-400!',
			'esf:focus:border-accent!',
			'esf:focus:shadow-none!',
			'esf:focus:outline-none!',
			'esf:disabled:bg-gray-100!',
			'esf:disabled:text-gray-400!',
		],
		'help' => [
			'esf:text-gray-400 esf:text-xs/17',
			'esf:mt-5',
			'esf:[&_code]:text-gray-400! esf:[&_code]:text-xs/17! esf:[&_code]:m-0! esf:[&_code]:px-3! esf:[&_code]:py-1! esf:[&_code]:bg-gray-100!',
			'esf:[&_a]:text-accent! esf:[&_a]:underline! esf:[&_a]:hover:text-accent-dark! esf:[&_a]:transition-colors! esf:[&_a]:duration-300!',
			'esf:[&_ul]:list-disc! esf:[&_ul]:list-inside! esf:[&_ul]:m-0! esf:[&_ul]:p-0! esf:[&_ul]:gap-5! esf:[&_ul]:flex! esf:[&_ul]:flex-col',
			'esf:[&_li]:m-0!',
		],
		'label' => [
			'esf:text-sm',
			'esf:block',
			'esf:p-0',
			'esf:transition-colors',
			'esf:duration-300',
		],
	];

	/**
	 * Register the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(HooksHelpers::getFilterName(['blocks', 'tailwindSelectorsAdmin']), [$this, 'getBlockFormsTailwindSelectors']);
	}

	/**
	 * Get the block forms tailwind selectors.
	 *
	 * @return array<string, mixed>
	 */
	public function getBlockFormsTailwindSelectors(): array
	{
		return [
			'forms' => [
				'base' => [
					'esf:[&.es-form-is-geolocation-loading]:min-h-100',
					'esf:[&.es-form-is-geolocation-loading_.es-form]:hidden',
					'esf:[&.es-form-is-geolocation-loading>.es-loader__geolocation]:block',
				],
			],
			'form' => [
				'parts' => [
					'fields' => [
						'group/field',
						'esf:flex esf:flex-col esf:gap-20',
					],
				],
			],
			'field' => [
				'base' => [
					'esf:flex esf:flex-col esf:gap-10',
					'esf:border-none',
					'esf:mx-0',
					'esf:p-0',
				],
				'parts' => [
					'inner' => ['esf:flex esf:flex-col esf:gap-5', 'esf:max-w-[850px]'],
					'content-wrap' => [
						'esf:flex esf:flex-col esf:gap-10',
					],
					'label' => [
						...Theme::THEME_ADMIN_SELECTORS['label'],
					],
					'help' => Theme::THEME_ADMIN_SELECTORS['help'],
					'error' => [],
				],
			],
			'input' => [
				'base' => [
					...Theme::THEME_ADMIN_SELECTORS['input'],
				],
			],
			'textarea' => [
				'base' => [
					...Theme::THEME_ADMIN_SELECTORS['input'],
					'esf:min-h-200',
					'esf:h-auto!',
				],
			],
			'select' => [
				'base' => [
					...Theme::THEME_ADMIN_SELECTORS['input'],
				],
			],
			'country' => [
				'base' => [
					...Theme::THEME_ADMIN_SELECTORS['input'],
				],
			],
			'date' => [
				'base' => [
					...Theme::THEME_ADMIN_SELECTORS['input'],
				],
			],
			'phone' => [
				'base' => [
					...Theme::THEME_ADMIN_SELECTORS['input'],
				],
				'parts' => [
					'field-content-wrap' => [
						'esf:grid',
						'esf:gap-x-10',
						'esf:grid-cols-[min(120px)_1fr]',
					],
				],
			],
			'error' => [
				'base' => [
					'esf:text-red-500',
					'esf:text-xs',
					'esf:pt-5',
				],
			],
			'submit' => [
				'base' => [
					'esf-button-primary',
					'esf:w-fit',
				],
				'parts' => [
					'field-content-wrap' => [
						'esf:items-end',
					],
				],
			],
			'checkbox' => [
				'base' => [
					'esf:[&.es-checkbox-toggle]:bg-transparent',
				],
				'parts' => [
					'input' => [
						'esf:peer/checkbox',
						'esf:sr-only',
					],
					'label' => [
						...Theme::THEME_ADMIN_SELECTORS['label'],
						'esf:cursor-pointer',

						'esf:[.es-checkbox__label]:relative',
						'esf:[.es-checkbox__label]:block',
						'esf:[.es-checkbox__label]:pl-30',
						'esf:[.es-checkbox__label]:min-h-20',
						'esf:[.es-checkbox__label]:before:content-[\'\']',
						'esf:[.es-checkbox__label]:before:absolute',
						'esf:[.es-checkbox__label]:before:top-0',
						'esf:[.es-checkbox__label]:before:start-0',
						'esf:[.es-checkbox__label]:before:bg-white',
						'esf:[.es-checkbox__label]:before:border-1',
						'esf:[.es-checkbox__label]:before:border-border',
						'esf:[.es-checkbox__label]:before:w-20',
						'esf:[.es-checkbox__label]:before:h-20',
						'esf:[.es-checkbox__label]:before:rounded-sm',
						'esf:[.es-checkbox__label]:before:transition-colors',
						'esf:[.es-checkbox__label]:peer-checked/checkbox:before:bg-accent',
						'esf:[.es-checkbox__label]:peer-checked/checkbox:before:border-accent',

						'esf:[.es-checkbox-toggle__label]:relative',
						'esf:[.es-checkbox-toggle__label]:block',
						'esf:[.es-checkbox-toggle__label]:w-full',
						'esf:[.es-checkbox-toggle__label]:pr-50',
						'esf:[.es-checkbox-toggle__label]:min-h-24',
						'esf:[.es-checkbox-toggle__label]:before:content-[\'\']',
						'esf:[.es-checkbox-toggle__label]:before:absolute',
						'esf:[.es-checkbox-toggle__label]:before:top-0',
						'esf:[.es-checkbox-toggle__label]:before:end-0',
						'esf:[.es-checkbox-toggle__label]:before:bg-white',
						'esf:[.es-checkbox-toggle__label]:before:border-1',
						'esf:[.es-checkbox-toggle__label]:before:border-border',
						'esf:[.es-checkbox-toggle__label]:before:w-44',
						'esf:[.es-checkbox-toggle__label]:before:h-22',
						'esf:[.es-checkbox-toggle__label]:before:rounded-full',
						'esf:[.es-checkbox-toggle__label]:before:transition-colors',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:before:bg-accent',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:before:border-accent',

						'esf:[.es-checkbox-toggle__label]:after:content-[\'\']',
						'esf:[.es-checkbox-toggle__label]:after:absolute',
						'esf:[.es-checkbox-toggle__label]:after:top-[3px]',
						'esf:[.es-checkbox-toggle__label]:after:end-[24px]',
						'esf:[.es-checkbox-toggle__label]:after:rounded-full',
						'esf:[.es-checkbox-toggle__label]:after:bg-accent',
						'esf:[.es-checkbox-toggle__label]:after:h-16',
						'esf:[.es-checkbox-toggle__label]:after:w-16',
						'esf:[.es-checkbox-toggle__label]:after:transition-all',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:-translate-x-20',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:bg-white',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:end-[3px]',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:transition-all',
					],
					'help' => [
						...Theme::THEME_ADMIN_SELECTORS['help'],
						'esf:[.es-checkbox-toggle\_\_help]:pr-50',
					],
				],
			],
			'radio' => [
				'base' => [
					'esf:[&.es-form-is-disabled]:opacity-50',
				],
				'parts' => [
					'input' => [
						'esf:peer/checkbox',
						'esf:sr-only',
					],
					'content' => [
						'esf:flex',
						'esf:flex-row',
						'esf:gap-10',
						'esf:items-center',
					],
					'label' => [
						...Theme::THEME_ADMIN_SELECTORS['label'],
						'esf:cursor-pointer',
						'esf:flex',
						'esf:flex-row',
						'esf:gap-10',
						'esf:items-center',
						'esf:before:content-[\'\']',
						'esf:before:block',
						'esf:before:size-15',
						'esf:before:shrink-0',
						'esf:before:border',
						'esf:before:border-secondary-200',
						'esf:before:rounded-full',
						'esf:peer-checked/checkbox:before:bg-accent-500!',
						'esf:peer-checked/checkbox:before:border-accent-500!',
					],
				],
			],
			'radios' => [
				'parts' => [
					'field-content-wrap' => [
						'esf:group-[.es-field--radios-style-horizontal]/field:flex',
						'esf:group-[.es-field--radios-style-horizontal]/field:flex-wrap',
						'esf:group-[.es-field--radios-style-horizontal]/field:items-center',
						'esf:group-[.es-field--radios-style-horizontal]/field:gap-10',
					],
				],
			],
			'checkboxes' => [
				'parts' => [
					'field-content-wrap' => [
						'esf:group-[.es-field--checkboxes-style-horizontal]/field:flex',
						'esf:group-[.es-field--checkboxes-style-horizontal]/field:flex-wrap',
						'esf:group-[.es-field--checkboxes-style-horizontal]/field:items-center',
						'esf:group-[.es-field--checkboxes-style-horizontal]/field:gap-10',
					],
				],
			],
			'loader' => [
				'base' => [
					'esf:group/loader',
					'esf:hidden',
					'esf:absolute',
					'esf:inset-0',
					'esf:w-screen',
					'esf:h-screen',
					'esf:z-99999',
					'esf:[&.es-form-is-active]:block',
				],
				'parts' => [
					'overlay' => [
						'esf:fixed',
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
						'esf:absolute',
						'esf:left-1/2',
						'esf:top-1/2',
						'esf:-translate-x-1/2',
						'esf:-translate-y-1/2',
						'esf:z-20',
						'esf:w-40',
						'esf:h-40',
						'esf:flex',
						'esf:border-3',
						'esf:border-x-accent-500',
						'esf:border-y-transparent',
						'esf:rounded-full',
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

					// 'esf:hidden',
					'esf:[&.es-form-is-active]:translate-x-0',
					'esf:[&.es-form-is-active]:opacity-100',
					'esf:data-[status="error"]:bg-red-100 esf:data-[status="error"]:border-red-500 esf:data-[status="error"]:text-red-950',
					'esf:data-[status="success"]:bg-green-100 esf:data-[status="success"]:border-green-500 esf:data-[status="success"]:text-green-950',
					'esf:data-[status="warning"]:bg-yellow-100 esf:data-[status="warning"]:border-yellow-500 esf:data-[status="warning"]:text-yellow-950',
					'esf:data-[status="info"]:bg-sky-100 esf:data-[status="info"]:border-sky-500 esf:data-[status="info"]:text-sky-950',
				],
			],
			'file' => [
				'base' => [
					'esf:focus:outline-none',
					'esf:absolute',
					'esf:-z-10',
					'esf:opacity-0',
				],
				'parts' => [
					'button' => [
						'esf:order-2',
						'esf:mr-5',
						'esf:text-secondary-600',
						'esf:hover:underline',
					],
					'custom-wrap' => [
						'esf:w-full',
						'esf:cursor-pointer',
						'esf:text-gray-500',
						'esf:text-sm',
						'esf:py-10',
						'esf:bg-white',
						'esf:border',
						'esf:border-secondary-400',
						'esf:border-dashed',
						'esf:flex',
						'esf:flex-row',
						'esf:flex-wrap',
						'esf:justify-center',
						'esf:[&.dz-max-files-reached]:opacity-50',
					],
					'info' => [
						'esf:text-gray-500',
						'esf:text-sm',
						'esf:order-3',
					],
				],
			],
			'step' => [
				'base' => [
					'esf:hidden',
					'esf:w-full',
					'esf:[&.es-form-is-active]:block',
				],
				'parts' => [
					'debug-details' => [
						'esf:hidden',
					],
					'inner' => [
						'esf:flex',
						'esf:flex-wrap',
						'esf:w-full',
					],
					'navigation' => [
						'esf:flex',
						'esf:items-center',
						'esf:justify-between',
						'esf:w-full',
					],
					'navigation-inner' => [
						'esf:flex',
						'esf:items-center',
						'esf:justify-between',
						'esf:w-full',
					],
					'navigation-next' => [
						'esf:ml-auto',
					],
				],
			],
			'progress-bar' => [
				'base' => [
					'esf:w-full',
					'esf:flex',
					'esf:items-center',
				],
				'parts' => [
					'item' => [
						'esf:relative',
						'esf:z-20',
						'esf:border',
						'esf:w-20',
						'esf:h-20',
						'esf:border-secondary-300',
						'esf:bg-white',
						'esf:rounded-full',
						'esf:flex',
						'esf:items-center',
						'esf:justify-center',
						'esf:text-xs',
						'esf:text-gray-500',
						'esf:transition-colors',
						'esf:[&.es-form-is-active]:border-accent-600',
						'esf:[&.es-form-is-active]:bg-accent-600',
						'esf:[&.es-form-is-active]:text-white',
						'esf:[&.es-form-is-filled]:border-accent-600',
						'esf:[&.es-form-is-filled]:bg-accent-600',
						'esf:[&.es-form-is-filled]:text-white',
					],
					'item-inner' => [
						'esf:text-xs',
					],
					'multistep' => [
						'esf:relative',
						'esf:flex',
						'esf:justify-between',
						'esf:gap-10',
						'esf:w-full',
						'esf:before:content-[\'\']',
						'esf:before:border-t',
						'esf:before:border-secondary-300',
						'esf:before:border-dashed',
						'esf:before:z-10',
						'esf:before:absolute',
						'esf:before:top-10',
						'esf:before:left-0',
						'esf:before:right-0',
					],
					'multiflow' => [
						'esf:relative',
						'esf:flex',
						'esf:justify-between',
						'esf:gap-10',
						'esf:w-full',
						'esf:before:content-[\'\']',
						'esf:before:border-t',
						'esf:before:border-secondary-300',
						'esf:before:border-dashed',
						'esf:before:z-10',
						'esf:before:absolute',
						'esf:before:top-10',
						'esf:before:left-0',
						'esf:before:right-0',
					],
				],
			],
			'range' => [
				'base' => [
					'esf:w-full',
					'esf:cursor-pointer',
					'esf:appearance-none',
					'esf:h-5',
					'esf:rounded-full',
					'esf:bg-secondary-200',
					'esf:focus:outline-none',
				],
				'parts' => [
					'field-content-wrap' => [
						'esf:flex',
						'esf:flex-wrap',
						'esf:items-center',
						'esf:justify-between',
						'esf:gap-5',
					],
					'min' => [
						'esf:text-gray-500',
						'esf:text-xs',
					],
					'max' => [
						'esf:text-gray-500',
						'esf:text-xs',
					],
					'current' => [
						'esf:text-secondary-400',
						'esf:text-xs',
					],
				],
			],
			'form-edit-actions' => [
				'base' => [
					'esf:flex',
					'esf:items-center',
					'esf:gap-5',
				],
				'parts' => [
					'link' => [
						'esf:text-secondary-400',
						'esf:hover:text-accent-600',
						'esf:transition-colors',
						'esf:duration-300',
						'esf:[&>svg]:w-20',
						'esf:[&>svg]:h-20',
					],
				],
			],
			'rating' => [
				'base' => [
					'esf:inline-flex',
					'esf:items-center',
					'esf:relative',
				],
				'parts' => [
					'star' => [
						'esf:flex',
						'esf:cursor-pointer',
						'esf:[&_*]:select-none!',
						'esf:[&_*]:pointer-events-none!',
					],
				],
			],
		];
	}
}
