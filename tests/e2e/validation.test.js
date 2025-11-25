const { test } = require('@playwright/test');
const { testFieldValidationMessage } = require('./helpers/tests');
const {
	TIMEOUT,
	SUBMIT_URL,
	openUrl,
	setTestEnvironment,
	submitFormAction,
	populateInput,
	waitFormLoaded,
	populatePhone,
} = require('./helpers');

test.describe('Validation tests', () => {

	test('should show required validation message for all fields when empty', async ({ page }) => {
	await openUrl(page, 'validation');
	await setTestEnvironment(page);

	await submitFormAction(page);

	const requiredMessages = 'This field is required.';

		await testFieldValidationMessage(page, 'input-email', requiredMessages);
		await testFieldValidationMessage(page, 'input-regular', requiredMessages);
		await testFieldValidationMessage(page, 'input-url', requiredMessages);
		await testFieldValidationMessage(page, 'input-number', requiredMessages);
		await testFieldValidationMessage(page, 'country-single', requiredMessages);
		await testFieldValidationMessage(page, 'country-multiple', requiredMessages);
		await testFieldValidationMessage(page, 'date-single', requiredMessages);
		await testFieldValidationMessage(page, 'date-multiple', requiredMessages);
		await testFieldValidationMessage(page, 'date-range', requiredMessages);
		await testFieldValidationMessage(page, 'date-time', requiredMessages);
		await testFieldValidationMessage(page, 'rating', requiredMessages);
		await testFieldValidationMessage(page, 'phone', requiredMessages);
		await testFieldValidationMessage(page, 'radios', requiredMessages);
		await testFieldValidationMessage(page, 'select-single', requiredMessages);
		await testFieldValidationMessage(page, 'select-multiple', requiredMessages);
		await testFieldValidationMessage(page, 'checkboxes', requiredMessages);
		await testFieldValidationMessage(page, 'textarea', requiredMessages);
	});

	test('should show validation message for input email field when email is not valid', async ({ page }) => {
		await openUrl(page, 'validation');
		await setTestEnvironment(page);
		await populateInput(page, 'input-email', 'invalid-email');
		await submitFormAction(page);
		await testFieldValidationMessage(page, 'input-email', 'Enter a valid email address.');
	});

	test('should show validation message for input email field when email is not valid TLD', async ({ page }) => {
		await openUrl(page, 'validation');
		await setTestEnvironment(page);
		await populateInput(page, 'input-email', 'john.doe@example.invalid');
		await submitFormAction(page);
		await testFieldValidationMessage(page, 'input-email', 'This e-mails top level domain is not valid.');
	});

	test('should show validation message for input URL field when URL is not valid', async ({ page }) => {
		await openUrl(page, 'validation');
		await setTestEnvironment(page);
		await populateInput(page, 'input-url', 'invalid-url');
		await submitFormAction(page);
		await testFieldValidationMessage(page, 'input-url', 'This URL is not valid.');
	});

	test('should show validation message for input number if value is lower than expected', async ({ page }) => {
		await openUrl(page, 'validation');
		await setTestEnvironment(page);
		await populateInput(page, 'input-number-min-max', '1');
		await submitFormAction(page);
		await testFieldValidationMessage(page, 'input-number-min-max', 'This field value is less than expected. Minimal number should be 5.');
	});

	test('should show validation message for input number if value is greater than expected', async ({ page }) => {
		await openUrl(page, 'validation');
		await setTestEnvironment(page);
		await populateInput(page, 'input-number-min-max', '20');
		await submitFormAction(page);
		await testFieldValidationMessage(page, 'input-number-min-max', 'This field value is more than expected. Maximal number should be 10.');
	});

	test('should show validation message for input text if number of characters is lower than expected', async ({ page }) => {
		await openUrl(page, 'validation');
		await setTestEnvironment(page);
		await populateInput(page, 'input-regular', '123');
		await submitFormAction(page);
		await testFieldValidationMessage(page, 'input-regular', 'This field value has less characters than expected. We expect minimum 5 characters.');
	});

	test('should show validation message for input text if number of characters is greater than expected', async ({ page }) => {
		await openUrl(page, 'validation');
		await setTestEnvironment(page);
		await populateInput(page, 'input-regular', '12345678901234567890');
		await submitFormAction(page);
		await testFieldValidationMessage(page, 'input-regular', 'This field value has more characters than expected. We expect maximum 10 characters.');
	});
});

