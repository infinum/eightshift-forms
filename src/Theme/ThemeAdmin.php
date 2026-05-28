<?php

/**
 * Theme admin class.
 *
 * @package EightshiftForms\Theme
 */

declare(strict_types=1);

namespace EightshiftForms\Theme;

use EightshiftForms\Helpers\HooksHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Theme admin class.
 */
class ThemeAdmin extends AbstractTheme implements ServiceInterface
{
	/**
	 * Admin selectors cache.
	 *
	 * @var array<string, mixed>|null
	 */
	private ?array $selectorsCache = null;

	/**
	 * Register the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(HooksHelpers::getFilterName(['blocks', 'tailwindSelectorsAdmin']), [$this, 'getSelectors']);
	}

	/**
	 * Get the tailwind selectors for admin.
	 *
	 * @return array<string, mixed>
	 */
	public function getSelectors(): array
	{
		if ($this->selectorsCache !== null) {
			return $this->selectorsCache;
		}

		$this->selectorsCache = \array_merge_recursive(
			$this->getTheme(),
			[
				'form' => [
					'base' => [
						'esf:text-xs',
					],
					'parts' => [
						'fields' => [
							'esf:[&>*]:col-span-12',
						],
						'picker' => [
							'esf:text-xs',
						],
					],
				],
				'input' => [
					'base' => [
						'esf:text-xs',
					],
				],
				'date' => [
					'base' => [
						'esf:text-xs',
					]
				],
				'textarea' => [
					'base' => [
						'esf:text-xs',
					]
				],
				'phone' => [
					'base' => [
						'esf:text-xs',
					],
					'parts' => [
						'select' => [
							'esf:text-xs',
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
							'esf:text-xs',
						],
						'after-content' => [
							'esf:group-[&.esf-input-with-suffix]/field:px-12',
							'esf:group-[&.esf-input-with-suffix]/field:flex',
							'esf:group-[&.esf-input-with-suffix]/field:items-center',
							'esf:group-[&.esf-input-with-suffix]/field:justify-center',
							'esf:group-[&.esf-input-with-suffix]/field:border',
							'esf:group-[&.esf-input-with-suffix]/field:border-gray-500',
							'esf:group-[&.esf-input-with-suffix]/field:border-s-0',
							'esf:group-[&.esf-input-with-suffix]/field:h-46',
							'esf:group-[&.esf-input-with-suffix]/field:rounded-e-xl',
							'esf:group-[&.esf-input-with-suffix]/field:-ml-5',
							'esf:group-[&.esf-input-with-suffix]/field:bg-gray-100',
						],
						'help' => self::THEME_SELECTORS['help-extended'],
					],
				],
				'checkbox' => [
					'base' => [
						'esf:grid esf:grid-cols-[1fr_auto] esf:grid-rows-1 esf:has-[.es-checkbox-toggle__help]:grid-rows-[auto_auto] esf:not-has-[.es-checkbox\_\_label-inner:empty]:gap-x-16 esf:has-[.es-checkbox-toggle__help]:gap-y-2 esf:not-has-[.es-checkbox-toggle__help]:items-center',
					],

					'parts' => [
						'input' => [
							'esf:relative',
							'esf:appearance-none',
							'esf:is-toggle:w-40 esf:is-toggle:h-24 esf:is-check:size-24',
							'esf:is-toggle:rounded-full esf:is-check:rounded-md',
							'esf:bg-white esf:checked:bg-mist-500',
							'esf:border esf:border-gray-500 esf:checked:border-mist-500',
							'esf:shadow-none',
							'esf:transition esf:after:transition',
							'esf:is-toggle:after:content-[""] esf:is-check:after:content-["✓"]',
							'esf:after:absolute esf:is-toggle:after:top-3 esf:is-toggle:after:left-3 esf:is-check:after:left-2 esf:is-check:after:top-2 esf:is-check:not-checked:after:opacity-0',
							'esf:is-check:after:text-white',
							'esf:is-toggle:after:size-16 esf:is-check:after:size-18 esf:is-toggle:after:scale-90 esf:is-toggle:checked:after:scale-110',
							'esf:after:flex esf:after:items-center esf:after:justify-center esf:after:text-base',
							'esf:is-toggle:checked:after:translate-x-16',
							'esf:is-toggle:after:bg-gray-500 esf:is-toggle:checked:after:bg-white esf:is-toggle:after:rounded-full',
							'esf:before:hidden!',
							'esf:col-2 esf:row-start-1 esf:row-end-3 esf:items-center',
							'esf:mx-0! esf:my-auto!',
							'esf:cursor-pointer',
						],
						'label' => [
							'esf:text-xs',
							'esf:col-1 esf:grid-row-1',
						],
						'help' => [
							'esf:col-1 esf:grid-row-2',
							...self::THEME_SELECTORS['help-extended'],
						],
						'content' => [
							'esf:contents',
						],
					],
				],
				'radio' => [
					'parts' => [
						'label' => [
							'esf:text-xs',
						],
						'help' => [
							...self::THEME_SELECTORS['help-extended'],
						],
					],
				],
				'select' => [
					'base' => [
						'esf:text-xs',
					],
					'parts' => [
						'select-choices-inner' => [
							'esf:text-xs',
						],
					],
				],
				'country' => [
					'base' => [
						'esf:text-xs',
					],
					'parts' => [
						'select-choices-inner' => [
							'esf:text-xs',
						],
					],
				],
				'submit' => [
					'base' => [
						'esf-button-primary',
						'esf:text-xs',
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

		return $this->selectorsCache;
	}
}
