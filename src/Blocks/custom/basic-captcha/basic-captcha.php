<?php

/**
 * Template for the BasicCaptcha Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Captcha\BasicCaptcha;

$blockClass = $attributes['blockClass'] ?? '';
$name = $attributes['name'] ?? BasicCaptcha::RESULT_KEY;
$theme = $attributes['theme'] ?? '';
$firstNumber = wp_rand(1, 15);
$secondNumber = wp_rand(1, 15);

$blockClasses = Components::classnames([
	$blockClass,
	"js-{$blockClass}",
	! empty($theme) ? "{$blockClass}__theme--{$theme}" : '',
]);

if (empty($this)) {
	return;
}

?>

<div class="<?php echo esc_attr($blockClasses); ?>">
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'label',
		[
			'blockClass' => $attributes['blockClass'] ?? '',
			'label' => $attributes['label'] ?? '',
			'id' => $attributes['id'] ?? '',
			'theme' => $attributes['theme'] ?? '',
		]
	);
	?>
	<div class="<?php echo esc_attr("{$blockClass}__content-wrap {$blockClass}__theme--{$theme}"); ?>">
	<div class="<?php echo esc_attr("{$blockClass}__captcha-number"); ?>" >
		<?php echo intval($firstNumber); ?>
		<input type="hidden" name="<?php echo esc_attr(BasicCaptcha::FIRST_NUMBER_KEY); ?>" readonly value="<?php echo intval($firstNumber); ?>" />
	</div>
	<div class="<?php echo esc_attr("{$blockClass}__captcha-plus"); ?>"> + </div>
	<div class="<?php echo esc_attr("{$blockClass}__captcha-number"); ?>">
		<?php echo intval($secondNumber); ?>
		<input type="hidden" name="<?php echo esc_attr(BasicCaptcha::SECOND_NUMBER_KEY); ?>" readonly value="<?php echo intval($secondNumber); ?>" />
	</div>
	<div class="<?php echo esc_attr("{$blockClass}__captcha-equals"); ?>"> = </div>
	<input
		name="<?php echo esc_attr($name); ?>"
		class="<?php echo esc_attr("{$blockClass}__captcha"); ?>"
		type="text"
		required
		aria-describedby="basic-captcha-description"
	/>
	</div>

	<?php /* translators: %1$d & %2$d is replaced with "int" */ ?>
	<div id="basic-captcha-description"><?php printf(esc_html__('Math captcha. Input sum of %1$d and %2$d.', 'eightshift-forms'), intval($firstNumber), intval($secondNumber)); ?></div>
</div>
