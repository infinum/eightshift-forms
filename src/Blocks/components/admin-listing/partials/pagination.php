<?php

/**
 * Pagination component.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

$data = $attributes['data'] ?? [];

$totalPages = $data['totalPages'] ?? 1;
$currentPage = $data['currentPage'] ?? 1;

if ($totalPages <= 1) {
	return;
}

$output = paginate_links([
	'base' => '%_%',
	'format' => '?paged=%#%',
	'current' => max(1, $currentPage),
	'total' => $totalPages,
	'mid_size' => 1,
	'prev_text' => __('«'),
	'next_text' => __('»'),
	'type' => 'list',
]);
?>

<div class="es-pagination">
	<?php echo wp_kses_post($output); ?>
</div>
