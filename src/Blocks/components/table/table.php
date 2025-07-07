<?php

/**
 * Template for the Table Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$tableContent = Helpers::checkAttr('tableContent', $attributes, $manifest);
$tableHead = Helpers::checkAttr('tableHead', $attributes, $manifest);

$tableWrapClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($selectorClass, $selectorClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
]);

$tableClass = Helpers::selector($componentClass, $componentClass, 'table');

if (!$tableContent) {
	return;
}

?>

<div class="<?php echo esc_attr($tableWrapClass); ?>">
	<table>
		<?php if ($tableHead) { ?>
			<thead>
				<tr>
					<?php foreach ($tableHead as $head) { ?>
						<th>
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
						<td>
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
