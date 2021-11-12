<?php

/**
 * Template for the query Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\BlockQuery;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Hooks\Filters;

$manifest = Components::getManifest(__DIR__);

$queryData = Components::checkAttr('queryData', $attributes, $manifest);

// Output form.
echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	BlockQuery::FILTER_BLOCK_QUERY_COMPONENT_NAME,
	$attributes,
	apply_filters(Filters::FILTER_BLOCK_QUERY_OPTIONS_DATA_NAME, $queryData) ?? []
);
