const { By } = require('selenium-webdriver');

/**
 * Test a simple field from the form payload.
 *
 * @param {Object} payload - The form payload object.
 * @param {string} name - The field name.
 * @param {string} value - The expected value of the field.
 * @param {string} type - The field type.
 * @param {string} typeCustom - The custom type of the field.
 * @param {string} innerName - The inner name of the field.
 */
const testFieldSimple = (payload, name, value, type, typeCustom, innerName) => {
	expect(payload).toHaveProperty(name);

	const data = JSON.parse(payload[name]);

	let expectedType = 'string';

	if (['select', 'country'].includes(type)) {
		expectedType = 'array';
	}

	expect(data).toHaveProperty('name');
	expect(data.name).toBe(name);
	expect(data).toHaveProperty('type');
	expect(data.type).toBe(type);
	expect(data).toHaveProperty('typeCustom');
	expect(data.typeCustom).toBe(typeCustom);
	expect(data).toHaveProperty('innerName');
	expect(data.innerName).toBe(innerName);

	expect(data).toHaveProperty('value');

	if (expectedType === 'array') {
		expect(data.value).toEqual(value);
	} else {
		expect(data.value).toBe(value);
	}
};

/**
 * Test multiple choice fields (checkboxes, radios, rating) from the form payload.
 *
 * @param {Object} payload - The form payload object.
 * @param {string} name - The field name (e.g., 'rating', 'radios', 'checkboxes').
 * @param {string|number|Array<string|number>} expectedValue - The expected selected value(s). Array for multiple selections, single value for single selection.
 * @param {string} type - The field type (e.g., 'rating', 'radio', 'checkbox').
 * @param {string} typeCustom - The custom type (e.g., 'rating', 'radio', 'checkbox').
 * @param {string[]} initialValues - The initial values of the fields.
 */
const testFieldMultiple = (payload, name, expectedValue, type, typeCustom, initialValues = []) => {
	// Find all fields with the pattern name[index]
	const fieldKeys = Object.keys(payload).filter((key) => key.startsWith(`${name}[`));

	if (fieldKeys.length === 0) {
		expect(fieldKeys).toHaveLength(1);
	}

	const selectedValues = [];
	const isMultipleSelection = Array.isArray(expectedValue);
	const expectedValues = isMultipleSelection ? expectedValue.map(String) : [String(expectedValue)];

	// Parse and validate each field
	fieldKeys.forEach((key, index) => {
		if (!(key in payload)) {
			expect(key).toBeIn(payload);
		}

		const data = JSON.parse(payload[key]);

		if (!data) {
			expect(data).toBeDefined();
		}

		// Validate structure
		expect(data).toHaveProperty('name');
		expect(data.name).toBe(name);
		expect(data).toHaveProperty('type');
		expect(data.type).toBe(type);
		expect(data).toHaveProperty('typeCustom');
		expect(data.typeCustom).toBe(typeCustom);
		expect(data).toHaveProperty('innerName');
		expect(data.innerName).toBe(initialValues[index]);
		expect(data).toHaveProperty('value');

		// Collect selected values (non-empty)
		if (data.value && data.value !== '') {
			selectedValues.push(String(data.value));
		}
	});

	// Verify that at least one value was selected
	if (selectedValues.length === 0) {
		expect(selectedValues).toHaveLength(1);
	}

	// For single selection fields (radio, rating), verify only one is selected
	if (!isMultipleSelection && selectedValues.length > 1) {
		expect(selectedValues).toHaveLength(1);
	}

	// Verify the selected value(s) match the expected value(s)
	if (isMultipleSelection) {
		// For multiple selections, check that arrays match (order doesn't matter for checkboxes)
		expect(selectedValues.sort()).toEqual(expectedValues.sort());
	} else {
		// For single selection, check exact match
		expect(selectedValues[0]).toBe(expectedValues[0]);
	}
};

/**
 * Test the message from the form.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} status - The status of the message.
 */
const testMessage = async (driver, status) => {
	const message = await driver.findElement(By.css(`.es-global-msg[data-status="${status}"] div span`)).getText();
	expect(message).toBe(message);
};

/**
 * Test the validation of a field.
 *
 * @param {import('selenium-webdriver').WebDriver} driver - The driver instance.
 * @param {string} name - The name of the field.
 */
const testFieldValidationMessage = async (driver, name, expectedMessage) => {
	const errorMessage = await driver.findElement(By.css(`.es-field[data-field-name="${name}"] .es-error`)).getText();
	expect(errorMessage).toBe(expectedMessage);
};

module.exports = {
	testFieldSimple,
	testFieldMultiple,
	testMessage,
	testFieldValidationMessage,
};
