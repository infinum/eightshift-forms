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
			'esf:focus:outline-2 esf:focus:outline-offset-2 esf:focus:outline-accent esf:focus:shadow-none'
		],
		'help' => [
			'esf:text-gray-400 esf:text-xs/17',
			'esf:mt-5',
			'esf:[&_code]:text-gray-400 esf:[&_code]:text-xs/17 esf:[&_code]:m-0 esf:[&_code]:px-3 esf:[&_code]:py-1 esf:[&_code]:bg-gray-100',
			'esf:[&_a]:text-accent esf:[&_a]:underline esf:[&_a]:hover:text-accent-dark esf:[&_a]:transition-colors esf:[&_a]:duration-300',
			'esf:[&_ul]:list-disc esf:[&_ul]:list-inside esf:[&_ul]:m-0 esf:[&_ul]:p-0 esf:[&_ul]:gap-5 esf:[&_ul]:flex esf:[&_ul]:flex-col',
			'esf:[&_li]:m-0',
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
			'form' => [
				'parts' => [
					'fields' => [
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
					'esf:h-auto',
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
						'esf:[.es-checkbox__label]:after:content-["✓"]',
						'esf:[.es-checkbox__label]:after:absolute',
						'esf:[.es-checkbox__label]:after:top-0',
						'esf:[.es-checkbox__label]:after:start-0',
						'esf:[.es-checkbox__label]:after:w-20',
						'esf:[.es-checkbox__label]:after:h-20',
						'esf:[.es-checkbox__label]:after:flex',
						'esf:[.es-checkbox__label]:after:items-center',
						'esf:[.es-checkbox__label]:after:justify-center',
						'esf:[.es-checkbox__label]:after:text-white',
						'esf:[.es-checkbox__label]:after:text-xs',
						'esf:[.es-checkbox__label]:after:font-semibold',
						'esf:[.es-checkbox__label]:after:transition-opacity',
						'esf:[.es-checkbox__label]:after:opacity-0',
						'esf:[.es-checkbox__label]:peer-checked/checkbox:after:opacity-100',
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
						'esf:[.es-checkbox-toggle__label]:after:bg-gray-300',
						'esf:[.es-checkbox-toggle__label]:after:h-16',
						'esf:[.es-checkbox-toggle__label]:after:w-16',
						'esf:[.es-checkbox-toggle__label]:after:transition-all',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:translate-x-21',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:bg-white',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:transition-all',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:duration-300',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:bg-accent',
					],
					'help' => [
						...Theme::THEME_ADMIN_SELECTORS['help'],
						'esf:[.es-checkbox-toggle__help]:pr-50',
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
			'loader' => [
				'base' => [
					'esf:hidden',
					'esf:fixed',
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
		];
	}
}
