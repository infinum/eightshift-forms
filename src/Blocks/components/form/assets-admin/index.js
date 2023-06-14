/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { Form } from './../assets/form';
import {
	componentCacheJsClass,
	componentMigrationJsClass,
	componentTransferJsClass,
	componentTestApiJsClass,
	componentManualImportApiJsClass,
} from './../manifest.json';
import {
	componentJsFilterClass,
	componentJsItemClass,
	componentJsSyncClass,
} from './../../admin-listing/manifest.json';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		console.warn('Your project is missing global variable esFormsLocalization called from the enqueue script in the forms. Forms will work but they will not get the admin settings configuration.');
	}

	new Form().init();

	////////////////////////////////////////////////////////////////
	// Cache
	////////////////////////////////////////////////////////////////

	const selectorCache = `.${componentCacheJsClass}`;
	const elementsCache = document.querySelectorAll(selectorCache);

	if (elementsCache.length) {
		import('./cache').then(({ Cache }) => {
			new Cache({
				selector: selectorCache,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Migration
	////////////////////////////////////////////////////////////////

	const selectorMigration = `.${componentMigrationJsClass}`;
	const elementsMigration = document.querySelectorAll(selectorMigration);

	if (elementsMigration.length) {
		import('./migration').then(({ Migration }) => {
			new Migration({
				selector: selectorMigration,
				outputSelector: `.${componentMigrationJsClass}-output`,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Transfer
	////////////////////////////////////////////////////////////////

	const selectorTransfer = `.${componentTransferJsClass}`;
	const elementsTransfer = document.querySelectorAll(selectorTransfer);

	if (elementsTransfer.length) {
		import('./transfer').then(({ Transfer }) => {
			new Transfer({
				selector: selectorTransfer,
				itemSelector: `.${componentTransferJsClass}-item`,
				uploadSelector: `.${componentTransferJsClass}-upload`,
				overrideExistingSelector: `.${componentTransferJsClass}-existing`,
				uploadConfirmMsg: esFormsLocalization.uploadConfirmMsg,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Test api
	////////////////////////////////////////////////////////////////

	const selectorTestApi = `.${componentTestApiJsClass}`;
	const elementsTestApi = document.querySelectorAll(selectorTestApi);

	if (elementsTestApi.length) {
		import('./test-api').then(({ TestApi }) => {
			new TestApi({
				selector: selectorTestApi,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Filter
	////////////////////////////////////////////////////////////////

	const selectorFilter = `.${componentJsFilterClass}`;
	const elementsFilter = document.querySelector(selectorFilter);

	if (elementsFilter) {
		import('./filter').then(({ Filter }) => {
			const filter = new Filter({
				filterSelector: selectorFilter,
				itemSelector: `.${componentJsItemClass}`,
			});

			filter.init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Sync
	////////////////////////////////////////////////////////////////

	const selectorSync = `.${componentJsSyncClass}`;
	const elementsSync = document.querySelector(selectorSync);

	if (elementsSync) {
		import('./sync').then(({ Sync }) => {
			const sync = new Sync({
				selector: selectorSync,
			});

			sync.init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Sync
	////////////////////////////////////////////////////////////////

	const selectorManualImportApi = `.${componentManualImportApiJsClass}`;
	const elementsManualImportApi = document.querySelector(selectorManualImportApi);

	if (elementsManualImportApi) {
		import('./manual-import-api').then(({ ManualImportApi }) => {
			const manualImportApi = new ManualImportApi({
				selector: selectorManualImportApi,
				outputSelector: `.${componentManualImportApiJsClass}-output`,
				dataSelector: `.${componentManualImportApiJsClass}-data`,
			});

			manualImportApi.init();
		});
	}
});
