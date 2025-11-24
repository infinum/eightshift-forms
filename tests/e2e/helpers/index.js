const TIMEOUT = 10000;
const SUBMIT_URL = '/eightshift-forms/v1/submit/calculator';


/**
 * Set the test environment to remove unnecessary elements and styles.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 */
const setTestEnvironment = async (page) => {
	const className = process.env.ES_CLASS || 'es-forms-tests';
	await page.evaluate((className) => {
		document.body.classList.add(className);
	}, className);
};

/**
 * Get the URL for the test environment.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} path - The path to the test environment.
 */
const openUrl = async (page, path) => {
	const baseUrl = process.env.ES_URL || 'http://localhost:3000';
	await page.goto(`${baseUrl}/tests/${path}`);
};

/**
 * Wait for the form to be loaded.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 */
const waitFormLoaded = async (page) => {
	await page.waitForSelector('.es-form', { timeout: TIMEOUT });
};

/**
 * Submit the form action and return the request data.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} urlPattern - Optional URL pattern to match (defaults to SUBMIT_URL).
 * @param {number} timeout - Timeout in milliseconds to wait for the request.
 * @returns {Promise<Object>} The request data with jsonValue.
 */
const submitFormAction = async (page, urlPattern = SUBMIT_URL, timeout = 10000) => {
	// Set up waitForRequest BEFORE submitting (order matters!)
	const requestPromise = page.waitForRequest(
		(request) => request.url().includes(urlPattern),
		{ timeout }
	);

	// Submit the form
	await page.locator('.es-field--submit button[type="submit"]').click();

	// Wait for the request
	const request = await requestPromise;
	const postData = request.postData();

	const output = request.response();
	// const outputText = await output.text();

	// console.log(outputText);

	// Parse multipart/form-data using browser's native parsing via evaluate
	const jsonValue = await page.evaluate((data) => {
		const result = {};
		const boundaryMatch = data.match(/^------([^\r\n]+)/);

		if (boundaryMatch) {
			const boundary = `------${boundaryMatch[1]}`;
			const parts = data.split(boundary);

			for (const part of parts) {
				if (!part.trim() || part.trim() === '--') {
					continue;
				}

				const nameMatch = part.match(/Content-Disposition:\s*form-data;\s*name="([^"]+)"/);

				if (!nameMatch) {
					continue;
				}

				const fieldName = nameMatch[1];
				const bodyStart = part.indexOf('\r\n\r\n');

				if (bodyStart === -1) {
					continue;
				}

				const jsonValue = part.substring(bodyStart + 4).trim().replace(/\r\n$/, '');

				try {
					result[fieldName] = JSON.parse(jsonValue);
				} catch {
					result[fieldName] = jsonValue;
				}
			}
		}

		return result;
	}, postData);

	return jsonValue;
};

/**
 * Populate the input field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the input field.
 * @param {string} value - The value to populate the input field with.
 */
const populateInput = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-input`).fill(value);
};

/**
 * Populate the range field.
 *
 * Unable to select specific values, only plus 1 and minus 1 are selected.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the range field.
 * @param {string} type - The type of the range field.
 */
const populateRange = async (page, name, type = 'plus') => {
	const rangeInput = page.locator(`.es-field[data-field-name="${name}"] .es-input__range`);

	if (type === 'plus') {
		await rangeInput.press('ArrowRight');
	}

	if (type === 'minus') {
		await rangeInput.press('ArrowLeft');
	}
};

/**
 * Populate the phone field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the phone field.
 * @param {string} value - The value to populate the phone field with.
 */
const populatePhone = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-phone`).fill(value);
};

/**
 * Populate the textarea field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the textarea field.
 * @param {string} value - The value to populate the textarea field with.
 */
const populateTextarea = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-textarea`).fill(value);
};

/**
 * Populate the select field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the select field.
 * @param {string} value - The value to populate the select field with.
 */
const populateSelect = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .choices__inner`).click();
	await page.locator(`.es-field[data-field-name="${name}"] .choices__input--cloned`).click();
	await page.locator(`.es-field[data-field-name="${name}"] .choices__input--cloned`).fill(value);
	await page.locator(`.es-field[data-field-name="${name}"] .choices__input--cloned`).press('Enter');
	await page.locator(`.es-field[data-field-name="${name}"] .choices__input--cloned`).press('Escape');
};

/**
 * Populate the select multiple field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the select multiple field.
 * @param {string[]} values - The values to populate the select multiple field with.
 */
const populateSelectMultiple = async (page, name, values) => {
	for (const value of values) {
		await populateSelect(page, name, value);
	}
};

/**
 * Populate the checkbox field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the checkbox field.
 * @param {string} value - The value to populate the checkbox field with.
 */
const populateCheckbox = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-checkbox[data-field-name="${value}"] label`).click();
};

/**
 * Populate the radio field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the radio field.
 * @param {string} value - The value to populate the radio field with.
 */
const populateRadio = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-radio[data-field-name="${value}"] label`).click();
};

/**
 * Populate the rating field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the rating field.
 * @param {number} value - The value to populate the rating field with.
 */
const populateRating = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-rating label:nth-of-type(${value})`).click();
};

/**
 * Populate the date single field.
 *
 * Unable to select specific dates, only today's date is selected.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the date single field.
 * @param {boolean} closeOnSelect - Whether to close the date picker on select.
 */
const populateDateSingle = async (page, name, closeOnSelect = false) => {
	await page.locator(`.es-field[data-field-name="${name}"] .input`).click();
	await page.locator(`.flatpickr-calendar.${name} .flatpickr-day.today`).click();

	if (closeOnSelect) {
		await page.locator(`.flatpickr-calendar.${name}`).press('Escape');
	}
};

/**
 * Populate the date multiple field.
 *
 * Unable to select specific dates, only today's date and the next day is selected.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the date multiple field.
 * @param {boolean} closeOnSelect - Whether to close the date picker on select.
 */
const populateDateMultiple = async (page, name, closeOnSelect = false) => {
	await page.locator(`.es-field[data-field-name="${name}"] .input`).click();
	await page.waitForSelector(`.flatpickr-calendar.${name} .flatpickr-day.today`, { timeout: TIMEOUT });
	await page.locator(`.flatpickr-calendar.${name} .flatpickr-day.today`).click();
	await page.locator(`.flatpickr-calendar.${name} .flatpickr-day.today + .flatpickr-day`).click();

	if (closeOnSelect) {
		await page.locator(`.flatpickr-calendar.${name}`).press('Escape');
	}
};

/**
 * Wait for network request and get its data.
 * Uses Playwright's built-in network interception.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} urlPattern - The URL pattern to match (e.g., '/eightshift-forms/v1/submit/calculator').
 * @param {number} timeout - Timeout in milliseconds to wait for the request.
 * @returns {Promise<Object>} The request data including method, URL, and postData.
 */
const getNetworkRequest = async (page, urlPattern, timeout = 10000) => {
	const response = await page.waitForResponse(
		(response) => response.url().includes(urlPattern),
		{ timeout }
	);

	const request = response.request();
	let postData = null;

	if (request.postData()) {
		try {
			// Try to parse as JSON first
			postData = JSON.parse(request.postData());
		} catch {
			// If not JSON, try to parse as FormData
			const formData = new URLSearchParams(request.postData());
			const obj = {};

			for (const [key, value] of formData.entries()) {
				obj[key] = value;
			}
			postData = obj;
		}
	}

	return {
		method: request.method(),
		url: request.url(),
		postData: postData ? JSON.stringify(postData) : null,
	};
};

module.exports = {
	TIMEOUT,
	SUBMIT_URL,
	setTestEnvironment,
	openUrl,
	waitFormLoaded,
	submitFormAction,
	populateInput,
	populateTextarea,
	populateCheckbox,
	populateRadio,
	populateRating,
	populatePhone,
	populateRange,
	populateSelect,
	populateSelectMultiple,
	populateDateSingle,
	populateDateMultiple,
	getNetworkRequest,
};

