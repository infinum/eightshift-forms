const { By, Key, until } = require('selenium-webdriver');

const TIMEOUT = 10000;
const SUBMIT_URL = '/eightshift-forms/v1/submit/calculator';

/**
 * Set the test environment to remove unnecessary elements and styles.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 */
const setTestEnvironment = async (driver) => {
	const className = process.env.ES_CLASS || 'es-forms-tests';
	await driver.executeScript(`document.body.classList.add("${className}");`);
};

/**
 * Get the URL for the test environment.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} path - The path to the test environment.
 */
const openUrl = async (driver, path) => {
	await driver.get(`${process.env.ES_URL}/tests/${path}`);
};

/**
 * Wait for the form to be loaded.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 */
const waitFormLoaded = async (driver) => {
	await driver.wait(until.elementLocated(By.css('.es-form')), 10000);
};

/**
 * Submit the form action.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 */
const submitFormAction = async (driver) => {
	await driver.findElement(By.css('.es-field--submit button[type="submit"]')).click();

	// To make sure the form is submitted and the message is displayed.
	await driver.sleep(1000);
};

/**
 * Populate the input field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the input field.
 * @param {string} value - The value to populate the input field with.
 */
const populateInput = async (driver, name, value) => {
	await driver.findElement(By.css(`.es-field[data-field-name="${name}"] .es-input`)).sendKeys(value);
};

/**
 * Populate the range field.
 *
 * Unable to select specific values, only plus 1 and minus 1 are selected.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the range field.
 * @param {string} type - The type of the range field.
 */
const populateRange = async (driver, name, type = 'plus') => {
	if (type === 'plus') {
		await driver.findElement(By.css(`.es-field[data-field-name="${name}"] .es-input__range`)).sendKeys(Key.ARROW_RIGHT);
	}

	if (type === 'minus') {
		await driver.findElement(By.css(`.es-field[data-field-name="${name}"] .es-input__range`)).sendKeys(Key.ARROW_LEFT);
	}
};

/**
 * Populate the phone field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the phone field.
 * @param {string} value - The value to populate the phone field with.
 */
const populatePhone = async (driver, name, value) => {
	await driver.findElement(By.css(`.es-field[data-field-name="${name}"] .es-phone`)).sendKeys(value);
};

/**
 * Populate the textarea field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the textarea field.
 * @param {string} value - The value to populate the textarea field with.
 */
const populateTextarea = async (driver, name, value) => {
	await driver.findElement(By.css(`.es-field[data-field-name="${name}"] .es-textarea`)).sendKeys(value);
};

/**
 * Populate the select field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the select field.
 * @param {string} value - The value to populate the select field with.
 */
const populateSelect = async (driver, name, value) => {
	await driver
		.wait(until.elementLocated(By.css(`.es-field[data-field-name="${name}"] .choices__input--cloned`)), 10000)
		.then(async () => {
			await driver.findElement(By.css(`.es-field[data-field-name="${name}"] .choices`)).click();
			await driver.sleep(100);
			await driver
				.findElement(By.css(`.es-field[data-field-name="${name}"] .choices__input--cloned`))
				.sendKeys(value, Key.ENTER, Key.ESCAPE);
		});
};

/**
 * Populate the select multiple field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the select multiple field.
 * @param {string[]} values - The values to populate the select multiple field with.
 */
const populateSelectMultiple = async (driver, name, values) => {
	for (const value of values) {
		await populateSelect(driver, name, value);
	}
};

/**
 * Populate the checkbox field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the checkbox field.
 * @param {string} value - The value to populate the checkbox field with.
 */
const populateCheckbox = async (driver, name, value) => {
	await driver
		.findElement(By.css(`.es-field[data-field-name="${name}"] .es-checkbox[data-field-name="${value}"] label`))
		.click();
};

/**
 * Populate the radio field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the radio field.
 * @param {string} value - The value to populate the radio field with.
 */
const populateRadio = async (driver, name, value) => {
	await driver
		.findElement(By.css(`.es-field[data-field-name="${name}"] .es-radio[data-field-name="${value}"] label`))
		.click();
};

/**
 * Populate the rating field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the rating field.
 * @param {number} value - The value to populate the rating field with.
 */
const populateRating = async (driver, name, value) => {
	await driver
		.findElement(By.css(`.es-field[data-field-name="${name}"] .es-rating label:nth-of-type(${value})`))
		.click();
};

/**
 * Populate the date single field.
 *
 * Unable to select specific dates, only today's date is selected.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the date single field.
 * @param {boolean} closeOnSelect - Whether to close the date picker on select.
 */
const populateDateSingle = async (driver, name, closeOnSelect = false) => {
	await driver
		.findElement(By.css(`.es-field[data-field-name="${name}"] .input`))
		.click()
		.then(async () => {
			await driver
				.wait(until.elementLocated(By.css(`.flatpickr-calendar.${name} .flatpickr-day.today`)), 10000)
				.then(async () => {
					await driver.findElement(By.css(`.flatpickr-calendar.${name} .flatpickr-day.today`)).click();

					if (closeOnSelect) {
						await driver.findElement(By.css(`.flatpickr-calendar.${name}`)).sendKeys(Key.ESCAPE);
					}
				});
		});
};

/**
 * Populate the date multiple field.
 *
 * Unable to select specific dates, only today's date and the next day is selected.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the date multiple field.
 * @param {boolean} closeOnSelect - Whether to close the date picker on select.
 */
const populateDateMultiple = async (driver, name, closeOnSelect = false) => {
	await driver
		.findElement(By.css(`.es-field[data-field-name="${name}"] .input`))
		.click()
		.then(async () => {
			await driver
				.wait(until.elementLocated(By.css(`.flatpickr-calendar.${name} .flatpickr-day.today`)), 10000)
				.then(async () => {
					await driver.findElement(By.css(`.flatpickr-calendar.${name} .flatpickr-day.today`)).click();
					await driver.findElement(By.css(`.flatpickr-calendar.${name} .flatpickr-day.today + .flatpickr-day`)).click();

					if (closeOnSelect) {
						await driver.findElement(By.css(`.flatpickr-calendar.${name}`)).sendKeys(Key.ESCAPE);
					}
				});
		});
};

/**
 * Set up network request interception using JavaScript injection.
 * This must be called BEFORE the form is submitted.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} urlPattern - The URL pattern to match (e.g., '/eightshift-forms/v1/submit/calculator').
 * @returns {Promise<void>}
 */
const setupNetworkInterception = async (driver, urlPattern) => {
	await driver.executeScript(`
		window.__capturedRequests = [];
		
		const originalFetch = window.fetch;
		window.fetch = function(url, options = {}) {
			if (typeof url === 'string' && url.includes('${urlPattern}')) {
				const formData = options.body instanceof FormData ? options.body : null;
				const obj = {};
				if (formData) {
					for (const [key, value] of formData.entries()) obj[key] = value;
				}
				window.__capturedRequests.push({
					method: options.method || 'POST',
					url: url,
					body: JSON.stringify(obj),
				});
			}
			return originalFetch.apply(this, arguments);
		};
	`);
};

/**
 * Get network request data for a specific URL.
 * Requires setupNetworkInterception to be called first.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} urlPattern - The URL pattern to match (e.g., '/eightshift-forms/v1/submit/calculator').
 * @param {number} timeout - Timeout in milliseconds to wait for the request.
 * @returns {Promise<Object>} The request data including method, URL, and postData.
 */
const getNetworkRequest = async (driver, urlPattern, timeout = 10000) => {
	const startTime = Date.now();

	while (Date.now() - startTime < timeout) {
		const requests = await driver.executeScript('return window.__capturedRequests || [];');

		if (requests.length > 0) {
			return {
				method: requests[0].method,
				url: requests[0].url,
				postData: requests[0].body,
			};
		}

		await driver.sleep(100);
	}

	throw new Error(`Network request to ${urlPattern} not found within ${timeout}ms`);
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
	setupNetworkInterception,
	getNetworkRequest,
};
