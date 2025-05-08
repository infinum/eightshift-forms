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
							// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
							echo $head;
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
						<th>
							<?php
							// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
							echo $row[$headKey] ?? '';
							?>
						</th>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
