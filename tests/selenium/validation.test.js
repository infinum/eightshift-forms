/**
 * @jest-environment jsdom
 */

const { Builder } = require('selenium-webdriver');
const { testFieldValidationMessage } = require('./helpers/tests');
const {
	TIMEOUT,
	SUBMIT_URL,
	openUrl,
	setTestEnvironment,
	submitFormAction,
	populateInput,
	waitFormLoaded,
} = require('./helpers');

let driver;

beforeAll(async () => {
	driver = await new Builder().forBrowser('chrome').build();

	await openUrl(driver, 'validation');
	await setTestEnvironment(driver);

	// Wait for the form container to exist before setting up event listener
	await waitFormLoaded(driver);
}, TIMEOUT);

afterAll(async () => {
	// await driver.quit();
}, TIMEOUT);

test('should show required validation message for all fields when empty', async () => {
	await submitFormAction(driver);

	await testFieldValidationMessage(driver, 'input-email', 'This field is required.');
	await testFieldValidationMessage(driver, 'input-regular', 'This field is required.');
	await testFieldValidationMessage(driver, 'input-url', 'This field is required.');
	await testFieldValidationMessage(driver, 'input-number', 'This field is required.');
	await testFieldValidationMessage(driver, 'country-single', 'This field is required.');
	await testFieldValidationMessage(driver, 'country-multiple', 'This field is required.');
	await testFieldValidationMessage(driver, 'date-single', 'This field is required.');
	await testFieldValidationMessage(driver, 'date-multiple', 'This field is required.');
	await testFieldValidationMessage(driver, 'date-range', 'This field is required.');
	await testFieldValidationMessage(driver, 'date-time', 'This field is required.');
	await testFieldValidationMessage(driver, 'rating', 'This field is required.');
	await testFieldValidationMessage(driver, 'phone', 'This field is required.');
	await testFieldValidationMessage(driver, 'radios', 'This field is required.');
	await testFieldValidationMessage(driver, 'select-single', 'This field is required.');
	await testFieldValidationMessage(driver, 'select-multiple', 'This field is required.');
	await testFieldValidationMessage(driver, 'checkboxes', 'This field is required.');
	await testFieldValidationMessage(driver, 'textarea', 'This field is required.');
});

test('should show validation message for input email field when email is not valid', async () => {
	await populateInput(driver, 'input-email', 'invalid-email');
	await submitFormAction(driver);
	await testFieldValidationMessage(driver, 'input-email', 'Enter a valid email address.');
});

test('should show validation message for input email field when email is not valid TLD', async () => {
	await populateInput(driver, 'input-email', 'john.doe@example.invalid');
	await submitFormAction(driver);
	await testFieldValidationMessage(driver, 'input-email', 'This e-mails top level domain is not valid.');
});

test('should show validation message for input URL field when URL is not valid', async () => {
	await populateInput(driver, 'input-url', 'invalid-url');
	await submitFormAction(driver);
	await testFieldValidationMessage(driver, 'input-url', 'This URL is not valid.');
});
