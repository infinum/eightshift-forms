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
	populatePhone,
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

	const requiredMessages = 'This field is required.';

	await testFieldValidationMessage(driver, 'input-email', requiredMessages);
	await testFieldValidationMessage(driver, 'input-regular', requiredMessages);
	await testFieldValidationMessage(driver, 'input-url', requiredMessages);
	await testFieldValidationMessage(driver, 'input-number', requiredMessages);
	await testFieldValidationMessage(driver, 'country-single', requiredMessages);
	await testFieldValidationMessage(driver, 'country-multiple', requiredMessages);
	await testFieldValidationMessage(driver, 'date-single', requiredMessages);
	await testFieldValidationMessage(driver, 'date-multiple', requiredMessages);
	await testFieldValidationMessage(driver, 'date-range', requiredMessages);
	await testFieldValidationMessage(driver, 'date-time', requiredMessages);
	await testFieldValidationMessage(driver, 'rating', requiredMessages);
	await testFieldValidationMessage(driver, 'phone', requiredMessages);
	await testFieldValidationMessage(driver, 'radios', requiredMessages);
	await testFieldValidationMessage(driver, 'select-single', requiredMessages);
	await testFieldValidationMessage(driver, 'select-multiple', requiredMessages);
	await testFieldValidationMessage(driver, 'checkboxes', requiredMessages);
	await testFieldValidationMessage(driver, 'textarea', requiredMessages);
});

// test('should show validation message for input email field when email is not valid', async () => {
// 	await populateInput(driver, 'input-email', 'invalid-email');
// 	await submitFormAction(driver);
// 	await testFieldValidationMessage(driver, 'input-email', 'Enter a valid email address.');
// });

// test('should show validation message for input email field when email is not valid TLD', async () => {
// 	await populateInput(driver, 'input-email', 'john.doe@example.invalid');
// 	await submitFormAction(driver);
// 	await testFieldValidationMessage(driver, 'input-email', 'This e-mails top level domain is not valid.');
// });

// test('should show validation message for input URL field when URL is not valid', async () => {
// 	await populateInput(driver, 'input-url', 'invalid-url');
// 	await submitFormAction(driver);
// 	await testFieldValidationMessage(driver, 'input-url', 'This URL is not valid.');
// });

// test('should show validation message for input number if value is lower than expected', async () => {
// 	await populateInput(driver, 'input-number-min-max', '1');
// 	await submitFormAction(driver);
// 	await testFieldValidationMessage(driver, 'input-number-min-max', 'This field value is less than expected. Minimal number should be 5.');
// });

// test('should show validation message for input number if value is greater than expected', async () => {
// 	await populateInput(driver, 'input-number-min-max', '20');
// 	await submitFormAction(driver);
// 	await testFieldValidationMessage(driver, 'input-number-min-max', 'This field value is more than expected. Maximal number should be 10.');
// });

// test('should show validation message for input text if number of characters is lower than expected', async () => {
// 	await populateInput(driver, 'input-regular', '123');
// 	await submitFormAction(driver);
// 	await testFieldValidationMessage(driver, 'input-regular', 'This field value has less characters than expected. We expect minimum 5 characters.');
// });

// test('should show validation message for input text if number of characters is greater than expected', async () => {
// 	await populateInput(driver, 'input-regular', '12345678901234567890');
// 	await submitFormAction(driver);
// 	await testFieldValidationMessage(driver, 'input-regular', 'This field value has more characters than expected. We expect maximum 10 characters.');
// });

test('should show validation message for input number if value contains anything but numbers', async () => {
	await populateInput(driver, 'input-number', 'asdf1234');
	await submitFormAction(driver);
	await testFieldValidationMessage(driver, 'input-number', 'This field should only contain numbers.');
});

