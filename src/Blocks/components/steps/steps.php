<?php

/**
 * Template for the Steps Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$stepsTitle = Helpers::checkAttr('stepsTitle', $attributes, $manifest);
$stepsContent = Helpers::checkAttr('stepsContent', $attributes, $manifest);

if (!$stepsContent) {
	return;
}

?>
<div>
	<?php if ($stepsTitle) { ?>
		<div class="esf:inline-block esf:mb-16 esf:text-[0.95rem] esf:font-medium">
			<?php echo esc_html($stepsTitle); ?>
		</div>
	<?php } ?>

	<ul class="esf-steps-list esf:flex esf:flex-col esf:gap-8 esf:m-0 esf:p-0 esf:list-none">
		<?php foreach ($stepsContent as $step) { ?>
			<li class="esf-steps-item esf:flex esf:items-baseline esf:gap-8 esf:m-0 esf:p-0 esf:text-xs esf:leading-relaxed esf:text-gray-500 esf:[&_a]:text-accent-600 esf:[&_a]:underline esf:[&_a]:decoration-dotted">
				<span>
					<?php echo wp_kses_post($step); ?>
				</span>
			</li>
		<?php } ?>
	</ul>
</div>
