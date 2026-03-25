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

<div class="esf:border-t esf:border-secondary-200 esf:pt-24 esf:-mx-24 esf:[&_ul]:flex esf:[&_ul]:items-center esf:[&_ul]:justify-center esf:[&_ul]:gap-8 esf:[&_li]:list-none esf:[&_a,&_span.current]:no-underline esf:[&_a,&_.current]:px-16 esf:[&_a,&_.current]:py-11 esf:[&_a,&_.current]:rounded-lg esf:[&_a,&_.current]:bg-transparent esf:[&_a,&_.current]:text-secondary-500 esf:[&_a,&_.current]:border esf:[&_a,&_.current]:border-secondary-300 esf:[&_a,&_.current]:transition-colors esf:[&_a:hover]:bg-accent-50 esf:[&_.current]:bg-accent-600 esf:[&_.current]:border-accent-600 esf:[&_.current]:text-white">
	<?php echo wp_kses_post($output); ?>
</div>
