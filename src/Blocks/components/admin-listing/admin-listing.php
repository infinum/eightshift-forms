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
$adminListingData = Helpers::checkAttr('adminListingData', $attributes, $manifest);
$adminListingTopItems = Helpers::checkAttr('adminListingTopItems', $attributes, $manifest);
$adminListingNoItems = Helpers::checkAttr('adminListingNoItems', $attributes, $manifest);

$additionalAttributes = [
	UtilsHelper::getStateAttribute('bulkItems') => wp_json_encode([])
];

?>

<div class="esf-main-container es:css-reset esf:flex esf:flex-col esf:gap-12 esf:-ml-20 esf:p-40 es:font-sans">
	<?php
	if ($adminListingPageTitle) {
		echo Helpers::render('intro', [
			'introTitle' => $adminListingPageTitle,
			'introSubtitle' => $adminListingPageSubTitle,
			'introTitleType' => 'big',
		]);
	}
	?>

	<div class="esf:flex esf:flex-row esf:gap-30 <?php echo esc_attr(UtilsHelper::getStateSelectorAdmin('listingBulkItems')); ?>" <?php echo wp_kses_post(Helpers::getAttrsOutput($additionalAttributes)); ?>>
		<div class="esf:bg-white esf:rounded-xl esf:flex-1 esf:border esf:border-mauve-200">
			<div class="esf:flex esf:items-center esf:justify-between esf:gap-8 esf:px-16 esf:py-20 esf:border-b esf:border-mauve-200">
				<div class="esf:flex esf:flex-row esf:gap-8 esf:items-center">
					<?php
					if (isset($adminListingTopItems['left'])) {
						echo wp_kses_post(Helpers::ensureString($adminListingTopItems['left']));
					}
					?>
				</div>
				<div class="esf:flex esf:flex-row esf:gap-8 esf:items-center">
					<?php
					if (isset($adminListingTopItems['right'])) {
						echo wp_kses_post(Helpers::ensureString($adminListingTopItems['right']));
					}
					?>
				</div>
			</div>
			<?php
			if ($adminListingShowNoItems) { ?>
				<div class="esf:px-16 esf:py-60">
					<?php echo wp_kses_post(Helpers::ensureString($adminListingNoItems)); ?>
				</div>
			<?php } else { ?>
				<?php
				echo wp_kses_post(Helpers::ensureString($adminListingItems));
				echo Helpers::render('pagination', [
					'paginationTotalPages' => $adminListingData['totalPages'] ?? 1,
					'paginationCurrentPage' => $adminListingData['currentPage'] ?? 1,
				], 'components', true);
				?>
			<?php } ?>
		</div>

		<div class="esf:max-w-sm esf:flex esf:flex-col esf:gap-20">
			<div class="esf:bg-white esf:rounded-xl esf:p-16 esf:border esf:border-mauve-200">
				<?php
				echo Helpers::render('intro', [
					'introTitle' => __('Need help?', 'eightshift-forms'),
					'introSubtitle' => __('Explore the in-depth documentation available for Eightshift Forms on the official website and gain the confidence you need to create powerful forms with ease!', 'eightshift-forms'),
					'introIcon' => 'docsFormList',
					'introType' => 'highlighted',
					'introActions' => Helpers::render('button', [
						'buttonLabel' => __('Visit Documentation', 'eightshift-forms'),
						'buttonVariant' => 'primaryOutline',
						'buttonUrl' => 'https://eightshift.com/forms/welcome/',
						'buttonNewTab' => true,
					]),
				]);
				?>
			</div>

			<div class="esf:bg-white esf:rounded-xl esf:p-16 esf:border esf:border-gray-200">
				<?php
				echo Helpers::render('intro', [
					'introTitle' => __('Search filters', 'eightshift-forms'),
					'introSubtitle' => __('
					You can use the following filters to search for specific items:
					<ul>
						<li><code>ID:123</code> - Search by ID</li>
						<li><code>STATUS:draft</code> - Search by status</li>
						<li><code>Hubspot</code> - Search by integration</li>
					</ul>
					', 'eightshift-forms'),
				]);
				?>
			</div>
		</div>
	</div>
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
