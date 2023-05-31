/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		console.warn('Your project is missing global variable esFormsLocalization called from the enqueue script in the forms. Forms will work but they will not get the admin settings configuration.');
	}

	const {
		componentJsClass,
		componentCacheJsClass,
		componentMigrationJsClass,
		componentTransferJsClass,
		componentTestApiJsClass,
	} = manifest;

	const selector = `.${componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length) {
		import('./../assets/form').then(({ Form }) => {
			const form = new Form({
				formIsAdmin: true,
			});

			form.init();
		});
	}

	// const selectorCache = `.${componentCacheJsClass}`;
	// const elementsCache = document.querySelectorAll(selectorCache);

	// if (elementsCache.length) {
	// 	import('./cache').then(({ Cache }) => {
	// 		const cache = new Cache({
	// 			utils: new Data(),
	// 			selector: selectorCache,
	// 			clearCacheRestUrl: `${esFormsLocalization.restPrefix}${esFormsLocalization.restRoutes.cacheClear}`,
	// 		});

	// 		cache.init();
	// 	});
	// }

	// const selectorMigration = `.${componentMigrationJsClass}`;
	// const elementsMigration = document.querySelectorAll(selectorMigration);

	// if (elementsMigration.length) {
	// 	import('./migration').then(({ Migration }) => {
	// 		const migration = new Migration({
	// 			utils: new Data(),
	// 			selector: selectorMigration,
	// 			outputSelector: `.${componentMigrationJsClass}-output`,
	// 			migrationRestUrl: `${esFormsLocalization.restPrefix}${esFormsLocalization.restRoutes.migration}`,
	// 		});

	// 		migration.init();
	// 	});
	// }

	// const selectorTransfer = `.${componentTransferJsClass}`;
	// const elementsTransfer = document.querySelectorAll(selectorTransfer);

	// if (elementsTransfer.length) {
	// 	import('./transfer').then(({ Transfer }) => {
	// 		const transfer = new Transfer({
	// 			utils: new Data(),
	// 			selector: selectorTransfer,
	// 			itemSelector: `.${componentTransferJsClass}-item`,
	// 			uploadSelector: `.${componentTransferJsClass}-upload`,
	// 			overrideExistingSelector: `.${componentTransferJsClass}-existing`,
	// 			transferRestUrl: `${esFormsLocalization.restPrefix}${esFormsLocalization.restRoutes.transform}`,
	// 			uploadConfirmMsg: esFormsLocalization.uploadConfirmMsg,
	// 		});

	// 		transfer.init();
	// 	});
	// }

	// const selectorTestApi = `.${componentTestApiJsClass}`;
	// const elementsTestApi = document.querySelectorAll(selectorTestApi);

	// if (elementsTestApi.length) {
	// 	import('./test-api').then(({ TestApi }) => {
	// 		const testApi = new TestApi({
	// 			utils: new Data(),
	// 			selector: selectorTestApi,
	// 			testApiRestUrl: `${esFormsLocalization.restPrefix}/${esFormsLocalization.restRoutes.testApi}`,
	// 		});

	// 		testApi.init();
	// 	});
	// }
});
