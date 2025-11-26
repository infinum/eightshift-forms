const SUBMIT_URL = '/eightshift-forms/v1/submit/calculator';

/**
 * Get the URL for the test environment.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} path - The path to the test environment.
 */
const openUrl = async (page, path) => {
	await page.goto(`/tests/${path}`);
	await waitFormLoaded(page);
	await setTestEnvironment(page);
};

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
 * Wait for the form to be loaded.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 */
const waitFormLoaded = async (page) => {
	await page.waitForSelector('.es-form');
};

/**
 * Submit the form action and return the response data and form data.
 * This function ensures the form actually submits to the real API.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} urlPattern - Optional URL pattern to match (defaults to SUBMIT_URL).
 * @returns {Promise<Object>} Object with responseData and formData keys.
 */
const submitFormAction = async (page, urlPattern = SUBMIT_URL) => {
	const submitButton = page.locator('.es-field--submit .es-submit');
	await submitButton.waitFor({ state: 'visible' });

	// Set up waitForRequest and waitForResponse BEFORE submitting (order matters!)
	// Using waitForResponse ensures the API call completes and reaches the real API
	const requestPromise = page.waitForRequest(
		(request) => request.url().includes(urlPattern),
	);

	const responsePromise = page.waitForResponse(
		(response) => response.url().includes(urlPattern),
	);

	// Submit the form - this will trigger the actual API call
	await submitButton.click();

	// Wait for both request and response
	const request = await requestPromise;
	const response = await responsePromise;
	
	// Get the response body
	const responseData = await response.json();

	// Parse the form data from the request
	let formData = {};
	const postData = request.postData();

	if (postData) {
		// Parse multipart/form-data
		formData = await page.evaluate((data) => {
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
	}

	return {
		responseData,
		formData,
	};
};

/**
 * Populate the input field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the input field.
 * @param {string} value - The value to populate the input field with.
 */
const populateInput = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-input`).fill(value.toString());
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
const populateRange = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-input__range`).fill(value.toString());
};

/**
 * Populate the phone field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the phone field.
 * @param {string} value - The value to populate the phone field with.
 */
const populatePhone = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-phone`).fill(value.toString());
};

/**
 * Populate the textarea field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the textarea field.
 * @param {string} value - The value to populate the textarea field with.
 */
const populateTextarea = async (page, name, value) => {
	await page.locator(`.es-field[data-field-name="${name}"] .es-textarea`).fill(value.toString());
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
 * Populate the date field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the date single field.
 */
const populateDate = async (page, name, value) => {
	const date = new Date(value);
	const year = date.getFullYear();
	const month = date.getMonth();
	const day = date.getDate();

	await page.locator(`.es-field[data-field-name="${name}"] .input`).click();
	await page.locator(`.flatpickr-calendar.${name} .numInputWrapper .numInput`).fill(year.toString());
	await page.locator(`.flatpickr-calendar.${name} .numInputWrapper .numInput`).press('Enter');
	await page.locator(`.flatpickr-calendar.${name} select.flatpickr-monthDropdown-months`).selectOption(month.toString());
	await page.locator(`.flatpickr-calendar.${name} .dayContainer .flatpickr-day:not(.prevMonthDay):not(.nextMonthDay):has-text("${day}")`).click();
};

/**
 * Populate the date time field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the date single field.
 */
const populateDateTime = async (page, name, value) => {
	const date = new Date(value);
	const year = date.getFullYear().toString();
	const month = date.getMonth().toString();
	const day = date.getDate().toString();
	const hour = date.getHours().toString().padStart(2, '0');
	const minute = date.getMinutes().toString().padStart(2, '0');

	await page.locator(`.es-field[data-field-name="${name}"] .input`).click();
	await page.locator(`.flatpickr-calendar.${name} input.flatpickr-hour`).fill(hour);
	await page.locator(`.flatpickr-calendar.${name} input.flatpickr-minute`).fill(minute);
	await page.locator(`.flatpickr-calendar.${name} input.cur-year`).fill(year);
	await page.locator(`.flatpickr-calendar.${name} input.cur-year`).press('Enter');
	await page.locator(`.flatpickr-calendar.${name} select.flatpickr-monthDropdown-months`).selectOption(month);
	await page.locator(`.flatpickr-calendar.${name} .dayContainer .flatpickr-day:not(.prevMonthDay):not(.nextMonthDay):has-text("${day}")`).click();
	await page.locator(`.flatpickr-calendar.${name}`).press('Escape');
};

/**
 * Populate the date multiple field.
 *
 * @param {import('@playwright/test').Page} page - The page instance.
 * @param {string} name - The name of the date multiple field.
 * @param {string[]} values - The values to populate the date multiple field with.
 */
const populateDateMultiple = async (page, name, values) => {
	await page.locator(`.es-field[data-field-name="${name}"] .input`).click();

	let index = 0;

	for (const value of values) {
		if (index === 2) {
			continue;
		}

		const date = new Date(value);
		const year = date.getFullYear();
		const month = date.getMonth();
		const day = date.getDate();

		await page.locator(`.flatpickr-calendar.${name} .numInputWrapper .numInput`).fill(year.toString());
		await page.locator(`.flatpickr-calendar.${name} .numInputWrapper .numInput`).press('Enter');
		await page.locator(`.flatpickr-calendar.${name} select.flatpickr-monthDropdown-months`).selectOption(month.toString());
		await page.locator(`.flatpickr-calendar.${name} .dayContainer .flatpickr-day:not(.prevMonthDay):not(.nextMonthDay):has-text("${day}")`).click();

		index++;
	}

	await page.locator(`.flatpickr-calendar.${name}`).press('Escape');
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
	SUBMIT_URL,
	openUrl,
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
	populateDate,
	populateDateTime,
	populateDateMultiple,
	getNetworkRequest,
};

