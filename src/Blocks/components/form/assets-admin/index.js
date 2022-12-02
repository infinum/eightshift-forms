/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';
import { Utils } from './../assets/utilities';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		throw 'Your project is missing the global "esFormsLocalization" variable called from the enqueue script.';
	}

	const {
		componentJsClass,
		componentCacheJsClass,
		componentMigrationJsClass,
		componentTransferJsClass,
	} = manifest;

	const selector = `.${componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length) {
		import('./../assets/form').then(({ Form }) => {
			const form = new Form(new Utils({
				formSubmitRestApiUrl: esFormsLocalization.formSettingsSubmitRestApiUrl,
				formIsAdmin: true,
			}));

			form.init();
		});
	}

	const selectorCache = `.${componentCacheJsClass}`;
	const elementsCache = document.querySelectorAll(selectorCache);

	if (elementsCache.length) {
		import('./cache').then(({ Cache }) => {
			const cache = new Cache({
				...new Utils(),
				selector: selectorCache,
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
				...new Utils(),
				selector: selectorMigration,
				migrationRestUrl: esFormsLocalization.migrationRestUrl,
			});

			migration.init();
		});
	}

	const selectorTransfer = `.${componentTransferJsClass}`;
	const elementsTransfer = document.querySelectorAll(selectorTransfer);

	if (elementsTransfer.length) {
		import('./transfer').then(({ Transfer }) => {
			const transfer = new Transfer({
				...new Utils(),
				selector: selectorTransfer,
				itemSelector: `.${componentTransferJsClass}-item`,
				uploadSelector: `.${componentTransferJsClass}-upload`,
				overrideExistingSelector: `.${componentTransferJsClass}-existing`,
				transferRestUrl: esFormsLocalization.transferRestUrl,
				uploadConfirmMsg: esFormsLocalization.uploadConfirmMsg,
			});

			transfer.init();
		});
	}
});
