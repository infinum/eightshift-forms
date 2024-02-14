/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { Form } from './../assets/form';
import { setStateInitial } from '../assets/state-init';
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

	// Load state.
	const state = utils.getState();

	// Init form.
	new Form(utils).init();

	////////////////////////////////////////////////////////////////
	// Cache
	////////////////////////////////////////////////////////////////

	const selectorCache = state.getStateSelectorAdmin('cacheDelete', true);

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

	const selectorMigration = state.getStateSelectorAdmin('migration', true);

	if (document.querySelectorAll(selectorMigration).length) {
		import('./migration').then(({ Migration }) => {
			new Migration({
				utils: utils,
				selector: selectorMigration,
				outputSelector: state.getStateSelectorAdmin('migrationOutput', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Transfer
	////////////////////////////////////////////////////////////////

	const selectorTransfer = state.getStateSelectorAdmin('transfer', true);

	if (document.querySelectorAll(selectorTransfer).length) {
		import('./transfer').then(({ Transfer }) => {
			new Transfer({
				utils: utils,
				selector: selectorTransfer,
				itemSelector: state.getStateSelectorAdmin('transferItem', true),
				uploadSelector: state.getStateSelectorAdmin('transferUpload', true),
				overrideExistingSelector: state.getStateSelectorAdmin('transferExisting', true),
				uploadConfirmMsg: esFormsLocalization.uploadConfirmMsg,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Test api
	////////////////////////////////////////////////////////////////

	const selectorTestApi = state.getStateSelectorAdmin('testApi', true);

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

	const selectorFilter = state.getStateSelectorAdmin('listingFilter', true);

	if (document.querySelector(selectorFilter)) {
		import('./filter').then(({ Filter }) => {
			new Filter({
				utils: utils,
				filterSelector: selectorFilter,
				itemSelector: state.getStateSelectorAdmin('listingItem', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Bulk
	////////////////////////////////////////////////////////////////

	const selectorBulk = state.getStateSelectorAdmin('listingBulk', true);

	if (document.querySelector(selectorBulk)) {
		import('./bulk').then(({ Bulk }) => {
			new Bulk({
				utils: utils,
				selector: selectorBulk,
				itemsSelector: state.getStateSelectorAdmin('listingBulkItems', true),
				itemSelector: state.getStateSelectorAdmin('listingItem', true),
				selectAllSelector: state.getStateSelectorAdmin('listingSelectAll', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Export
	////////////////////////////////////////////////////////////////

	const selectorExport = state.getStateSelectorAdmin('listingExport', true);

	if (document.querySelector(selectorExport)) {
		import('./export').then(({ Export }) => {
			new Export({
				utils: utils,
				selector: selectorExport,
				itemsSelector: state.getStateSelectorAdmin('listingBulkItems', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Locations
	////////////////////////////////////////////////////////////////

	const selectorLocations = state.getStateSelectorAdmin('listingLocations', true);

	if (document.querySelector(selectorLocations)) {
		import('./locations').then(({ Locations }) => {
			new Locations({
				utils: utils,
				selector: selectorLocations,
				itemSelector: state.getStateSelectorAdmin('listingItem', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Manual import api
	////////////////////////////////////////////////////////////////

	const selectorManualImportApi = state.getStateSelectorAdmin('manualImportApi', true);

	if (document.querySelector(selectorManualImportApi)) {
		import('./manual-import-api').then(({ ManualImportApi }) => {
			new ManualImportApi({
				utils: utils,
				selector: selectorManualImportApi,
				outputSelector: state.getStateSelectorAdmin('manualImportApiOutput', true),
				dataSelector: state.getStateSelectorAdmin('manualImportApiData', true),
				importErrorMsg: esFormsLocalization.importErrorMsg,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Tabs
	////////////////////////////////////////////////////////////////

	const selectorTabs = state.getStateSelectorAdmin('tabs', true);

	if (document.querySelectorAll(selectorTabs).length) {
		import('./tabs').then(({ Tabs }) => {
			new Tabs({
				tabsSelector: selectorTabs,
				tabSelector: state.getStateSelectorAdmin('tabsItem', true),
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Debug Encrypt
	////////////////////////////////////////////////////////////////

	const selectorDebugEncrypt = state.getStateSelectorAdmin('debugEncryptionRun', true);

	if (document.querySelectorAll(selectorDebugEncrypt).length) {
		import('./debug-encrypt').then(({ DebugEncrypt }) => {
			new DebugEncrypt({
				utils: utils,
				selector: selectorDebugEncrypt,
				outputSelector: state.getStateSelectorAdmin('debugEncryptionOutput', true),
				typeSelector: state.getStateSelectorAdmin('debugEncryptionType', true),
				dataSelector: state.getStateSelectorAdmin('debugEncryption', true),
			}).init();
		});
	}
});
