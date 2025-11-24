const { test } = require('@playwright/test');
const { testFieldSimple, testFieldMultiple, testMessage } = require('./helpers/tests');
const {
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
} = require('./helpers');

let payload = null;

test.describe('Basic form tests', () => {
	test.beforeAll(async ({ browser }) => {
		const context = await browser.newContext();
		const page = await context.newPage();

		await openUrl(page, 'basic');
		await setTestEnvironment(page);

		// INPUT.
		await populateInput(page, 'input-email', 'john.doe@example.com');
		// await populateInput(page, 'input-regular', 'John Doe');
		// await populateInput(page, 'input-url', 'https://eightshift.com/');
		// await populateInput(page, 'input-number', '1234567890');
		// await populateRange(page, 'input-range');

		// // SELECT.
		// await populateSelect(page, 'country-single', 'Croatia');
		// await populateSelectMultiple(page, 'country-multiple', ['Croatia', 'United States']);

		// // DATE.
		// await populateDateSingle(page, 'date-single');
		// await populateDateMultiple(page, 'date-multiple', true);
		// await populateDateMultiple(page, 'date-range');
		// await populateDateSingle(page, 'date-time', true);

		// // RATING.
		// await populateRating(page, 'rating', 5);

		// // PHONE.
		// await populatePhone(page, 'phone', '385911234567');

		// // RADIO.
		// await populateRadio(page, 'radios', 'radio-2');

		// // SELECT.
		// await populateSelect(page, 'select-single', 'Option 2');
		// await populateSelectMultiple(page, 'select-multiple', ['Option 2', 'Option 3']);

		// // CHECKBOXES.
		// await populateCheckbox(page, 'checkboxes', 'checkbox-2');

		// // TEXTAREA.
		// await populateTextarea(page, 'textarea', 'Hello, world!');

		// SUBMIT and get request data
		payload = await submitFormAction(page, SUBMIT_URL, 5000);

		await context.close();
	});

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
		testFieldSimple(payload, 'date-single', '2025-11-24', 'date', 'date', '');
	});

	test('should populate date multiple field', async () => {
		testFieldSimple(payload, 'date-multiple', '2025-11-24---2025-11-25', 'date', 'date', '');
	});

	test('should populate date range field', async () => {
		testFieldSimple(payload, 'date-range', '2025-11-24---2025-11-25', 'date', 'date', '');
	});

	test('should populate date time field', async () => {
		testFieldSimple(payload, 'date-time', '2025-11-24 12:00', 'dateTime', 'datetime-local', '');
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

	test('should submit form and show success', async ({ page }) => {
		await testMessage(page, 'success');
	});

	// test('should submit form and show success', async ({ page }) => {
	// 	await openUrl(page, 'basic');
	// 	await setTestEnvironment(page);
	// 	await waitFormLoaded(page);

	// 	// INPUT.
	// 	await populateInput(page, 'input-email', 'john.doe@example.com');
	// 	await populateInput(page, 'input-regular', 'John Doe');
	// 	await populateInput(page, 'input-url', 'https://eightshift.com/');
	// 	await populateInput(page, 'input-number', '1234567890');
	// 	await populateRange(page, 'input-range');

	// 	// SELECT.
	// 	await populateSelect(page, 'country-single', 'Croatia');
	// 	await populateSelectMultiple(page, 'country-multiple', ['Croatia', 'United States']);

	// 	// DATE.
	// 	await populateDateSingle(page, 'date-single');
	// 	await populateDateMultiple(page, 'date-multiple', true);
	// 	await populateDateMultiple(page, 'date-range');
	// 	await populateDateSingle(page, 'date-time', true);

	// 	// RATING.
	// 	await populateRating(page, 'rating', 5);

	// 	// PHONE.
	// 	await populatePhone(page, 'phone', '385911234567');

	// 	// RADIO.
	// 	await populateRadio(page, 'radios', 'radio-2');

	// 	// SELECT.
	// 	await populateSelect(page, 'select-single', 'Option 2');
	// 	await populateSelectMultiple(page, 'select-multiple', ['Option 2', 'Option 3']);

	// 	// CHECKBOXES.
	// 	await populateCheckbox(page, 'checkboxes', 'checkbox-2');

	// 	// TEXTAREA.
	// 	await populateTextarea(page, 'textarea', 'Hello, world!');

	// 	// SUBMIT.
	// 	await submitFormAction(page);

	// 	await testMessage(page, 'success');
	// });
});

