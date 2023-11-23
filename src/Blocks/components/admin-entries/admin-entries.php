<?php

/**
 * Template for admin entries page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestCustomFormAttrs = Components::getSettings()['customFormAttrs'];

echo Components::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

$componentJsItemClass = $manifest['componentJsItemClass'] ?? '';
$componentJsBulkClass = $manifest['componentJsBulkClass'] ?? '';

$adminEntriesForms = Components::checkAttr('adminEntriesForms', $attributes, $manifest);

$hasForms = !empty($adminEntriesForms);

$formCardsToDisplay = [];
$topBar = [];

if ($hasForms) {
	foreach ($adminEntriesForms as $item) {
		$id = $item['id'] ?? ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$formId = $item['formId'] ?? '';
		$entryValue = $item['entryValue'] ?? [];
		$viewLink = '';

		$outputContent = '<ul>';
		foreach ($entryValue as $key => $value) {
			if (gettype($value) === 'array') {
				$value = implode(',  ', $value);
			}

			$value = str_replace(AbstractBaseRoute::DELIMITER, ', ', $value);

			$outputContent .= '<li>' . $key . ': ' . $value . '</li>';
		}
		$outputContent .= '</ul>';

		$formCardsToDisplay[] = Components::render('card', [
			'additionalClass' => Components::classnames([
				$componentJsItemClass,
			]),
			'additionalAttributes' => [
				$manifestCustomFormAttrs['bulkId'] => $id,
			],
			'cardTitle' => '<a href="' . $viewLink . '">' . $id . '</a>',
			'cardContentDetails' => $outputContent,
			'cardShowButtonsOnHover' => true,
			// 'cardIcon' => $cardIcon,
			'cardId' => $id,
			// 'cardBulk' => !$isLocationsPage,
			'cardTrailingButtons' => [
				[
					'label' => __('View', 'eightshift-forms'),
					'url' => $viewLink,
					'internal' => true,
				],
			],
		]);
	}
}

echo Components::render('layout', [
	'layoutType' => 'layout-v-stack-card-fullwidth',
	'layoutContent' => Components::ensureString([
		Components::render('container', [
			'containerContent' => Components::ensureString([
				Components::ensureString($formCardsToDisplay),
			]),
		]),
	]),
	'additionalClass' => "{$componentJsBulkClass}-items",
	'additionalAttributes' => [
		$manifestCustomFormAttrs['bulkItems'] => wp_json_encode([]),
	],
]);
