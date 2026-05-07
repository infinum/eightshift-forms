<?php

/**
 * Pagination component.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$paginationTotalPages = Helpers::checkAttr('paginationTotalPages', $attributes, $manifest);
$paginationCurrentPage = Helpers::checkAttr('paginationCurrentPage', $attributes, $manifest);

if ($paginationTotalPages <= 1) {
	return;
}

$output = paginate_links([
	'base' => '%_%',
	'format' => '?paged=%#%',
	'current' => max(1, $paginationCurrentPage),
	'total' => $paginationTotalPages,
	'mid_size' => 1,
	'prev_text' => __('«'),
	'next_text' => __('»'),
	'type' => 'list',
]);
?>

<div class="esf-pagination esf-focus-ring">
	<?php echo wp_kses_post($output); ?>
</div>
