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
						'esf:text-sm',
					],
					'parts' => [
						'fields' => [
							'esf:[&>*]:col-span-12',
						],
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
						'help' => self::THEME_SELECTORS['help-extended'],
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
							...self::THEME_SELECTORS['help-extended'],
						],
					],
				],
				'radio' => [
					'parts' => [
						'label' => [
							'esf:text-sm',
						],
						'help' => [
							...self::THEME_SELECTORS['help-extended'],
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

		return $this->selectorsCache;
	}
}
