<?php

/**
 * File that holds class for admin sub menu example.
 *
 * @package EightshiftLibs\AdminMenus
 */

declare(strict_types=1);

namespace EightshiftForms\AdminMenus;

use EightshiftForms\Helpers\Components;
use EightshiftFormsPluginVendor\EightshiftLibs\AdminMenus\AbstractAdminSubMenu;
use EightshiftForms\Settings\FormBuilderInterface;

/**
 * FormOptionAdminSubMenu class.
 */
class FormOptionAdminSubMenu extends AbstractAdminSubMenu
{
	/**
	 * Instance variable of form options data.
	 *
	 * @var FormBuilderInterface
	 */
	protected $formOption;

	/**
	 * Create a new instance.
	 *
	 * @param FormBuilderInterface $formOption Inject documentsData which holds form options data.
	 */
	public function __construct(FormBuilderInterface $formOption)
	{
		$this->formOption = $formOption;
	}

	/**
	 * Capability for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_CAPABILITY = 'mangage-options';

	/**
	 * Menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const ADMIN_MENU_SLUG = 'form-option';

	/**
	 * Parent menu slug for this admin sub menu
	 *
	 * @var string
	 */
	public const PARENT_MENU_SLUG = '';

	/**
	 * Get the title to use for the admin page.
	 *
	 * @return string The text to be displayed in the title tags of the page when the menu is selected.
	 */
	protected function getTitle(): string
	{
		return \esc_html__('Form Option', 'eightshift-forms');
	}

	/**
	 * Get the menu title to use for the admin menu.
	 *
	 * @return string The text to be used for the menu.
	 */
	protected function getMenuTitle(): string
	{
		return \esc_html__('Form Option', 'eightshift-forms');
	}

	/**
	 * Get the capability required for this menu to be displayed.
	 *
	 * @return string The capability required for this menu to be displayed to the user.
	 */
	protected function getCapability(): string
	{
		return self::ADMIN_MENU_CAPABILITY;
	}

	/**
	 * Get the menu slug.
	 *
	 * @return string The slug name to refer to this menu by.
	 *                Should be unique for this menu page and only include lowercase alphanumeric,
	 *                dashes, and underscores characters to be compatible with sanitize_key().
	 */
	protected function getMenuSlug(): string
	{
		return self::ADMIN_MENU_SLUG;
	}

	/**
	 * Get the slug of the parent menu.
	 *
	 * @return string The slug name for the parent menu (or the file name of a standard WordPress admin page.
	 */
	protected function getParentMenu(): string
	{
		return self::PARENT_MENU_SLUG;
	}

	/**
	 * Get the view component that will render correct view.
	 *
	 * @return string View uri.
	 */
	protected function getViewComponent(): string
	{
		return 'settings-form-option';
	}

	/**
	 * Render the current view.
	 *
	 * @param array<string, mixed>  $attributes Array of attributes passed to the view.
	 * @param string $innerBlockContent Not used here.
	 *
	 * @return string Rendered HTML.
	 * @throws \Exception On missing attributes OR missing template.
	 */
	public function render(array $attributes = [], string $innerBlockContent = ''): string
	{
		return Components::render($this->getViewComponent(), $attributes);
	}

	/**
	 * Process the admin menu attributes.
	 *
	 * Here you can get any kind of metadata, query the database, etc..
	 * This data will be passed to the component view to be rendered out in the
	 * processAdminMenu parent method.
	 *
	 * @param array<string, mixed>|string $attr Raw admin menu attributes passed into the
	 *                           admin menu function.
	 *
	 * @return array<string, mixed> Processed admin menu attributes.
	 */
	protected function processAttributes($attr): array
	{
		$slug = FormMainListingAdminMenu::ADMIN_MENU_SLUG;

		return [
			'settingsFormOptionPageTitle' => \esc_html__('From Options', 'eightshift-forms'),
			'settingsFormOptionSubTitle' => \esc_html__('On settings page you can setup email settings, integrations and much more.', 'eightshift-forms'),
			'settingsFormOptionBackLink' => '/wp-admin/admin.php?page=' . FormMainListingAdminMenu::ADMIN_MENU_SLUG,
			'settingsFormOptionForm' => $this->formOption->getFormFields(),
		];
	}
}
