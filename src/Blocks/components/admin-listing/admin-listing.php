<?php

/**
 * Template for admin listing page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestSection = Components::getComponent('admin-settings-section');

echo Components::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminListingPageTitle = Components::checkAttr('adminListingPageTitle', $attributes, $manifest);
$adminListingPageSubTitle = Components::checkAttr('adminListingPageSubTitle', $attributes, $manifest);
$adminListingShowNoItems = Components::checkAttr('adminListingShowNoItems', $attributes, $manifest);
$adminListingItems = Components::checkAttr('adminListingItems', $attributes, $manifest);
$adminListingTopItems = Components::checkAttr('adminListingTopItems', $attributes, $manifest);
$adminListingNoItems = Components::checkAttr('adminListingNoItems', $attributes, $manifest);

$help = Components::render('container', [
	'containerContent' => Components::render('highlighted-content', [
		'highlightedContentTitle' => __('Need help?', 'eightshift-forms'),
		'highlightedContentSubtitle' => __('Explore the in-depth documentation available for Eightshift Forms on the official website and gain the confidence you need to create powerful forms with ease!', 'eightshift-forms') . '<br /><br /><a class="es-submit es-submit--outline" target="__blank" rel="noopener noreferrer" href="https://eightshift.com/forms/welcome/">' . __('Visit Documentation', 'eightshift-forms') . '</a>',
		'highlightedContentIcon' => 'docsFormList',
	]),
]);

?>

<div class="<?php echo esc_attr($componentClass); ?>">
	<?php
	if ($adminListingPageTitle) {
		echo Components::render('intro', [
			'introTitle' => $adminListingPageTitle,
			// Translators: %s is the number of forms.
			'introSubtitle' => $adminListingPageSubTitle,
			'introIsHeading' => true,
		]);
	}
	?>

	<?php
	echo Components::render('layout', [
		'layoutType' => 'layout-v-stack-card-fullwidth',
		'layoutContent' => Components::ensureString([
			Components::render('container', [
				'containerContent' => Components::ensureString([
					Components::render('container', [
						'containerUse' => $adminListingTopItems,
						'containerClass' => "{$componentClass}__top-bar",
						'containerContent' => Components::ensureString([
							Components::render('container', [
								'containerClass' => "{$componentClass}__top-bar-left",
								'containerUse' => !empty($adminListingTopItems['left']),
								'containerContent' => $adminListingTopItems['left'] ?? '',
							]),
							Components::render('container', [
								'containerClass' => "{$componentClass}__top-bar-right",
								'containerUse' => !empty($adminListingTopItems['right']),
								'containerContent' => $adminListingTopItems['right'] ?? '',
							]),
						]),
					]),
					$adminListingShowNoItems ?
					Components::ensureString($adminListingNoItems) :
					Components::ensureString($adminListingItems),
				]),
			]),
			$help,
		]),
		'additionalClass' => Helper::getStateSelectorAdmin('listingBulkItems'),
		'additionalAttributes' => [
			Helper::getStateAttribute('bulkItems') => wp_json_encode([]),
		],
	]);
	?>
</div>

<?php

// This is fake form to be able to init state for global msg.

$formClasses = Components::classnames([
	Helper::getStateSelector('form'),
	Components::selector($componentClass, $componentClass, 'form'),
]);
?>
<form class="<?php echo esc_attr($formClasses); ?>" <?php echo Helper::getStateAttribute('formId'); ?>="0">
	<?php echo Components::render('global-msg', Components::props('globalMsg', $attributes)); ?>
	<?php echo Components::render('loader', Components::props('loader', $attributes)); ?>
</form>
