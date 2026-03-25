<?php

/**
 * Template for the Table Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$tableContent = Helpers::checkAttr('tableContent', $attributes, $manifest);
$tableHead = Helpers::checkAttr('tableHead', $attributes, $manifest);

if (!$tableContent) {
	return;
}

?>

<div class="esf:relative esf:overflow-auto esf:[&_table]:w-full esf:[&_table]:border-spacing-0 esf:[&_thead]:bg-secondary-100 esf:[&_th]:p-8 esf:[&_th]:text-left esf:[&_th]:border-r esf:[&_th]:border-b esf:[&_th]:border-secondary-200 esf:[&_td]:p-8 esf:[&_td]:text-left esf:[&_td]:border-r esf:[&_td]:border-b esf:[&_td]:border-secondary-200">
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
