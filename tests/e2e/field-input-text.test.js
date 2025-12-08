const { test, expect } = require('@playwright/test');
const {
	openUrl,
	submitFormAction,
	populateInput,
	getFieldInput,
	getFieldLabel,
	getField,
	getFieldError,
	getFieldBeforeContent,
	getFieldAfterContent,
	getFieldSuffixContent,
	getFieldHelp,
} = require('./helpers');
const { testFieldSingle, testFieldSingleMissing } = require('./helpers/tests');

const URL = 'field-input-text';

test.describe('Field input text tests', () => {
	test('input-default', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-default';
		const input = await getFieldInput(page, selector);
		const label = await getFieldLabel(page, selector);
		const field = await getField(page, selector);
		const error = await getFieldError(page, selector);


		await expect(field).toHaveClass('es-field es-field--input js-es-block-field');
		await expect(input).toHaveAttribute('type', 'text');
		await expect(input).toHaveAttribute('name', 'input-default');
		await expect(input).toHaveAttribute('aria-invalid', 'false');

		await expect(error).toBeEmpty();

		await expect(label).toHaveText('input-default');
	});

	test('input-placeholder-with-label', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-placeholder-with-label';
		const input = await getFieldInput(page, selector);
		const label = await getFieldLabel(page, selector);

		await expect(input).toHaveAttribute('placeholder', 'input-placeholder-with-label');
		await expect(label).toHaveText('input-placeholder-with-label');
	});

	test('input-placeholder-use-label', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-placeholder-use-label';
		const input = await getFieldInput(page, selector);
		const label = await getFieldLabel(page, selector);

		await expect(input).toHaveAttribute('placeholder', 'input-placeholder-use-label');
		await expect(label).not.toBeVisible();
	});

	test('input-no-placeholder-no-label', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-no-placeholder-no-label';
		const input = await getFieldInput(page, selector);
		const label = await getFieldLabel(page, selector);

		await expect(input).not.toHaveAttribute('placeholder');
		await expect(label).not.toBeVisible();
	});

	test('input-placeholder-no-label', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-placeholder-no-label';
		const input = await getFieldInput(page, selector);
		const label = await getFieldLabel(page, selector);

		await expect(input).toHaveAttribute('placeholder', 'input-placeholder-no-label');
		await expect(label).not.toBeVisible();
	});

	test('input-grid', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-grid';
		const field = await getField(page, selector);

		await expect(field).toHaveCSS('flex', '0 0 50%');
		await expect(field).toHaveCSS('max-inline-size', '50%');
		await expect(field).toHaveCSS('padding-inline', '10px');
		await expect(field).toHaveCSS('margin-block-end', '30px');
	});

	test('input-value', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-value';
		const input = await getFieldInput(page, selector);
		const field = await getField(page, selector);

		await expect(field).toHaveClass(/(^|\s)es-form-is-filled(\s|$)/);
		await expect(input).toHaveValue('input-value');
	});

	test('input-hidden', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-hidden';
		const input = await getFieldInput(page, selector);
		const field = await getField(page, selector);

		await expect(field).toBeHidden();
		await expect(field).toHaveClass(/(^|\s)es-form-is-hidden(\s|$)/);
		await expect(input).toHaveAttribute('type', 'text');
	});

	test('input-readonly', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-readonly';
		const input = await getFieldInput(page, selector);

		await expect(input).toHaveAttribute('readonly');
	});

	test('input-disabled', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-disabled';
		const input = await getFieldInput(page, selector);
		const field = await getField(page, selector);

		await expect(field).toHaveClass(/(^|\s)es-form-is-disabled(\s|$)/);
		await expect(input).toHaveAttribute('disabled');
	});

	test('input-required', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-required';
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(field).toHaveClass(/(^|\s)es-field--is-required(\s|$)/);
		await expect(input).toHaveAttribute('aria-required', 'true');
	});

	test('input-required-submit-show-error-message-if-value-is-empty', async ({ page }) => {
		await openUrl(page, URL);
		await submitFormAction(page);

		const selector = 'input-required';
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);
		const error = await getFieldError(page, selector);

		await expect(error).toHaveText('This field is required.');
		await expect(input).toHaveAttribute('aria-invalid', 'true');
		await expect(field).toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-required-submit-show-no-error-message-if-value-is-not-empty', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-required';
		await populateInput(page, selector, 'test');
		await submitFormAction(page);

		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);
		const error = await getFieldError(page, selector);

		await expect(error).toBeEmpty();
		await expect(input).toHaveAttribute('aria-invalid', 'false');
		await expect(field).not.toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-submit-show-error-message-if-value-is-less-than-expected', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5';
		await populateInput(page, selector, '1234');
		await submitFormAction(page);

		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);
		const error = await getFieldError(page, selector);

		await expect(error).toHaveText('This field value has less characters than expected. We expect minimum 5 characters.');
		await expect(input).toHaveAttribute('aria-invalid', 'true');
		await expect(field).toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-submit-show-no-error-message-if-value-is-greater-than-expected', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5';
		await populateInput(page, selector, '1234567890');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toBeEmpty();
		await expect(input).toHaveAttribute('aria-invalid', 'false');
		await expect(field).not.toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-submit-show-no-error-message-if-value-is-exact-number-of-characters', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5';
		await populateInput(page, selector, '12345');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toBeEmpty();
		await expect(input).toHaveAttribute('aria-invalid', 'false');
		await expect(field).not.toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-max-10-submit-show-error-message-if-value-is-greater-than-expected', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-max-10';
		await populateInput(page, selector, '1234567890123');
		await submitFormAction(page);

		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);
		const error = await getFieldError(page, selector);

		await expect(error).toHaveText('This field value has more characters than expected. We expect maximum 10 characters.');
		await expect(input).toHaveAttribute('aria-invalid', 'true');
		await expect(field).toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-max-10-submit-show-no-error-message-if-value-is-less-than-expected', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-max-10';
		await populateInput(page, selector, '1234');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toBeEmpty();
		await expect(input).toHaveAttribute('aria-invalid', 'false');
		await expect(field).not.toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-max-10-submit-show-no-error-message-if-value-is-exact-number-of-characters', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-max-10';
		await populateInput(page, selector, '1234567890');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toBeEmpty();
		await expect(input).toHaveAttribute('aria-invalid', 'false');
		await expect(field).not.toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-match-pattern', async ({ page }) => {
		// TODO: Implement this test.
	});

	test('input-min-5-max-10-req-pattern-submit-show-error-message-if-value-is-less-than-min-expected', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5-max-10-req-pattern';
		await populateInput(page, selector, '1234');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toHaveText('This field value has less characters than expected. We expect minimum 5 characters.');
		await expect(input).toHaveAttribute('aria-invalid', 'true');
		await expect(field).toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-max-10-req-pattern-submit-show-error-message-if-value-is-greater-than-max-expected', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5-max-10-req-pattern';
		await populateInput(page, selector, '1234567890123');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toHaveText('This field value has more characters than expected. We expect maximum 10 characters.');
		await expect(input).toHaveAttribute('aria-invalid', 'true');
		await expect(field).toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-max-10-req-pattern-submit-show-no-error-message-if-value-is-exact-number-of-min-characters', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5-max-10-req-pattern';
		await populateInput(page, selector, '12345');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toBeEmpty();
		await expect(input).toHaveAttribute('aria-invalid', 'false');
		await expect(field).not.toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-max-10-req-pattern-submit-show-no-error-message-if-value-is-exact-number-of-max-characters', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5-max-10-req-pattern';
		await populateInput(page, selector, '1234567890');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toBeEmpty();
		await expect(input).toHaveAttribute('aria-invalid', 'false');
		await expect(field).not.toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-max-10-req-pattern-submit-show-no-error-message-if-value-is-in-range', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5-max-10-req-pattern';
		await populateInput(page, selector, '1234567');
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toBeEmpty();
		await expect(input).toHaveAttribute('aria-invalid', 'false');
		await expect(field).not.toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-max-10-req-pattern-submit-show-required-error-message-if-value-is-empty', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-min-5-max-10-req-pattern';
		await submitFormAction(page);

		const error = await getFieldError(page, selector);
		const field = await getField(page, selector);
		const input = await getFieldInput(page, selector);

		await expect(error).toHaveText('This field is required.');
		await expect(input).toHaveAttribute('aria-invalid', 'true');
		await expect(field).toHaveClass(/(^|\s)es-form-has-error(\s|$)/);
	});

	test('input-min-5-max-10-req-pattern-submit-show-match-pattern-error-message-if-value-is-not-matching-pattern', async ({ page }) => {
		// TODO: Implement this test.
	});

	test('input-more', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-more';

		const beforeContent = await getFieldBeforeContent(page, selector);
		const afterContent = await getFieldAfterContent(page, selector);
		const suffixContent = await getFieldSuffixContent(page, selector);
		const help = await getFieldHelp(page, selector);

		await expect(beforeContent).toHaveText('Below the field label');
		await expect(afterContent).toHaveText('Above the help text');
		await expect(suffixContent).toHaveText('After field text');
		await expect(help).toHaveText('Help text');
	});

	test('input-tracking', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'input-tracking';
		const field = await getField(page, selector);

		await expect(field).toHaveAttribute('data-tracking', 'tracking');
	});

	test('input-submit-check-submitted-data', async ({ page }) => {
		await openUrl(page, URL);
		const payload = await submitFormAction(page);

		testFieldSingle(payload, 'input-default', '', 'input', 'text', '');
		testFieldSingle(payload, 'input-value', 'input-value', 'input', 'text', '');
		testFieldSingle(payload, 'input-readonly', '', 'input', 'text', '');
		testFieldSingle(payload, 'input-readonly-value', 'input-readonly-value', 'input', 'text', '');
		testFieldSingleMissing(payload, 'input-disabled');
		testFieldSingleMissing(payload, 'input-disabled-value');
		testFieldSingle(payload, 'input-hidden', '', 'input', 'text', '');
		testFieldSingle(payload, 'input-hidden-value', 'input-hidden-value', 'input', 'text', '');
	});
});
