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
		// Input defaults.
		$input = [
			'esf:w-full!',
			'esf:border!',
			'esf:border-secondary-200!',
			'esf:bg-white!',
			'esf:p-10!',
			'esf:rounded-md!',
			'esf:text-sm!',
			'esf:h-42!',
			'esf:shadow-none!',
			'esf:text-secondary-900!',
			'esf:placeholder:text-secondary-400!',
			'esf:focus:border-accent-600!',
			'esf:focus:shadow-none!',
			'esf:focus:outline-none!',
		];
		$hidden = ['esf:opacity-50!'];
		$disabled = ['esf:bg-secondary-100! esf:text-secondary-400!'];
		$readonly = ['esf:bg-secondary-100! esf:text-secondary-400!'];
		$required = ['esf:bg-secondary-100! esf:text-secondary-400!'];

		$fieldset = [
			'esf:flex!',
			'esf:flex-col!',
			'esf:gap-10!',
			'esf:border!',
			'esf:border-secondary-200!',
		];

		$fieldsetItem = [
			'esf:py-8!',
			'esf:px-5!',
		];

		$fieldsetCheckbox = [
			'esf:flex!',
			'esf:flex-row!',
			'esf:gap-10!',
			'esf:items-center!',
			'esf:before:content-[\'\']',
			'esf:before:block!',
			'esf:before:size-15!',
			'esf:before:border!',
			'esf:before:border-secondary-200!',
		];

		$fieldsetRadio = [
			'esf:before:rounded-full!',
		];

		$fieldsetChecked = [
			'esf:before:bg-accent-500!',
			'esf:before:border-accent-500!',
		];

		$help = [
			'esf:text-secondary-400 esf:text-xs',
		];

		$label = [
			'esf:text-sm',
		];

		// $placeholder = 'placeholder:esf:text-primary-500';
		// $errorState = [
		// 	'group-[.es-form-has-error]/field:esf:bg-tertiary-100',
		// 	'group-[.es-form-has-error]/field:esf:border-tertiary-500',
		// ];
		// $baseFocus = 'focus:esf:outline-none';
		// $activeState = 'group-[.es-form-is-active]/field:esf:border-secondary-600';

		// // Checkbox/radio defaults.
		// $cBoxLabel = 'esf:cursor-pointer esf:relative esf:block esf:pl-8';
		// $cBoxMark = [
		// 	'before:esf:bg-0 before:esf:bg-no-repeat',
		// 	'before:esf:absolute before:esf:left-0 before:esf:top-0.5',
		// 	'before:esf:bg-center',
		// 	'before:esf:border before:esf:border-primary-300',
		// 	'before:esf:w-5 before:esf:h-5',
		// 	'before:esf:block',
		// ];
		// $cBoxFocus = [
		// 	'peer-focus/checkbox:before:esf:outline',
		// 	'peer-focus/checkbox:before:esf:outline-offset-1',
		// 	'peer-focus/checkbox:before:esf:outline-1',
		// 	'peer-focus/checkbox:before:esf:outline-secondary-600',
		// ];
		// $cBoxChecked = [
		// 	'peer-checked/checkbox:before:esf:border-secondary-600',
		// 	'peer-checked/checkbox:before:esf:bg-secondary-600',
		// ];

		// // Fake hide inputs so it can be focused on.
		// $hideInput = 'esf:absolute esf:-z-10 esf:opacity-0';

		return [
			'form' => [
				// 	'base' => [
				// 		'esf:-ms-5 esf:-me-5',
				// 		'esf:group/form'
				// 	],
				'parts' => [
					'fields' => [
						'group/field',
						'esf:flex esf:flex-col esf:gap-20',
					],
				],
				// ],
				// 'loader' => [
				// 	'base' => [
				// 		'esf:hidden',
				// 		'esf:absolute esf:top-0 esf:left-0',
				// 		'esf:w-full esf:h-full',
				// 		'esf:bg-white esf:bg-opacity-50',
				// 		'esf:flex esf:justify-center esf:items-center',
				// 		'[&.es-form-is-active]:esf:flex',
				// 	],
				// ],
				// 'global-msg' => [
				// 	'base' => [
				// 		'esf:w-full',
				// 		'esf:ps-5 esf:pe-5',
				// 		'esf:text-sm',
				// 		'[&.es-form-is-active]:esf:mb-6',
				// 		'[&>div]:esf:bg-primary-50',
				// 		'[&>div]:esf:border [&>div]:esf:border-primary-300',
				// 		'[&>div]:esf:p-5',
				// 		// '[&>div>div]:esf:font-bold [&>div>div]:esf:mb-1', // Title.
				// 		'[&>div>div]:esf:hidden', // Title.
				// 		'[&.es-form-has-error>div]:esf:bg-tertiary-50',
				// 		'[&.es-form-has-error>div]:esf:border-tertiary-300',
				// 	],
			],
			'field' => [
				'base' => [
					'esf:flex esf:flex-col esf:gap-10',
					// ...$fieldset,
					// 		'esf:group/field',
					// 		'[&.es-form-is-disabled]:esf:opacity-50 [&.es-form-is-disabled]:esf:!cursor-not-allowed',
				],
				'parts' => [
					'inner' => ['esf:flex esf:flex-col esf:gap-5'],
					'content-wrap' => [
						'esf:flex esf:flex-col esf:gap-10',
					],
					'label' => [
						...$label,
					],
					// 'label-inner' => [
					// 	// 			'esf:group/label',
					// 	'esf:text-primary-900 esf:text-sm esf:block',
					// 	// 			'group-[.es-form-has-error]/field:esf:text-tertiary-500',
					// 	// 			'group-[.es-form-is-disabled]/field:esf:opacity-40',
					// ],
					// 'after-content' => 'esf:flex esf:flex-col esf:gap-10',
					// 			"group-[.es-field\\_\\_label--is-required]/label:after:esf:content-['*']",
					// 			"group-[.es-field\\_\\_label--is-required]/label:after:esf:text-tertiary-600",
					// 			"group-[.es-field\\_\\_label--is-required]/label:after:esf:text-xs",
					// 		'after-content' => 'esf:text-primary-500 esf:text-xs esf:pt-2',
					'help' => $help,
					'error' => [
						// 'esf:text-tertiary-500 esf:text-xs',
					],
				],
			],
			'input' => [
				'base' => [
					...$input,
					// 'esf:h-12',
					// $placeholder,
					// $baseFocus,
					// ...$errorState,
					// $activeState,
				],
			],
			// 'date' => [
			// 	'base' => [
			// 		...$base,
			// 		'esf:h-12',
			// 		$placeholder,
			// 		$baseFocus,
			// 		...$errorState,
			// 		$activeState,
			// 	],
			// ],
			// 'range' => [
			// 	'base' => [
			// 		'esf:w-full esf:h-2.5',
			// 		'esf:cursor-pointer esf:appearance-none',
			// 		$baseFocus,
			// 		$activeState,
			// 		'esf:flex-auto',
			// 		'esf:border esf:border-primary-300',
			// 		'esf:bg-white',
			// 	],
			// 	'parts' => [
			// 		'min' => 'esf:text-primary-500 esf:text-xs',
			// 		'max' => 'esf:text-primary-500 esf:text-xs',
			// 		'current' => 'esf:text-primary-400 esf:text-xs',
			// 		'field-content-wrap' => [
			// 			'esf:flex esf:flex-wrap esf:items-center esf:justify-between esf:gap-2',
			// 		],
			// 	],
			// ],
			// 'rating' => [
			// 	'base' => [
			// 		'esf:align-middle',
			// 	],
			// ],
			// 'radios' => [
			// 	'parts' => [
			// 		'field-content-wrap' => [
			// 			// Style horizontal.
			// 			'group-[.es-field--radios-style-horizontal]/field:esf:flex',
			// 			'group-[.es-field--radios-style-horizontal]/field:esf:flex-wrap',
			// 			'group-[.es-field--radios-style-horizontal]/field:esf:items-center',
			// 			'group-[.es-field--radios-style-horizontal]/field:[&>div]:esf:mb-0',
			// 			'group-[.es-field--radios-style-horizontal]/field:[&>div]:esf:-mr-px',

			// 			// Style vertical.
			// 			'group-[.es-field--radios-style-vertical]/field:[&_label]:esf:flex',
			// 			'group-[.es-field--radios-style-vertical]/field:esf:flex-wrap',
			// 			'group-[.es-field--radios-style-vertical]/field:[&_label]:esf:justify-center',
			// 			'group-[.es-field--radios-style-vertical]/field:[&>div]:esf:-mb-px',
			// 		],
			// 	]
			// ],
			// 'radio' => [
			// 	'base' => [
			// 		'esf:mb-2 last:esf:mb-0',
			// 		'[&.es-form-is-disabled]:esf:opacity-40'
			// 	],
			// 	'parts' => [
			// 		'input' => [
			// 			'esf:peer/checkbox',
			// 			$hideInput,
			// 		],
			// 		'label' => [
			// 			$cBoxLabel,
			// 			...$cBoxMark,
			// 			...$cBoxChecked,
			// 			...$cBoxFocus,
			// 			'before:esf:rounded-full',
			// 			'peer-checked/checkbox:before:esf:bg-66',

			// 			// Style outline.
			// 			'before:group-[.es-field--radios-style-outline]/field:esf:hidden',
			// 			'group-[.es-field--radios-style-outline]/field:esf:border group-[.es-field--radios-style-outline]/field:esf:border-primary-300',
			// 			'group-[.es-field--radios-style-outline]/field:esf:transition group-[.es-field--radios-style-outline]/field:esf:ease-out',
			// 			'group-[.es-field--radios-style-outline]/field:esf:inline-flex',
			// 			'group-[.es-field--radios-style-outline]/field:esf:py-2.5 group-[.es-field--radios-style-outline]/field:esf:px-5',
			// 			'group-[.es-field--radios-style-outline]/field:hover:esf:bg-primary-100 group-[.es-field--radios-style-outline]/field:hover:esf:border-gradient-violet-dark',
			// 			'group-[.es-field--radios-style-outline]/field:peer-checked/checkbox:esf:bg-primary-100 group-[.es-field--radios-style-outline]/field:peer-checked/checkbox:esf:border-gradient-violet-dark',
			// 		],
			// 	],
			// ],
			'submit' => [
				'base' => [
					'esf:flex esf:justify-center esf:items-center esf:gap-5 esf:flex-row',
					'esf:w-full',
					'esf:p-10',
					'esf:rounded-md',
					'esf:text-sm!',
					'esf:text-white',
					'esf:bg-accent-600',
					'esf:font-medium!',
					'esf:transition-[background-color,color]',
					'esf:duration-300',
					'esf:ease-in-out',
					'esf:hover:bg-accent-700',
					'esf:hover:shadow-none',
					'esf:hover:outline-none',
					'esf:hover:cursor-pointer!',

					// Ghost variant.
					'esf:[&.es-submit--ghost]:bg-transparent',
					'esf:[&.es-submit--ghost]:text-accent-700',
					'esf:[&.es-submit--ghost]:hover:bg-accent-100',
				],
			],
			'checkbox' => [
				'base' => [
					'esf:[&.es-checkbox-toggle]:bg-transparent'
				],
				'parts' => [
					'input' => [
						'esf:peer/checkbox',
						'esf:sr-only',
					],
					'label' => [
						...$label,
						'esf:cursor-pointer',

						'esf:[.es-checkbox-toggle__label]:relative',
						'esf:[.es-checkbox-toggle__label]:block',
						'esf:[.es-checkbox-toggle__label]:w-full',
						'esf:[.es-checkbox-toggle__label]:before:content-[\'\']',
						'esf:[.es-checkbox-toggle__label]:before:absolute',
						'esf:[.es-checkbox-toggle__label]:before:top-0',
						'esf:[.es-checkbox-toggle__label]:before:end-0',
						'esf:[.es-checkbox-toggle__label]:before:bg-white',
						'esf:[.es-checkbox-toggle__label]:before:border-1',
						'esf:[.es-checkbox-toggle__label]:before:border-secondary-200',
						'esf:[.es-checkbox-toggle__label]:before:w-40',
						'esf:[.es-checkbox-toggle__label]:before:h-20',
						'esf:[.es-checkbox-toggle__label]:before:rounded-full',
						'esf:[.es-checkbox-toggle__label]:before:transition-colors',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:before:bg-accent-600',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:before:border-accent-600',

						'esf:[.es-checkbox-toggle__label]:after:content-[\'\']',
						'esf:[.es-checkbox-toggle__label]:after:absolute',
						'esf:[.es-checkbox-toggle__label]:after:top-[2px]',
						'esf:[.es-checkbox-toggle__label]:after:end-[2px]',
						'esf:[.es-checkbox-toggle__label]:after:rounded-full',
						'esf:[.es-checkbox-toggle__label]:after:bg-accent-600',
						'esf:[.es-checkbox-toggle__label]:after:h-16',
						'esf:[.es-checkbox-toggle__label]:after:w-16',
						'esf:[.es-checkbox-toggle__label]:after:transition-all',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:-translate-x-20',
						'esf:[.es-checkbox-toggle__label]:peer-checked/checkbox:after:bg-white',
					],
					'help' => [
						...$help,

						'esf:[.es-checkbox-toggle__help]:max-w-lg',
					],
				],
			],
			// 'file' => [
			// 	'base' => [
			// 		$baseFocus,
			// 		$hideInput,
			// 	],
			// 	'parts' => [
			// 		'button' => 'esf:order-2 esf:mr-1 esf:text-secondary-600 hover:esf:underline',
			// 		'custom-wrap' => [
			// 			'esf:w-full esf:cursor-pointer',
			// 			'esf:text-primary-500 esf:text-sm esf:py-10 esf:bg-white',
			// 			'esf:border esf:border-primary-400 esf:border-dotted',
			// 			'esf:flex esf:flex-row esf:flex-wrap esf:justify-center',
			// 			'group-[.dz-max-files-reached]/field:esf:opacity-50',
			// 			$activeState,
			// 			...$errorState,
			// 		],
			// 		'info' => 'esf:text-primary-500 esf:text-sm esf:order-3',
			// 		'field' => [
			// 			'[&_.dz-preview]:esf:flex [&_.dz-preview]:esf:flex-wrap [&_.dz-preview]:esf:items-center [&_.dz-preview]:esf:py-3 [&_.dz-preview]:esf:px-8 [&_.dz-preview]:esf:bg-primary-50 [&_.dz-preview]:esf:relative [&_.dz-preview]:esf:gap-2',
			// 			'max-md:[&_.dz-preview]:esf:px-3',
			// 			'[&_.dz-image]:esf:h-6 [&_.dz-image]:esf:w-6 [&_.dz-image]:esf:bg-center [&_.dz-image]:esf:bg-no-repeat [&_.dz-image]:esf:mr-4',
			// 			'max-md:[&_.dz-image]:esf:hidden',
			// 			'[&_.dz-details]:esf:flex [&_.dz-details]:esf:flex-col [&_.dz-details]:esf:overflow-hidden [&_.dz-details]:esf:pr-10',
			// 			'[&_.dz-size]:esf:text-xs [&_.dz-size]:esf:text-primary-500 [&_.dz-size]:esf:order-2',
			// 			'[&_.dz-filename]:esf:text-sm [&_.dz-filename]:esf:text-primary-900 [&_.dz-filename]:esf:order-1 [&_.dz-filename]:esf:truncate',
			// 			'[&_.dz-remove]:esf:absolute [&_.dz-remove]:esf:right-8 [&_.dz-remove]:esf:top-3',
			// 			'max-md:[&_.dz-remove]:esf:right-3',
			// 			'[&_.dz-progress]:esf:h-1.5 [&_.dz-progress]:esf:bg-primary-200 [&_.dz-progress]:esf:order-3 [&_.dz-progress]:esf:w-full [&_.dz-progress]:esf:relative',
			// 			'[&_.dz-preview.dz-success__.dz-progress]:esf:hidden',
			// 			'[&_.dz-error-message]:esf:w-full [&_.dz-error-message]:esf:text-tertiary-500 [&_.dz-error-message]:esf:text-xs [&_.dz-error-message]:esf:order-4',
			// 			'[&_.dz-upload]:esf:absolute [&_.dz-upload]:esf:top-0 [&_.dz-upload]:esf:left-0 [&_.dz-upload]:esf:h-full [&_.dz-upload]:esf:bg-secondary-600',
			// 			'[&_img]:esf:hidden [&_.dz-success-mark]:esf:hidden [&_.dz-error-mark]:esf:hidden',
			// 		],
			// 		'field-error' => [
			// 			'group-[.es-form-has-error]/field:esf:pb-2'
			// 		],
			// 	],
			// ],
			// 'phone' => [
			// 	'base' => [
			// 		...$base,
			// 		$placeholder,
			// 		$baseFocus,
			// 		...$errorState,
			// 		$activeState,
			// 		'esf:h-12',
			// 	],
			// 	'parts' => [
			// 		'field' => 'esf:group/phone',
			// 		'field-content-wrap' => [
			// 			'esf:flex',
			// 			'[&>div]:esf:flex-none'
			// 		],
			// 	],
			// ],
			'textarea' => [
				'base' => [
					...$input,
					'esf:min-h-200',
					// 		...$base,
					// 		$placeholder,
					// 		$baseFocus,
					// 		...$errorState,
					// 		$activeState,
				],
			],
			// 'step' => [
			// 	'base' => '',
			// 	'parts' => [
			// 		'debug-details' => '',
			// 		'inner' => [
			// 			'esf:w-full',
			// 			'esf:gap-y-6',
			// 			'[&>.es-field]:esf:ps-5 [&>.es-field]:esf:pe-5',
			// 		],
			// 		'navigation-inner' => [
			// 			'esf:flex esf:justify-between esf:items-center',
			// 		],
			// 		'navigation-prev' => '',
			// 		'navigation-next' => [
			// 			'esf:ml-auto',
			// 		],
			// 	],
			// ],
			// 'progress-bar' => [
			// 	'base' => [
			// 		'esf:w-full',
			// 		'esf:flex esf:items-center',
			// 		'esf:mb-3 esf:ps-5 esf:pe-5',
			// 	],
			// 	'parts' => [
			// 		'item-inner' => '',
			// 		'multiflow' => [
			// 			'esf:relative',
			// 			'esf:flex esf:justify-between',
			// 			'esf:gap-2',
			// 			'before:esf:border',
			// 			'before:esf:z-10',
			// 			'before:esf:w-11/12 before:esf:h-px',
			// 			'before:esf:border-primary-300 before:esf:border-dashed',
			// 			'before:esf:absolute before:esf:top-2.5 before:esf:left-w-3',

			// 			'[&>div]:esf:relative',
			// 			'[&>div]:esf:z-20',
			// 			'[&>div]:esf:border',
			// 			'[&>div]:esf:w-5 [&>div]:esf:h-5',
			// 			'[&>div]:esf:border-primary-300 [&>div]:esf:bg-white',
			// 			'[&>.es-form-is-filled]:esf:border-secondary-600 [&>.es-form-is-filled]:esf:bg-secondary-600',
			// 		],
			// 		'multistep' => [
			// 			'esf:relative',
			// 			'esf:flex esf:justify-between',
			// 			'esf:gap-2',
			// 			'before:esf:border',
			// 			'before:esf:z-10',
			// 			'before:esf:w-10/12 before:esf:h-px',
			// 			'before:esf:border-primary-300 before:esf:border-dashed',
			// 			'before:esf:absolute before:esf:top-5 before:esf:left-w-3',

			// 			'[&>div]:esf:border [&>div]:esf:border-primary-300',
			// 			'[&>div]:esf:py-2 [&>div]:esf:p-3',
			// 			'[&>div]:esf:bg-white',
			// 			'[&>div]:esf:z-20',
			// 			'[&>div]:esf:relative',
			// 			'[&>.es-form-is-active]:esf:text-white [&>.es-form-is-active]:esf:bg-secondary-600 [&>.es-form-is-active]:esf:border-secondary-600',
			// 		],
			// 	],
			// ],
		];
	}
}
