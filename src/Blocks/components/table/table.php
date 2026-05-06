<?php

/**
 * Template for the Table Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$tableContent = Helpers::checkAttr('tableContent', $attributes, $manifest);
$tableHead = Helpers::checkAttr('tableHead', $attributes, $manifest);
$additionalClass = $attributes['additionalClass'] ?? '';

if (!$tableContent) {
	return;
}

$classes = Helpers::clsx([
	'esf:relative',
	'esf:overflow-auto',
	$additionalClass,
]);
?>

<div class="<?php echo esc_attr($classes); ?>">
	<table class="esf:border-spacing-0 esf:w-full">
		<?php if ($tableHead) { ?>
			<thead class="esf:bg-secondary-100">
				<tr>
					<?php foreach ($tableHead as $head) { ?>
						<th class="esf:border-r esf:border-b esf:border-border esf:p-8 esf:text-left esf:last:border-r-0">
							<?php
							echo $head; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
							?>
						</th>
					<?php } ?>
				</tr>
			</thead>
		<?php } ?>
		<tbody>
			<?php foreach ($tableContent as $row) { ?>
				<tr>
					<?php foreach ($tableHead as $headKey => $headValue) { ?>
						<td class="esf:border-r esf:border-b esf:border-border esf:p-8 esf:text-left esf:last:border-r-0">
							<?php
							echo $row[$headKey] ?? ''; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
							?>
						</td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
