/**
 * @jest-environment jsdom
 */

const { Builder } = require('selenium-webdriver');
const { testFieldSimple, testFieldMultiple, testMessage } = require('./helpers/tests');
const {
	TIMEOUT,
	SUBMIT_URL,
	openUrl,
	setTestEnvironment,
	submitFormAction,
	populateInput,
	populateCheckbox,
	populateRadio,
	populateRating,
	populateTextarea,
	populatePhone,
	populateRange,
	populateSelect,
	populateSelectMultiple,
	populateDateSingle,
	populateDateMultiple,
	setupNetworkInterception,
	getNetworkRequest,
	waitFormLoaded,
} = require('./helpers');

let driver;
let payload = null;

beforeAll(async () => {
	driver = await new Builder().forBrowser('chrome').build();

	await openUrl(driver, 'basic');
	await setTestEnvironment(driver);

	// Wait for the form container to exist before setting up event listener
	await waitFormLoaded(driver);

	// INPUT.
	await populateInput(driver, 'input-email', 'john.doe@example.com');
	await populateInput(driver, 'input-regular', 'John Doe');
	await populateInput(driver, 'input-url', 'https://eightshift.com/');
	await populateInput(driver, 'input-number', '1234567890');
	await populateRange(driver, 'input-range');

	// SELECT.
	await populateSelect(driver, 'country-single', 'Croatia');
	await populateSelectMultiple(driver, 'country-multiple', ['Croatia', 'United States']);

	// // DATE.
	await populateDateSingle(driver, 'date-single');
	await populateDateMultiple(driver, 'date-multiple', true);
	await populateDateMultiple(driver, 'date-range');
	await populateDateSingle(driver, 'date-time', true);

	// // RATING.
	await populateRating(driver, 'rating', 5);

	// PHONE.
	await populatePhone(driver, 'phone', '385911234567');

	// RADIO.
	await populateRadio(driver, 'radios', 'radio-2');

	// SELECT.
	await populateSelect(driver, 'select-single', 'Option 2');
	await populateSelectMultiple(driver, 'select-multiple', ['Option 2', 'Option 3']);

	// CHECKBOXES.
	await populateCheckbox(driver, 'checkboxes', 'checkbox-2');

	// TEXTAREA.
	await populateTextarea(driver, 'textarea', 'Hello, world!');

	// Set up network interception BEFORE submitting the form
	await setupNetworkInterception(driver, SUBMIT_URL);

	// SUBMIT.
	await submitFormAction(driver);

	const request = await getNetworkRequest(driver, SUBMIT_URL, 5000);

	if (request.postData) {
		payload = JSON.parse(request.postData);
	}
}, TIMEOUT);

afterAll(async () => {
	await driver.quit();
}, TIMEOUT);

test('should populate input email field', async () => {
	testFieldSimple(payload, 'input-email', 'john.doe@example.com', 'input', 'email', '');
});

test('should populate input regular field', async () => {
	testFieldSimple(payload, 'input-regular', 'John Doe', 'input', 'text', '');
});

test('should populate input url field', async () => {
	testFieldSimple(payload, 'input-url', 'https://eightshift.com/', 'input', 'url', '');
});

test('should populate input number field', async () => {
	testFieldSimple(payload, 'input-number', '1234567890', 'input', 'number', '');
});

test('should populate input range field', async () => {
	testFieldSimple(payload, 'input-range', '34', 'range', 'range', '');
});

test('should populate country single field', async () => {
	testFieldSimple(payload, 'country-single', ['Croatia'], 'country', 'country', '');
});

test('should populate country multiple field', async () => {
	testFieldSimple(payload, 'country-multiple', ['Croatia', 'United States'], 'country', 'country', '');
});

test('should populate date single field', async () => {
	testFieldSimple(payload, 'date-single', '2025-11-20', 'date', 'date', '');
});

test('should populate date multiple field', async () => {
	testFieldSimple(payload, 'date-multiple', '2025-11-20---2025-11-21', 'date', 'date', '');
});

test('should populate date range field', async () => {
	testFieldSimple(payload, 'date-range', '2025-11-20---2025-11-21', 'date', 'date', '');
});

test('should populate date time field', async () => {
	testFieldSimple(payload, 'date-time', '2025-11-20 12:00', 'dateTime', 'datetime-local', '');
});

test('should populate rating field', async () => {
	testFieldMultiple(payload, 'rating', 5, 'rating', 'rating', ['1', '2', '3', '4', '5']);
});

test('should populate phone field', async () => {
	testFieldSimple(payload, 'phone', '385911234567', 'phone', 'phone', '');
});

test('should populate radios field', async () => {
	testFieldMultiple(payload, 'radios', 'radio-2', 'radio', 'radio', ['radio-1', 'radio-2', 'radio-3']);
});

test('should populate select single field', async () => {
	testFieldSimple(payload, 'select-single', ['option-2'], 'select', 'select', '');
});

test('should populate select multiple field', async () => {
	testFieldSimple(payload, 'select-multiple', ['option-2', 'option-3'], 'select', 'select', '');
});

test('should populate checkboxes field', async () => {
	testFieldMultiple(payload, 'checkboxes', ['checkbox-2'], 'checkbox', 'checkbox', [
		'checkbox-1',
		'checkbox-2',
		'checkbox-3',
	]);
});

test('should populate textarea field', async () => {
	testFieldSimple(payload, 'textarea', 'Hello, world!', 'textarea', 'textarea', '');
});

test('should submit form and show success', async () => {
	await testMessage(driver, 'success');
});
