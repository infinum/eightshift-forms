const { test, expect } = require('@playwright/test');
const {
	openUrl,
	getFieldLabel,
	getField,
} = require('./helpers');

const URL = 'field-country';

test.describe('Field country tests', () => {
	test('country-default', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'country-default';
		const label = await getFieldLabel(page, selector);
		const field = await getField(page, selector);

		await expect(field).toHaveClass(/es-field--country/);
		await expect(label).toBeVisible();
		await expect(label).toHaveText('country-default');
	});

	test('country-hide-label', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'country-hide-label';
		const label = await getFieldLabel(page, selector);

		await expect(label).not.toBeVisible();
	});

	test('country-placeholder-use-label', async ({ page }) => {
		await openUrl(page, URL);
		const selector = 'country-placeholder-use-label';
		const label = await getFieldLabel(page, selector);

		await expect(label).not.toBeVisible();
	});
});
