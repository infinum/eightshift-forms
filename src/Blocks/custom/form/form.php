<?php

/**
 * Template for the Form Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Forms;
use EightshiftForms\Config\Config;
use EightshiftForms\Hooks\Actions;
use EightshiftForms\Rest\DynamicsCrmRoute;
use EightshiftForms\Rest\SendEmailRoute;
use EightshiftForms\Rest\AbstractBuckarooRoute as Buckaroo_Route;
use EightshiftForms\Rest\BuckarooEmandateRoute;
use EightshiftForms\Rest\MailchimpRoute;
use EightshiftForms\Rest\MailerliteRoute;

$currentUrl = ! empty(\get_permalink()) ? \get_permalink() : '';
$blockClass = $attributes['blockClass'] ?? '';
$formAction = $attributes['formAction'] ?? '';
$formMethod = $attributes['formMethod'] ?? '';
$formTarget = $attributes['formTarget'] ?? '';
$formClasses = $attributes['formClasses'] ?? '';
$formId = $attributes['formId'] ?? 'form-' . hash('crc32', time() . wp_rand(0, 10000));
$formType = $attributes['formType'] ?? '';
$formTypesComplex = $attributes['formTypesComplex'] ?? [];
$formTypesComplexRedirect = $attributes['formTypesComplexRedirect'] ?? [];
$isFormComplex = isset($attributes['formIsComplexType']) ? filter_var($attributes['formIsComplexType'], FILTER_VALIDATE_BOOL) : false;
$formTheme = $attributes['theme'] ?? '';
$formSuccessMessage = $attributes['formSuccessMessage'] ?? '';
$formErrorMessage = $attributes['formErrorMessage'] ?? '';
$referralUrl = isset($attributes['referralUrl']) ? $attributes['referralUrl'] : $currentUrl;
$formShouldRedirectOnSuccess = isset($attributes['formShouldRedirectOnSuccess']) ? filter_var($attributes['formShouldRedirectOnSuccess'], FILTER_VALIDATE_BOOL) : false;
$redirectUrlSuccess = $attributes['formRedirectSuccess'] ?? '';
$dynamicsCrmEntity = $attributes['formDynamicsEntity'] ?? '';
$formEmailTo = $attributes['formEmailTo'] ?? '';
$formEmailSubject = $attributes['formEmailSubject'] ?? '';
$formEmailMessage = $attributes['formEmailMessage'] ?? '';
$formEmailAdditionalHeaders = $attributes['formEmailAdditionalHeaders'] ?? '';
$emailSendConfirmToSender = isset($attributes['formEmailSendConfirmationToSender']) ? filter_var($attributes['formEmailSendConfirmationToSender'], FILTER_VALIDATE_BOOL) : false;
$formEmailConfirmationSubject = $attributes['formEmailConfirmationSubject'] ?? '';
$formEmailConfirmationMessage = $attributes['formEmailConfirmationMessage'] ?? '';
$formBuckarooRedirectUrl = $attributes['formBuckarooRedirectUrl'] ?? '';
$formBuckarooRedirectUrlCancel = ! empty($currentUrl) ? $currentUrl : \home_url();
$formBuckarooRedirectUrlError = $attributes['formBuckarooRedirectUrlError'] ?? '';
$formBuckarooRedirectUrlReject = $attributes['formBuckarooRedirectUrlReject'] ?? '';
$formBuckarooService = $attributes['formBuckarooService'] ?? '';
$buckarooPaymentDesc = $attributes['formBuckarooPaymentDescription'] ?? '';
$buckarooEmandateDesc = $attributes['formBuckarooEmandateDescription'] ?? '';
$formBuckarooSequenceType = $attributes['formBuckarooSequenceType'] ?? '';
$buckarooIsRecurring = $formBuckarooSequenceType === '0';
$buckarooSequenceTypeFront = isset($attributes['formBuckarooIsSequenceTypeOnFrontend']) ? filter_var($attributes['formBuckarooIsSequenceTypeOnFrontend'], FILTER_VALIDATE_BOOLEAN) : false;
$formMailchimpListId = $attributes['formMailchimpListId'] ?? '';
$formMailchimpTags = $attributes['formMailchimpTags'] ?? [];
$mailchimpAddExisting = isset($attributes['formMailchimpAddExistingMembers']) ? filter_var($attributes['formMailchimpAddExistingMembers'], FILTER_VALIDATE_BOOL) : false;
$formMailerliteGroupId = $attributes['formMailerliteGroupId'] ?? '';
$customEventNames = $attributes['formEventNames'] ?? [];
$usedTypes = Forms::detectUsedTypes($isFormComplex, $formType, $formTypesComplex, $formTypesComplexRedirect);
$innerBlockContent = ! empty($innerBlockContent) ? $innerBlockContent : '';

$blockClasses = Components::classnames([
	$blockClass,
	$formClasses,
	'js-form',
	"js-form__type--{$formType}",
	! empty($formTheme) ? "{$blockClass}__theme--{$formTheme}" : '',
]);

if (empty($this)) {
	return;
}

?>

<div class="<?php echo esc_attr($blockClasses); ?>">
	<form
	id="<?php echo esc_attr($formId); ?>"
	class="<?php echo esc_attr("{$blockClass}__form js-{$blockClass}-form"); ?>"
	action="<?php echo esc_attr($formAction); ?>"
	method="<?php echo esc_attr($formMethod); ?>"
	target="<?php echo esc_attr($formTarget); ?>"
	target="<?php echo esc_attr($formTarget); ?>"
	<?php ! empty($formId) ? printf('id="%s"', esc_attr($formId)) : ''; // phpcs:ignore Eightshift.Security.CustomEscapeOutput.OutputNotEscaped ?>
	<?php $isFormComplex ? printf('data-is-form-complex') : ''; // phpcs:ignore Eightshift.Security.CustomEscapeOutput.OutputNotEscaped  ?>
	<?php $formShouldRedirectOnSuccess ? printf('data-redirect-on-success="%s"', esc_url($redirectUrlSuccess)) : ''; // phpcs:ignore Eightshift.Security.CustomEscapeOutput.OutputNotEscaped ?>

	<?php if (isset($usedTypes[Config::BUCKAROO_METHOD])) { ?>
		data-buckaroo-service="<?php echo esc_attr($formBuckarooService); ?>"
	<?php } ?>

	<?php if (! $isFormComplex) { ?>
		data-form-type="<?php echo esc_attr($formType); ?>"
	<?php } else { ?>
		data-form-types-complex="<?php echo esc_attr(implode(',', $formTypesComplex)); ?>"
		data-form-types-complex-redirect="<?php echo esc_attr(implode(',', $formTypesComplexRedirect)); ?>"
	<?php } ?>
	>
	<?php echo wp_kses_post($innerBlockContent); ?>

	<?php

	/**
	 * Project specific fields.
	 */
	if (has_action(Actions::EXTRA_FORM_FIELDS)) {
		do_action(Actions::EXTRA_FORM_FIELDS, $attributes ?? []);
	}

	/**
	 * Here we need to add some additional fields for specific methods.
	 */
	?>
	<input type="hidden" name="referral-url" value="<?php echo esc_url($referralUrl); ?>" />

	<?php if (isset($usedTypes[Config::DYNAMICS_CRM_METHOD])) { ?>
		<input type="hidden" name="<?php echo esc_attr(DynamicsCrmRoute::ENTITY_PARAM); ?>" value="<?php echo esc_attr($dynamicsCrmEntity); ?>" />
	<?php } ?>

	<?php if (isset($usedTypes[Config::EMAIL_METHOD])) { ?>
		<input type="hidden" name="<?php echo esc_attr(SendEmailRoute::TO_PARAM); ?>" value="<?php echo esc_attr($formEmailTo); ?>" />
		<input type="hidden" name="<?php echo esc_attr(SendEmailRoute::SUBJECT_PARAM); ?>" value="<?php echo esc_attr($formEmailSubject); ?>" />
		<input type="hidden" name="<?php echo esc_attr(SendEmailRoute::MESSAGE_PARAM); ?>" value="<?php echo esc_attr($formEmailMessage); ?>" />
		<input type="hidden" name="<?php echo esc_attr(SendEmailRoute::ADDITIONAL_HEADERS_PARAM); ?>" value="<?php echo esc_attr($formEmailAdditionalHeaders); ?>" />
		<input type="hidden" name="<?php echo esc_attr(SendEmailRoute::SEND_CONFIRMATION_TO_SENDER_PARAM); ?>" value="<?php echo (int) $emailSendConfirmToSender; ?>" />
		<input type="hidden" name="<?php echo esc_attr(SendEmailRoute::CONFIRMATION_SUBJECT_PARAM); ?>" value="<?php echo esc_attr($formEmailConfirmationSubject); ?>" />
		<input type="hidden" name="<?php echo esc_attr(SendEmailRoute::CONFIRMATION_MESSAGE_PARAM); ?>" value="<?php echo esc_attr($formEmailConfirmationMessage); ?>" />
	<?php } ?>

	<?php if (isset($usedTypes[Config::BUCKAROO_METHOD])) { ?>
		<input type="hidden" name="<?php echo esc_attr(Buckaroo_Route::REDIRECT_URL_PARAM); ?>" value="<?php echo esc_attr($formBuckarooRedirectUrl); ?>" />
		<input type="hidden" name="<?php echo esc_attr(Buckaroo_Route::REDIRECT_URL_CANCEL_PARAM); ?>" value="<?php echo esc_attr($formBuckarooRedirectUrlCancel); ?>" />
		<input type="hidden" name="<?php echo esc_attr(Buckaroo_Route::REDIRECT_URL_ERROR_PARAM); ?>" value="<?php echo esc_attr($formBuckarooRedirectUrlError); ?>" />
		<input type="hidden" name="<?php echo esc_attr(Buckaroo_Route::REDIRECT_URL_REJECT_PARAM); ?>" value="<?php echo esc_attr($formBuckarooRedirectUrlReject); ?>" />

		<?php if ($formBuckarooService === 'emandate') { ?>
		<input type="hidden" name="<?php echo esc_attr(BuckarooEmandateRoute::EMANDATE_DESCRIPTION_PARAM); ?>" value="<?php echo esc_attr($buckarooEmandateDesc); ?>" />

			<?php if (! $buckarooSequenceTypeFront && $buckarooIsRecurring) { ?>
			<input type="hidden" name="<?php echo esc_attr(BuckarooEmandateRoute::SEQUENCE_TYPE_IS_RECURRING_PARAM); ?>" value="1" />
			<?php } ?>
		<?php } ?>

		<?php if ($formBuckarooService === 'ideal') { ?>
		<input type="hidden" name="<?php echo esc_attr(Buckaroo_Route::PAYMENT_DESCRIPTION_PARAM); ?>" value="<?php echo esc_attr($buckarooPaymentDesc); ?>" />
		<?php } ?>
	<?php } ?>

	<?php if (isset($usedTypes[Config::MAILCHIMP_METHOD])) { ?>
		<input type="hidden" name="<?php echo esc_attr(MailchimpRoute::LIST_ID_PARAM); ?>" value="<?php echo esc_attr($formMailchimpListId); ?>" />
		<input type="hidden" name="<?php echo esc_attr(MailchimpRoute::ADD_EXISTING_MEMBERS_PARAM); ?>" value="<?php echo (int) $mailchimpAddExisting; ?>" />

		<?php foreach ($formMailchimpTags as $mailchimpTag) { ?>
		<input type="hidden" name="<?php echo esc_attr(MailchimpRoute::TAGS_PARAM); ?>[]" value="<?php echo esc_attr($mailchimpTag); ?>" />
		<?php } ?>
	<?php } ?>

	<?php if (isset($usedTypes[Config::MAILERLITE_METHOD])) { ?>
		<input type="hidden" name="<?php echo esc_attr(MailerliteRoute::GROUP_ID_PARAM); ?>" value="<?php echo esc_attr($formMailerliteGroupId); ?>" />
	<?php } ?>

	<?php if (isset($usedTypes[Config::CUSTOM_EVENT_METHOD])) { ?>
		<?php foreach ($customEventNames as $customEventName) { ?>
		<input type="hidden" name="custom-events[]" value="<?php echo esc_attr($customEventName); ?>" />
		<?php } ?>
	<?php } ?>

	<input type="hidden" name="form-unique-id" value="<?php echo esc_attr($formId); ?>" />
	<?php wp_nonce_field($formId, 'nonce', false); ?>
	</form>

	<?php echo wp_kses_post(Components::render('form-overlay')); ?>
	<?php echo wp_kses_post(Components::render('spinner', ['theme' => $formTheme])); ?>
	<?php echo wp_kses_post(Components::render('form-message', ['message' => $formSuccessMessage, 'type' => 'success', 'theme' => $formTheme])); ?>
	<?php echo wp_kses_post(Components::render('form-error-message-wrapper', ['theme' => $formTheme])); ?>
</div>

