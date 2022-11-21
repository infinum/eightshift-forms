/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		throw 'Your project is missing the global "esFormsLocalization" variable called from the enqueue script.';
	}

	const {
		componentJsClass,
		componentCacheJsClass,
		componentMigrationJsClass,
	} = manifest;

	const selector = `.${componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length) {
		import('./../assets/form').then(({ Form }) => {
			const form = new Form({
				formSelector: selector,
				formSubmitRestApiUrl: esFormsLocalization.formSettingsSubmitRestApiUrl,
				formIsAdmin: true,
				customFormParams: esFormsLocalization.customFormParams,
				customFormDataAttributes: esFormsLocalization.customFormDataAttributes,
			});

			form.init();
		});
	}

	const selectorCache = `.${componentCacheJsClass}`;
	const elementsCache = document.querySelectorAll(selectorCache);

	if (elementsCache.length) {
		import('./cache').then(({ Cache }) => {
			const cache = new Cache({
				selector: selectorCache,
				formSelector: selector,
				clearCacheRestUrl: esFormsLocalization.clearCacheRestUrl,
			});

			cache.init();
		});
	}

	const selectorMigration = `.${componentMigrationJsClass}`;
	const elementsMigration = document.querySelectorAll(selectorMigration);

	if (elementsMigration.length) {
		import('./migration').then(({ Migration }) => {
			const migration = new Migration({
				selector: selectorMigration,
				formSelector: selector,
				migrationRestUrl: esFormsLocalization.migrationRestUrl,
			});

			migration.init();
		});
	}
});
