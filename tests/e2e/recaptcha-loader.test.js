const { test, expect } = require('@playwright/test');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');

const loader = readFileSync(resolve(__dirname, '../../src/Blocks/components/form/assets/recaptcha-loader.js'), 'utf8');

test('retries the bootstrap when its secondary script never becomes ready', async ({ page }) => {
	let requests = 0;
	await page.route('https://captcha.test/**', async (route) => {
		const path = new URL(route.request().url()).pathname;

		if (path === '/') {
			await route.fulfill({ contentType: 'text/html', body: '<html></html>' });
		} else if (path === '/loader.js') {
			await route.fulfill({ contentType: 'text/javascript', body: loader });
		} else {
			requests++;
			const body = requests === 1 ? 'window.grecaptcha = { ready() {} };' : 'window.grecaptcha = { ready(cb) { cb(); }, execute() { return "token"; } };';
			await route.fulfill({ contentType: 'text/javascript', body });
		}
	});
	await page.clock.install();
	await page.goto('https://captcha.test/');
	await page.evaluate(async () => {
		const { loadRecaptcha } = await import('/loader.js');
		const state = { getStateCaptchaIsEnterprise: () => false, getStateCaptchaScriptUrl: () => '/bootstrap.js' };
		window.loadCaptcha = () => loadRecaptcha(state);
		window.firstAttempt = window.loadCaptcha().catch((error) => error.message);
	});
	await expect.poll(() => page.evaluate(() => Boolean(window.grecaptcha))).toBe(true);
	await page.clock.fastForward(15001);
	await expect(page.evaluate(() => window.firstAttempt)).resolves.toContain('timed out');
	await expect(page.evaluate(async () => (await window.loadCaptcha()).execute())).resolves.toBe('token');
	expect(requests).toBe(2);
});

test('rapid submits while reCAPTCHA is loading only submit once', async ({ page }) => {
	const formSource = readFileSync(resolve(__dirname, '../../src/Blocks/components/form/assets/form.js'), 'utf8');
	const modules = {
		'/form.js': formSource.replace(/^import .* from '@eightshift\/.*';$/gm, ''),
		'/recaptcha-loader': loader,
		'/state-init': 'export const StateEnum = {}, prefix = "esForms", setStateFormInitial = null, setStateWindow = () => { window.esForms = {}; }, removeStateForm = null;',
	};
	await page.route('https://captcha.test/**', (route) => {
		const path = new URL(route.request().url()).pathname;

		return path === '/' ? route.fulfill({ contentType: 'text/html', body: '<form><button>Submit</button></form>' }) : route.fulfill({ contentType: 'text/javascript', body: modules[path] });
	});
	await page.goto('https://captcha.test/');
	const result = await page.evaluate(async () => {
		const { Form } = await import('/form.js');
		let ready;
		window.grecaptcha = { ready: (callback) => (ready = callback), execute: async () => 'token' };
		const tokens = [];
		const form = new Form({
			getState: () => ({
				getStateCaptchaIsUsed: () => true,
				getStateCaptchaType: () => 'google',
				getStateCaptchaIsEnterprise: () => false,
				getStateCaptchaSubmitAction: () => 'submit',
				getStateCaptchaSiteKey: () => 'test-key',
			}),
			getEnrichment() {},
			getConditionalTags() {},
			getSteps() {},
			getGeolocation() {},
		});
		let token;
		form.setFormDataCaptcha = (data) => (token = data.token);
		form.formSubmit = async () => tokens.push(token);
		const element = document.querySelector('form');
		const submissions = [];
		element.addEventListener('submit', (event) => {
			event.preventDefault();
			submissions.push(form.runFormCaptcha('1'));
		});
		element.requestSubmit();
		element.requestSubmit();
		ready();
		await Promise.all(submissions);
		const afterRapidSubmit = [...tokens];
		element.requestSubmit();
		await submissions.at(-1);

		return { afterRapidSubmit, afterNextSubmit: tokens };
	});
	expect(result).toEqual({ afterRapidSubmit: ['token'], afterNextSubmit: ['token', 'token'] });
});
