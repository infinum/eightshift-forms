<?php

/**
 * Template for admin listing page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);
$manifestSection = Helpers::getComponent('admin-settings-section');

echo Helpers::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminListingPageTitle = Helpers::checkAttr('adminListingPageTitle', $attributes, $manifest);
$adminListingPageSubTitle = Helpers::checkAttr('adminListingPageSubTitle', $attributes, $manifest);
$adminListingShowNoItems = Helpers::checkAttr('adminListingShowNoItems', $attributes, $manifest);
$adminListingItems = Helpers::checkAttr('adminListingItems', $attributes, $manifest);
$adminListingTopItems = Helpers::checkAttr('adminListingTopItems', $attributes, $manifest);
$adminListingNoItems = Helpers::checkAttr('adminListingNoItems', $attributes, $manifest);

$help = Helpers::render('container', [
	'containerContent' => Helpers::render('highlighted-content', [
		'highlightedContentTitle' => __('Need help?', 'eightshift-forms'),
		'highlightedContentSubtitle' => __('Explore the in-depth documentation available for Eightshift Forms on the official website and gain the confidence you need to create powerful forms with ease!', 'eightshift-forms') . '<br /><br /><a class="es-submit es-submit--outline" target="__blank" rel="noopener noreferrer" href="https://eightshift.com/forms/welcome/">' . __('Visit Documentation', 'eightshift-forms') . '</a>',
		'highlightedContentIcon' => 'docsFormList',
	]),
]);

?>

<div class="<?php echo esc_attr($componentClass); ?>">
	<?php
	if ($adminListingPageTitle) {
		echo Helpers::render('intro', [
			'introTitle' => $adminListingPageTitle,
			// Translators: %s is the number of forms.
			'introSubtitle' => $adminListingPageSubTitle,
			'introIsHeading' => true,
		]);
	}
	?>

	<?php
	echo Helpers::render('layout', [
		'layoutType' => 'layout-v-stack-card-fullwidth',
		'layoutContent' => Helpers::ensureString([
			Helpers::render('container', [
				'containerContent' => Helpers::ensureString([
					Helpers::render('container', [
						'containerUse' => $adminListingTopItems,
						'containerClass' => "{$componentClass}__top-bar",
						'containerContent' => Helpers::ensureString([
							Helpers::render('container', [
								'containerClass' => "{$componentClass}__top-bar-left",
								'containerUse' => !empty($adminListingTopItems['left']),
								'containerContent' => $adminListingTopItems['left'] ?? '',
							]),
							Helpers::render('container', [
								'containerClass' => "{$componentClass}__top-bar-right",
								'containerUse' => !empty($adminListingTopItems['right']),
								'containerContent' => $adminListingTopItems['right'] ?? '',
							]),
						]),
					]),
					$adminListingShowNoItems ?
					Helpers::ensureString($adminListingNoItems) :
					Helpers::ensureString($adminListingItems),
				]),
			]),
			$help,
		]),
		'additionalClass' => UtilsHelper::getStateSelectorAdmin('listingBulkItems'),
		'additionalAttributes' => [
			UtilsHelper::getStateAttribute('bulkItems') => wp_json_encode([]),
		],
	]);
	?>
</div>

<?php

// This is fake form to be able to init state for global msg.

$formClasses = Helpers::classnames([
	UtilsHelper::getStateSelector('form'),
	Helpers::selector($componentClass, $componentClass, 'form'),
]);
?>
<form class="<?php echo esc_attr($formClasses); ?>" <?php echo esc_attr(UtilsHelper::getStateAttribute('formId')); ?>="0" novalidate>
	<?php echo Helpers::render('global-msg', Helpers::props('globalMsg', $attributes)); ?>
	<?php echo Helpers::render('loader', Helpers::props('loader', $attributes)); ?>
</form>
