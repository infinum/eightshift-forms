<?php

/**
 * Template for admin listing page.
 *
 * @package EightshiftForms\Blocks.
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifestSection = Helpers::getComponent('admin-settings-section');

echo Helpers::outputCssVariablesGlobal(); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$sectionClass = $manifestSection['componentClass'] ?? '';

$adminListingPageTitle = Helpers::checkAttr('adminListingPageTitle', $attributes, $manifest);
$adminListingPageSubTitle = Helpers::checkAttr('adminListingPageSubTitle', $attributes, $manifest);
$adminListingShowNoItems = Helpers::checkAttr('adminListingShowNoItems', $attributes, $manifest);
$adminListingItems = Helpers::checkAttr('adminListingItems', $attributes, $manifest);
$adminListingPagination = Helpers::checkAttr('adminListingPagination', $attributes, $manifest);
$adminListingTopItems = Helpers::checkAttr('adminListingTopItems', $attributes, $manifest);
$adminListingNoItems = Helpers::checkAttr('adminListingNoItems', $attributes, $manifest);

$additionalAttributes = [
	UtilsHelper::getStateAttribute('bulkItems') => wp_json_encode([])
];

?>

<div class="esf:flex esf:flex-col esf:gap-15 esf:-ml-20 esf:p-40">
	<?php
	if ($adminListingPageTitle) {
		echo Helpers::render('intro', [
			'introTitle' => $adminListingPageTitle,
			'introSubtitle' => $adminListingPageSubTitle,
			'introIsHeading' => true,
		]);
	}
	?>

	<div class="esf:flex esf:flex-row esf:gap-30 <?php echo UtilsHelper::getStateSelectorAdmin('listingBulkItems'); ?>" <?php echo Helpers::getAttrsOutput($additionalAttributes); ?>>
		<div class="esf:bg-white esf:rounded-md esf:flex-1">
			<div class="esf:flex esf:items-center esf:justify-between esf:gap-8 esf:px-20 esf:py-20 esf:border-b esf:border-secondary-200">
				<div class="esf:flex esf:flex-row esf:gap-8 esf:items-center">
					<?php
					if (isset($adminListingTopItems['left'])) {
						echo Helpers::ensureString($adminListingTopItems['left']);
					}
					?>
				</div>
				<div class="esf:flex esf:flex-row esf:gap-8 esf:items-center">
					<?php
					if (isset($adminListingTopItems['right'])) {
						echo Helpers::ensureString($adminListingTopItems['right']);
					}
					?>
				</div>
			</div>
			<?php
			if ($adminListingShowNoItems) { ?>
				<div class="esf:px-20 esf:py-60">
					<?php echo Helpers::ensureString($adminListingNoItems); ?>
				</div>
			<?php } else { ?>
				<?php
				echo Helpers::ensureString($adminListingItems);
				echo Helpers::render('pagination', [
					'data' => $adminListingPagination,
				], 'components', false, 'admin-listing/partials');
				?>
			<?php } ?>
		</div>

		<div class="esf:max-w-sm">
			<div class="esf:bg-white esf:rounded-md esf:p-20">
				<?php
				echo Helpers::render('highlighted-content', [
					'highlightedContentTitle' => __('Need help?', 'eightshift-forms'),
					'highlightedContentSubtitle' => __('Explore the in-depth documentation available for Eightshift Forms on the official website and gain the confidence you need to create powerful forms with ease!', 'eightshift-forms') . '<br /><br /><a class="esf-link-primary" target="__blank" rel="noopener noreferrer" href="https://eightshift.com/forms/welcome/">' . __('Visit Documentation', 'eightshift-forms') . '</a>',
					'highlightedContentIcon' => 'docsFormList',
				]);
				?>
			</div>
		</div>
	</div>

	<?php
	// echo Helpers::render('layout', [
	// 	'layoutType' => 'layout-v-stack-card-fullwidth',
	// 	'layoutContent' => Helpers::ensureString([
	// 		Helpers::render('container', [
	// 			'containerContent' => Helpers::ensureString([
	// 				// Helpers::render('container', [
	// 				// 	'containerUse' => $adminListingTopItems,
	// 				// 	'containerClass' => "esf:flex esf:items-center esf:justify-between esf:gap-8 esf:border-b esf:border-secondary-200 esf:-mx-24 esf:px-24 esf:pb-24",
	// 				// 	'containerContent' => Helpers::ensureString([
	// 				// 		Helpers::render('container', [
	// 				// 			'containerClass' => "esf:flex esf:items-center esf:gap-5",
	// 				// 			'containerUse' => !empty($adminListingTopItems['left']),
	// 				// 			'containerContent' => $adminListingTopItems['left'] ?? '',
	// 				// 		]),
	// 				// 		Helpers::render('container', [
	// 				// 			'containerClass' => "esf:flex esf:items-center esf:gap-5",
	// 				// 			'containerUse' => !empty($adminListingTopItems['right']),
	// 				// 			'containerContent' => $adminListingTopItems['right'] ?? '',
	// 				// 		]),
	// 				// 	]),
	// 				// ]),
	// 				// $adminListingShowNoItems ?
	// 				// 	Helpers::ensureString($adminListingNoItems) :
	// 				// 	Helpers::ensureString($adminListingItems),
	// 				// Helpers::render('pagination', [
	// 				// 	'data' => $adminListingPagination,
	// 				// ], 'components', false, 'admin-listing/partials'),
	// 			]),
	// 		]),
	// 		// $help,
	// 	]),
	// 	'additionalClass' => UtilsHelper::getStateSelectorAdmin('listingBulkItems'),
	// 	'additionalAttributes' => [
	// 		UtilsHelper::getStateAttribute('bulkItems') => wp_json_encode([]),
	// 	],
	// ]);
	?>
</div>

<?php

// This is fake form to be able to init state for global msg.

$formClasses = Helpers::clsx([
	UtilsHelper::getStateSelector('form'),
	Helpers::selector($componentClass, $componentClass, 'form'),
]);
?>
<form
	class="<?php echo esc_attr($formClasses); ?>"
	<?php echo esc_attr(UtilsHelper::getStateAttribute('formId')); ?>="0"
	novalidate>
	<?php echo Helpers::render('global-msg', Helpers::props('globalMsg', $attributes)); ?>
	<?php echo Helpers::render('loader', Helpers::props('loader', $attributes)); ?>
</form>
