const { test } = require('@playwright/test');

const { testFieldSimple, testFieldMultiple, testMessage } = require('./helpers/tests');
const {
	SUBMIT_URL,
	openUrl,
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
	populateDate,
	populateDateTime,
	populateDateMultiple,
} = require('./helpers');

let payload = null;
let submittedPage = null;
let browserContext = null;

test.describe('Basic form tests', () => {
	test.beforeAll(async ({ browser }) => {
		browserContext = await browser.newContext();
		submittedPage = await browserContext.newPage();

		await openUrl(submittedPage, 'basic');

		// INPUT.
		await populateInput(submittedPage, 'input-email', 'john.doe@example.com');
		await populateInput(submittedPage, 'input-regular', 'John Doe');
		await populateInput(submittedPage, 'input-url', 'https://eightshift.com/');
		await populateInput(submittedPage, 'input-number', '1234567890');
		await populateRange(submittedPage, 'input-range', 10);

		// SELECT.
		await populateSelect(submittedPage, 'country-single', 'Croatia');
		await populateSelectMultiple(submittedPage, 'country-multiple', ['Croatia', 'United States']);

		// DATE.
		await populateDate(submittedPage, 'date-single', '2022-04-30');
		await populateDateMultiple(submittedPage, 'date-multiple', ['2025-11-24', '2025-11-26']);
		await populateDateMultiple(submittedPage, 'date-range', ['2025-11-24', '2025-11-26']);
		await populateDateTime(submittedPage, 'date-time', '2022-04-30 15:30');

		// RATING.
		await populateRating(submittedPage, 'rating', 5);

		// PHONE.
		await populatePhone(submittedPage, 'phone', '385911234567');

		// RADIO.
		await populateRadio(submittedPage, 'radios', 'radio-2');

		// SELECT.
		await populateSelect(submittedPage, 'select-single', 'Option 2');
		await populateSelectMultiple(submittedPage, 'select-multiple', ['Option 2', 'Option 3']);

		// CHECKBOXES.
		await populateCheckbox(submittedPage, 'checkboxes', 'checkbox-2');

		// TEXTAREA.
		await populateTextarea(submittedPage, 'textarea', 'Hello, world!');

		// SUBMIT and get request data
		payload = await submitFormAction(submittedPage, SUBMIT_URL);
	});

	test.afterAll(async () => {
		if (browserContext) {
			await browserContext.close();
		}
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
		testFieldSimple(payload, 'input-range', '10', 'range', 'range', '');
	});

	test('should populate country single field', async () => {
		testFieldSimple(payload, 'country-single', ['Croatia'], 'country', 'country', '');
	});

	test('should populate country multiple field', async () => {
		testFieldSimple(payload, 'country-multiple', ['Croatia', 'United States'], 'country', 'country', '');
	});

	test('should populate date single field', async () => {
		testFieldSimple(payload, 'date-single', '2022-04-30', 'date', 'date', '');
	});

	test('should populate date multiple field', async () => {
		testFieldSimple(payload, 'date-multiple', '2025-11-24---2025-11-26', 'date', 'date', '');
	});

	test('should populate date range field', async () => {
		testFieldSimple(payload, 'date-range', '2025-11-24---2025-11-26', 'date', 'date', '');
	});

	test('should populate date time field', async () => {
		testFieldSimple(payload, 'date-time', '2022-04-30 15:30', 'dateTime', 'datetime-local', '');
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
		await testMessage(submittedPage, 'success');
	});
});

