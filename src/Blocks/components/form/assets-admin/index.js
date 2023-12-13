/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { Form } from './../assets/form';
import { getStateSelectorAdmin, setStateInitial } from '../assets/state/init';
import { Utils } from '../assets/utils';

domReady(() => {
	// Global variable must be set for everything to work.
	if (typeof esFormsLocalization === 'undefined') {
		throw Error('Your project is missing global variable "esFormsLocalization" called from the enqueue script in the forms.');
	}

	// Bailout if no forms pages.
	if (esFormsLocalization.length === 0) {
		return;
	}

	// Set initial state.
	setStateInitial();

	// Load state helpers.
	const utils = new Utils();

	// Init form.
	new Form(utils).init();

	////////////////////////////////////////////////////////////////
	// Cache
	////////////////////////////////////////////////////////////////

	const selectorCache = getStateSelectorAdmin('cacheDelete', true);

	if (document.querySelectorAll(selectorCache).length) {
		import('./cache').then(({ Cache }) => {
			new Cache({
				utils: utils,
				selector: selectorCache,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Migration
	////////////////////////////////////////////////////////////////

	const selectorMigration = getStateSelectorAdmin('migration', true);

	if (document.querySelectorAll(selectorMigration).length) {
		import('./migration').then(({ Migration }) => {
			new Migration({
				utils: utils,
				selector: selectorMigration,
				outputSelector: getStateSelectorAdmin('migrationOutput', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Transfer
	////////////////////////////////////////////////////////////////

	const selectorTransfer = getStateSelectorAdmin('transfer', true);

	if (document.querySelectorAll(selectorTransfer).length) {
		import('./transfer').then(({ Transfer }) => {
			new Transfer({
				utils: utils,
				selector: selectorTransfer,
				itemSelector: getStateSelectorAdmin('transferItem', true),
				uploadSelector: getStateSelectorAdmin('transferUpload', true),
				overrideExistingSelector: getStateSelectorAdmin('transferExisting', true),
				uploadConfirmMsg: esFormsLocalization.uploadConfirmMsg,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Test api
	////////////////////////////////////////////////////////////////

	const selectorTestApi = getStateSelectorAdmin('testApi', true);

	if (document.querySelectorAll(selectorTestApi).length) {
		import('./test-api').then(({ TestApi }) => {
			new TestApi({
				utils: utils,
				selector: selectorTestApi,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Filter
	////////////////////////////////////////////////////////////////

	const selectorFilter = getStateSelectorAdmin('listingFilter', true);

	if (document.querySelector(selectorFilter)) {
		import('./filter').then(({ Filter }) => {
			new Filter({
				utils: utils,
				filterSelector: selectorFilter,
				itemSelector: getStateSelectorAdmin('listingItem', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Bulk
	////////////////////////////////////////////////////////////////

	const selectorBulk = getStateSelectorAdmin('listingBulk', true);

	if (document.querySelector(selectorBulk)) {
		import('./bulk').then(({ Bulk }) => {
			new Bulk({
				utils: utils,
				selector: selectorBulk,
				itemsSelector: getStateSelectorAdmin('listingBulkItems', true),
				itemSelector: getStateSelectorAdmin('listingItem', true),
				selectAllSelector: getStateSelectorAdmin('listingSelectAll', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Export
	////////////////////////////////////////////////////////////////

	const selectorExport = getStateSelectorAdmin('listingExport', true);

	if (document.querySelector(selectorExport)) {
		import('./export').then(({ Export }) => {
			new Export({
				utils: utils,
				selector: selectorExport,
				itemsSelector: getStateSelectorAdmin('listingBulkItems', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Locations
	////////////////////////////////////////////////////////////////

	const selectorLocations = getStateSelectorAdmin('listingLocations', true);

	if (document.querySelector(selectorLocations)) {
		import('./locations').then(({ Locations }) => {
			new Locations({
				utils: utils,
				selector: selectorLocations,
				itemSelector: getStateSelectorAdmin('listingItem', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Manual import api
	////////////////////////////////////////////////////////////////

	const selectorManualImportApi = getStateSelectorAdmin('manualImportApi', true);

	if (document.querySelector(selectorManualImportApi)) {
		import('./manual-import-api').then(({ ManualImportApi }) => {
			new ManualImportApi({
				utils: utils,
				selector: selectorManualImportApi,
				outputSelector: getStateSelectorAdmin('manualImportApiOutput', true),
				dataSelector: getStateSelectorAdmin('manualImportApiData', true),
			}).init();
		});
	}


	////////////////////////////////////////////////////////////////
	// Tabs
	////////////////////////////////////////////////////////////////

	const selectorTabs = getStateSelectorAdmin('tabs', true);

	if (document.querySelectorAll(selectorTabs).length) {
		import('./tabs').then(({ Tabs }) => {
			new Tabs({
				tabsSelector: selectorTabs,
				tabSelector: getStateSelectorAdmin('tabsItem', true),
			}).init();
		});
	}
});
