<?php

/**
 * Template for admin listing page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\AdminMenus\FormAdminMenu;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getComponent('admin-settings-section');
$manifestUtils = Components::getComponent('utils');

echo Components::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

$componentClass = $manifest['componentClass'] ?? '';
$componentJsItemClass = $manifest['componentJsItemClass'] ?? '';
$componentJsFilterClass = $manifest['componentJsFilterClass'] ?? '';
$componentJsSyncClass = $manifest['componentJsSyncClass'] ?? '';
$componentJsLocationsClass = $manifest['componentJsLocationsClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminListingPageTitle = Components::checkAttr('adminListingPageTitle', $attributes, $manifest);
$adminListingSubTitle = Components::checkAttr('adminListingSubTitle', $attributes, $manifest);
$adminListingNewFormLink = Components::checkAttr('adminListingNewFormLink', $attributes, $manifest);
$adminListingTrashLink = Components::checkAttr('adminListingTrashLink', $attributes, $manifest);
$adminListingForms = Components::checkAttr('adminListingForms', $attributes, $manifest);
$adminListingType = Components::checkAttr('adminListingType', $attributes, $manifest);
$adminListingListingLink = Components::checkAttr('adminListingListingLink', $attributes, $manifest);
$adminListingIntegrations = Components::checkAttr('adminListingIntegrations', $attributes, $manifest);
$adminListingIsDeveloperMode = Components::checkAttr('adminListingIsDeveloperMode', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($sectionClass, $sectionClass),
]);

$formCardsToDisplay = [];

if ($adminListingForms) {
	foreach ($adminListingForms as $form) {
		$id = $form['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$editLink = $form['editLink'] ?? '';
		$postType = $form['postType'] ?? '';
		$viewLink = $form['viewLink'] ?? '';
		$trashLink = $form['trashLink'] ?? '';
		$trashRestoreLink = $form['trashRestoreLink'] ?? '';
		$settingsLink = $form['settingsLink'] ?? '';
		$settingsLocationLink = $form['settingsLocationLink'] ?? '';
		$formTitle = $form['title'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$status = $form['status'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$useSync = $form['useSync'] ?? false;
		$activeIntegration = $form['activeIntegration'] ?? [];

		$activeIntegrationIsActive = $activeIntegration['isActive'] ?? false;
		$activeIntegrationIsValid = $activeIntegration['isValid'] ?? false;
		$activeIntegrationIsApiValid = $activeIntegration['isApiValid'] ?? false;

		$isFormValid = $activeIntegrationIsActive && $activeIntegrationIsValid && $activeIntegrationIsApiValid;

		$slug = $editLink;

		if (!$editLink) {
			$slug = '#';
		}

		$subtitle = null;
		$errorText = '';

		if ($status !== 'publish' && $status !== 'trash') {
			$subtitle = ucfirst($status);
		}

		if ($subtitle && $postType) {
			$subtitle .= ' ' . ucfirst($postType);
		} else {
			$subtitle .= ucfirst($postType);
		}

		if (!$isFormValid && $adminListingType !== 'trash') {
			$errorText = '';

			if (!$activeIntegrationIsActive) {
				$errorText .= esc_html__('Integration not enabled.', 'eightshift-forms') . ' ';
			}

			if (!$activeIntegrationIsValid) {
				$errorText .= esc_html__('Form configuration not valid.', 'eightshift-forms') . ' ';
			}

			if (!$activeIntegrationIsApiValid) {
				$errorText .= esc_html__('Missing form fields.', 'eightshift-forms');
			}

			if (!empty($errorText)) {
				$errorText = implode(', ', array_map(fn ($item) => lcfirst($item), explode('. ', $errorText)));
				$errorText = '<span class="error-text">' . ucfirst($errorText) . '</span>';

				if (!empty($subtitle)) {
					$errorText = $errorText . ' &mdash; ';
				}
			}
		}

		if (!$formTitle) {
			// Translators: %s is the form ID.
			$formTitle = sprintf(__('Form %s', 'eightshift-forms'), $id);
		}

		$cardIcon = $activeIntegration['icon'] ?? $manifestUtils['icons']['listingGeneric'];

		if ($postType === 'post') {
			$cardIcon = Helper::getProjectIcons('post');
		} elseif ($postType === 'page') {
			$cardIcon = Helper::getProjectIcons('page');
		}

		$formCardsToDisplay[] = Components::render('card', [
			'additionalClass' => Components::classnames([
				'js-es-admin-listing-item',
				!$isFormValid && $adminListingType !== 'trash' ? 'es-form-has-error' : '',
			]),
			'additionalAttributes' => [
				'data-integration-type' => esc_attr($activeIntegration['value'] ?? FormAdminMenu::ADMIN_MENU_FILTER_NOT_CONFIGURED),
				'data-integration-is-active' => wp_json_encode($activeIntegrationIsActive),
				'data-integration-is-valid' => wp_json_encode($activeIntegrationIsValid),
				'data-integration-is-api-valid' => wp_json_encode($activeIntegrationIsApiValid),
			],
			'cardTitle' => '<a href="' . $editLink . '">' . $formTitle . ($adminListingIsDeveloperMode ? " ({$id})" : '') . '</a>',
			'cardSubTitle' => $errorText . $subtitle,
			'cardShowButtonsOnHover' => true,
			'cardIcon' => $cardIcon,
			'cardTrailingButtons' => [
				...($adminListingType === 'trash' ? [
					[
						'label' => __('Delete permanently', 'eightshift-forms'),
						'url' => $trashLink,
						'internal' => true,
					],
					[
						'label' => __('Restore', 'eightshift-forms'),
						'url' => $trashRestoreLink,
						'internal' => true,
					],
				] : [
					[
						'label' => __('Delete', 'eightshift-forms'),
						'url' => $trashLink,
						'internal' => true,
					],
				]),
				...($isFormValid ? [
					($adminListingType === 'trash' || $adminListingType === 'locations' || !Helper::canIntegrationUseSync($activeIntegration['value'])) ? [] : [
						'label' => __('Sync', 'eightshift-forms'),
						'internal' => true,
						'isButton' => true,
						'additionalAttrs' => [AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['syncId'] => $id],
						'additionalClass' => $componentJsSyncClass,
					],
					[
						'label' => __('Locations', 'eightshift-forms'),
						'url' => $settingsLocationLink,
						'internal' => true,
						'isButton' => true,
						'additionalAttrs' => [AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['locationsId'] => $id],
						'additionalClass' => $componentJsLocationsClass,
					],
					[
						'label' => __('Settings', 'eightshift-forms'),
						'url' => $settingsLink,
						'internal' => true,
					],
					[
						'label' => __('Edit', 'eightshift-forms'),
						'url' => $editLink,
						'internal' => true,
					],
					[
						'label' => __('View', 'eightshift-forms'),
						'url' => $viewLink,
						'internal' => true,
					],
				] : [
					[
						'label' => __('Settings', 'eightshift-forms'),
						'url' => $settingsLink,
						'internal' => true,
					]
				]),
			],
		]);
	}
}


if (!empty($adminListingPageTitle)) {
	echo Components::render('intro', [
		'introTitle' => $adminListingPageTitle,
		'introSubtitle' => $adminListingSubTitle,
		'introIsHeading' => true,
	]);
}

$topBar = [];

if ($adminListingPageTitle || $adminListingSubTitle) {
	$topBar = [
		Components::render('layout', [
			'layoutType' => 'first-left-others-right',
			'layoutContent' => Components::ensureString([
				Components::render('container', [
					'containerUse' => $adminListingType === 'trash' && $adminListingListingLink,
					'containerClass' => 'es-submit es-submit--ghost',
					'containerTag' => 'a',
					'additionalAttributes' => [
						'href' => $adminListingListingLink,
					],
					'containerContent' => Components::ensureString([
						Helper::getProjectIcons('arrowLeft'),
						esc_html__('Back', 'eightshift-forms'),
					]),
				]),
				Components::render('container', [
					'containerUse' => $adminListingIntegrations,
					'containerClass' => "{$sectionClass}__heading-filter {$componentJsFilterClass}",
					'containerContent' => wp_kses_post($adminListingIntegrations),
				]),
				Components::render('container', [
					'containerUse' => $adminListingType !== 'trash' && $adminListingTrashLink,
					'containerClass' => 'es-submit es-submit--outline',
					'containerTag' => 'a',
					'additionalAttributes' => [
						'href' => $adminListingTrashLink,
					],
					'containerContent' => Components::ensureString([
						esc_html__('Deleted', 'eightshift-forms'),
					]),
				]),
				Components::render('container', [
					'containerUse' => $adminListingType !== 'trash' && $adminListingTrashLink,
					'containerClass' => "es-submit es-submit--outline {$componentJsSyncClass}",
					'containerTag' => 'button',
					'additionalAttributes' => [
						AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['syncId'] => 'all',
					],
					'containerContent' => esc_html__('Sync all', 'eightshift-forms'),
				]),
				Components::render('container', [
					'containerUse' => $adminListingType !== 'trash' && $adminListingNewFormLink,
					'containerClass' => 'es-submit es-submit--fit-icon',
					'containerTag' => 'a',
					'additionalAttributes' => [
						'href' => $adminListingNewFormLink,
					],
					'containerContent' => Components::ensureString([
						Helper::getProjectIcons('addHighContrast'),
						esc_html__('Create', 'eightshift-forms'),
					]),
				]),
			]),
		]),
		Components::render('divider', [
			'dividerExtraVSpacing' => true,
		]),
	];
}

echo Components::render('layout', [
	'layoutType' => 'layout-v-stack-card-fullwidth',
	'layoutContent' => Components::ensureString([
		...$topBar,
		empty($formCardsToDisplay)
			? Components::render('highlighted-content', [
				'highlightedContentTitle' => $adminListingType === 'trash' ? __('Trash is empty', 'eightshift-forms') : __('No forms', 'eightshift-forms'),
				'highlightedContentSubtitle' => $adminListingType === 'trash' ? '' : '<br /><a class="es-submit es-submit--outline" href="' . $adminListingNewFormLink . '">Add form<a/>',
				'highlightedContentIcon' => $adminListingType === 'trash' ? 'emptyStateTrash' : 'emptyStateFormList',
			])
			: Components::ensureString($formCardsToDisplay),
	]),
]);

// This is fake form to be able to init state for global msg.

$formClasses = Components::classnames([
	Components::getComponent('form')['componentFormJsClass'],
	Components::selector($componentClass, $componentClass, 'form'),
]);
?>
<form class="<?php echo esc_attr($formClasses); ?>">
	<?php echo Components::render('global-msg', Components::props('globalMsg', $attributes)); ?>
	<?php echo Components::render('loader', Components::props('loader', $attributes)); ?>
</form>
