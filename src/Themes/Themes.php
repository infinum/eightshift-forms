<?php

/**
 * Themes class.
 *
 * @package EightshiftForms\Themes
 */

declare(strict_types=1);

namespace EightshiftForms\Themes;

use EightshiftForms\Helpers\HooksHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Themes class.
 */
class Themes implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(HooksHelpers::getFilterName(['blocks', 'tailwindSelectors']), [$this, 'getBlockFormsTailwindSelectors']);
	}

	/**
	 * Get the block forms tailwind selectors.
	 *
	 * @return array<string, mixed>
	 */
	public function getBlockFormsTailwindSelectors(): array
	{
		// Input defaults.
		$base = [
			'bg-white',
			'block w-full',
			'px-12 py-14',
			'text-base text-primary-90',
			'border border-neutral-50',
			'rounded-sm',
		];
		$placeholder = 'placeholder:text-neutral-50';
		$errorState = [
			'group-[.es-form-has-error]/field:border-error-50',
			'group-[.es-form-has-error]/field:text-error-50',
		];
		$baseFocus = 'focus:outline-none';
		$inputFocus = [
			'group-[&:not(.es-form-is-disabled)]/field:focus:outline-none',
			'group-[&:not(.es-form-is-disabled)]/field:focus-visible:ring-primary-50',
			'group-[&:not(.es-form-is-disabled)]/field:focus-visible:ring-2',
			'group-[&:not(.es-form-is-disabled)]/field:focus-visible:ring-offset-2',
			'group-[&:not(.es-form-is-disabled)]/field:focus-visible:border-neutral-80',
			'transition-shadow',
		];
		$activeState = 'group-[.es-form-is-active]/field:border-neutral-80';
		$hoverState = 'group-[&:not(.es-form-is-disabled)]/field:hover:border-neutral-80';
		$disabledState = [
			'group-[.es-form-is-disabled]/field:text-neutral-40',
			'group-[.es-form-is-disabled]/field:bg-neutral-5',
			'group-[.es-form-is-disabled]/field:border-neutral-50',
			'group-[.es-form-is-disabled]/field:cursor-not-allowed',
		];

		// Checkbox/radio defaults.
		$cBoxBase = [
			'group/cbox',
			'mb-12 last:mb-0',
			'[&.es-form-is-disabled]:opacity-50',
			'[&.es-form-is-disabled]:pointer-events-none',
		];
		$cBoxLabel = 'cursor-pointer relative block pl-36';
		$cBoxMark = [
			'before:bg-0 before:bg-no-repeat before:bg-center before:bg-white',
			'before:absolute before:left-0 before:top-0',
			'before:border before:border-primary-60',
			'before:w-24 before:h-24',
			'before:block',
			'before:ring-transparent',
			'before:ring-2',
			'before:ring-offset-0',
			'before:transition-shadow',
			'group-[&.es-form-is-disabled]/cbox:before:bg-primary-10',
			'group-[&.es-form-is-disabled]/cbox:before:border-primary-20',
		];
		$cBoxFocus = [
			'peer-focus-visible/checkbox:before:ring-primary-50',
			'peer-focus-visible/checkbox:before:ring-offset-2',
		];
		$cBoxHover = [
			'peer-hover/checkbox:before:ring-primary-50',
			'peer-hover/checkbox:before:ring-offset-2',
		];

		// Fake hide inputs so it can be focused on.
		$hideInput = 'absolute -z-10 opacity-0';

		return [
			'forms' => [
				'base' => [
					'relative',
					'text-start',
					'rounded-lg',
					'group/forms',

					// Padding.
					'[&.es-block-forms--has-inner-spacing]:p-24',
					'[&.es-block-forms--has-inner-spacing]:sm:p-48',

					// White background.
					'[&.es-block-forms--background-white]:bg-white',

					// Purple background (primary-5).
					'[&.es-block-forms--background-primary-5]:bg-primary-5',
				],
			],
			'form' => [
				'base' => [
					'-ms-12 -me-12',
				],
				'parts' => [
					'fields' => [
						'w-full',
						'gap-y-16',
						'[&>.es-field]:ps-6 [&>.es-field]:pe-6',
					],
				],
			],
			'loader' => [
				'base' => [
					'hidden',
					'absolute top-0 left-0',
					'w-full h-full',
					'bg-white bg-opacity-50',
					'flex justify-center items-center',
					'[&.es-form-is-active]:flex',
				],
			],
			'global-msg' => [
				'base' => [
					'w-full',
					'ps-12 pe-12',
					'text-neutral-80 text-sm',
					'[&.es-form-is-active]:mb-24',
					'[&>div]:rounded-sm',
					'[&>div]:bg-neutral-5',
					'[&>div]:border [&>div]:border-neutral-50',
					'[&>div]:p-20',
					'[&>div>div]:font-bold [&>div>div]:mb-4', // Title.
					'[&.es-form-has-error>div]:text-error-80',
					'[&.es-form-has-error>div]:bg-error-10',
					'[&.es-form-has-error>div]:border-error-30',
				],
			],
			'field' => [
				'base' => [
					'group/field',
					'[&.es-form-is-disabled]:!cursor-not-allowed',
				],
				'parts' => [
					'label' => [
						'text-neutral-80 text-sm font-medium block pb-4 ps-4',
						'group-[.es-field--is-required]/field:after:content-["*"]',
						'group-[.es-form-has-error]/field:text-error-50',
					],
					'before-content' => 'text-neutral-60 text-xs my-8 ps-4',
					'suffix-content' => 'text-error-60 text-xs my-8 ps-4',
					'after-content' => 'text-neutral-60 text-xs my-8 ps-4',
					'help' => 'text-neutral-60 text-sm mb-8 ps-4',
					'error' => [
						'text-error-50 text-xs ps-4',
						'group-[.es-form-has-error]/field:pt-8',
					],
				],
			],
			'input' => [
				'base' => [
					...$base,
					$placeholder,
					$baseFocus,
					...$inputFocus,
					...$errorState,
					$activeState,
					$hoverState,
					...$disabledState,
					'h-48',
				],
				'parts' => [
					'field-content-wrap' => [
						'input--field',
					],
				],
			],
			'textarea' => [
				'base' => [
					...$base,
					$placeholder,
					$baseFocus,
					...$inputFocus,
					...$errorState,
					$activeState,
					$hoverState,
					...$disabledState,
					'!overflow-y-auto !overflow-x-hidden',
					'h-auto',
					'min-h-128 max-h-320',
					'!resize-y',
				],
			],
			'date' => [
				'base' => [
					...$base,
					'h-48',
					$placeholder,
					$baseFocus,
					...$errorState,
					$activeState,
					$hoverState,
				],
			],
			'range' => [
				'base' => [
					'w-full h-12',
					'rounded-lg',
					'cursor-pointer appearance-none',
					$baseFocus,
					$activeState,
					'flex-auto',
					'border-none',
					'bg-primary-10',
				],
				'parts' => [
					'min' => 'text-neutral-60 text-sm',
					'max' => 'text-neutral-60 text-sm',
					'current' => 'text-neutral-60 text-sm',
					'thumb' => [
						'w-20 h-20',
						'rounded-full',
						'bg-primary-70',
					],
					'field-content-wrap' => [
						'flex flex-wrap items-center justify-between gap-4',
					],
				],
			],
			'rating' => [
				'base' => [
					'group-[.es-form-is-disabled]/field:opacity-50',
					'group-[.es-form-is-disabled]/field:pointer-events-none',
					'align-middle',
				],
				'parts' => [
					'field-label' => 'ps-0'
				],
			],
			'radios' => [
				'parts' => []
			],
			'radio' => [
				'base' => [
					...$cBoxBase,
				],
				'parts' => [
					'input' => [
						'peer/checkbox',
						$hideInput,
					],
					'label' => [
						$cBoxLabel,
						...$cBoxMark,
						...$cBoxFocus,
						...$cBoxHover,
						'before:rounded-full',
						'peer-checked/checkbox:before:border-none',
						'peer-checked/checkbox:before:bg-transparent',
						'peer-checked/checkbox:before:inset-ring-4',
						'peer-checked/checkbox:before:inset-ring-primary-60',
					],
				],
			],
			'checkboxes' => [
				'parts' => []
			],
			'checkbox' => [
				'base' => [
					...$cBoxBase,
				],
				'parts' => [
					'input' => [
						'peer/checkbox',
						$hideInput,
					],
					'label' => [
						$cBoxLabel,
						...$cBoxMark,
						...$cBoxFocus,
						...$cBoxHover,
						'before:rounded-xs',
						'peer-checked/checkbox:before:bg-primary-60',
						'peer-checked/checkbox:before:border-none',
						'group-[&.es-form-is-disabled]/cbox:peer-checked/checkbox:before:border-transparent',
						'group-[&.es-form-is-disabled]/cbox:peer-not-checked/checkbox:before:!bg-none',
					],
				],
			],
			'file' => [
				'base' => [
					$baseFocus,
					$hideInput,
				],
				'parts' => [
					'button' => 'order-2 mr-4 text-primary-60 underline',
					'custom-wrap' => [
						...$base,
						'flex flex-row flex-wrap justify-center items-baseline',
						'cursor-pointer',
						'py-40 border-dashed',
						'group-[.dz-max-files-reached]/field:opacity-50',
						$activeState,
						$hoverState,
						...$errorState,
						...$disabledState,
					],
					'info' => 'text-neutral-80 text-sm order-3',
					'field' => [
						'[&_.dz-preview]:rounded-xl [&_.dz-preview]:flex [&_.dz-preview]:flex-wrap [&_.dz-preview]:items-center [&_.dz-preview]:py-12 [&_.dz-preview]:px-8 [&_.dz-preview]:bg-neutral-20 [&_.dz-preview]:relative [&_.dz-preview]:gap-8',
						'max-md:[&_.dz-preview]:px-12',
						'[&_.dz-image]:h-24 [&_.dz-image]:w-24 [&_.dz-image]:bg-center [&_.dz-image]:bg-no-repeat [&_.dz-image]:mr-16',
						'max-md:[&_.dz-image]:hidden',
						'[&_.dz-details]:flex [&_.dz-details]:flex-col [&_.dz-details]:overflow-hidden [&_.dz-details]:pr-40',
						'[&_.dz-size]:text-sm [&_.dz-size]:text-neutral-40 [&_.dz-size]:order-2',
						'[&_.dz-filename]:text-sm [&_.dz-filename]:text-neutral-80 [&_.dz-filename]:order-1 [&_.dz-filename]:truncate',
						'[&_.dz-remove]:absolute [&_.dz-remove]:right-32 [&_.dz-remove]:top-12',
						'max-md:[&_.dz-remove]:right-12',
						'[&_.dz-progress]:h-6 [&_.dz-progress]:bg-primary-20 [&_.dz-progress]:order-3 [&_.dz-progress]:w-full [&_.dz-progress]:relative',
						'[&_.dz-preview.dz-success__.dz-progress]:hidden',
						'[&_.dz-error-message]:w-full [&_.dz-error-message]:text-error-50  [&_.dz-error-message]:text-sm [&_.dz-error-message]:order-4',
						'[&_.dz-upload]:absolute [&_.dz-upload]:top-0 [&_.dz-upload]:left-0 [&_.dz-upload]:h-full [&_.dz-upload]:bg-neutral-60',
						'[&_img]:hidden [&_.dz-success-mark]:hidden [&_.dz-error-mark]:hidden',
					],
					'field-error' => [
						'group-[.es-form-has-error]/field:pb-8'
					],
				],
			],
			'phone' => [
				'base' => [
					...$base,
					$placeholder,
					$baseFocus,
					...$errorState,
					$activeState,
					$hoverState,
					'h-48',
					'border-l-0',
					'rounded-s-none'
				],
				'parts' => [
					'field' => 'group/phone',
					'field-content-wrap' => [
						'flex',
						'[&>div]:flex-none'
					],
				],
			],
			'submit' => [
				'base' => '',
				'parts' => [
					'inner' => '',
					'field' => '',
					'field-inner' => '',
					'field-label' => '',
					'field-label-inner' => '',
					'field-before-content' => '',
					'field-content' => '',
					'field-content-wrap' => '',
					'field-after-content' => '',
					'field-help' => '',
					'field-error' => '',
				],
			],
			'step' => [
				'base' => '',
				'parts' => [
					'inner' => [
						'w-full',
						'gap-y-4',
						'[&>.es-field]:ps-12 [&>.es-field]:pe-12',
					],
					'navigation-inner' => [
						'flex justify-between items-center',
					],
					'navigation-prev' => '[&_button]:flex-row-reverse',
				],
			],
			'progress-bar' => [
				'base' => [
					'w-full',
					'flex items-center',
					'mb-12 ps-12 pe-12',
				],
				'parts' => [
					'item-inner' => '',
					'multiflow' => [
						'relative',
						'flex justify-between',
						'gap-8',
						'before:border',
						'before:z-10',
						'before:inset-x-40 before:h-px',
						'before:border-primary-50/20',
						'before:absolute before:top-10 before:left-w-12',

						'[&>div]:relative',
						'[&>div]:z-20',
						'[&>div]:border [&>div]:rounded-full',
						'[&>div]:w-20 [&>div]:h-20',
						'[&>div]:border-primary-50 [&>div]:bg-primary-5 [&>div]:rounded-sm',
						'[&>.es-form-is-filled]:border-primary-50 [&>.es-form-is-filled]:bg-primary-50',
					],
					'multistep' => [
						'relative',
						'flex justify-between',
						'gap-8',
						'text-sm',
						'text-neutral-80',
						'font-medium',
						'uppercase',
						'before:border',
						'before:z-10',
						'before:inset-x-40 before:h-px',
						'before:border-neutral-50/20',
						'before:absolute before:top-1/2 before:left-w-12',

						'[&>div]:py-8 [&>div]:p-12',
						'[&>div]:bg-primary-5 [&>div]:rounded-sm',
						'[&>div]:z-20',
						'[&>div]:relative',
						'[&>div]:flex',
						'[&>div]:items-center',
						'[&>div]:gap-8',

						'[&>div>div]:sr-only [&>div>div]:md:not-sr-only',

						'[counter-reset:_multistep]',
						'[&>div]:before:[counter-increment:_multistep]',
						'[&>div]:before:content-[counter(multistep)]',

						'[&>div]:before:flex',
						'[&>div]:before:items-center',
						'[&>div]:before:justify-center',
						'[&>div]:before:text-xs',
						'[&>div]:before:size-24',
						'[&>div]:before:border',
						'[&>div]:before:rounded-full',
						'[&>div]:before:border-neutral-50',

						'[&>.es-form-is-active]:text-primary-50',
						'[&>.es-form-is-active]:before:border-primary-50',

						'[&>.es-form-is-filled]:text-primary-50',
						'[&>.es-form-is-filled]:font-bold',
						'[&>.es-form-is-filled]:before:text-white',
						'[&>.es-form-is-filled]:before:border-primary-50',
						'[&>.es-form-is-filled]:before:bg-primary-50',
						'[&>.es-form-is-filled]:before:content-["✓"]',
					],
				],
			],
		];
	}
}
