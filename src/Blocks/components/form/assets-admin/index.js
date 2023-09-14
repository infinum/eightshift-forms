/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { Form } from './../assets/form';
import manifest from './../manifest.json';
import adminListingManifest from './../../admin-listing/manifest.json';
import { setStateInitial } from '../assets/state/init';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		console.warn('Your project is missing global variable esFormsLocalization called from the enqueue script in the forms. Forms will work but they will not get the admin settings configuration.');
	}

	// Set initial state.
	setStateInitial();

	new Form().init();

	////////////////////////////////////////////////////////////////
	// Cache
	////////////////////////////////////////////////////////////////

	const selectorCache = `.${manifest.componentCacheJsClass}`;
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

	const selectorMigration = `.${manifest.componentMigrationJsClass}`;
	const elementsMigration = document.querySelectorAll(selectorMigration);

	if (elementsMigration.length) {
		import('./migration').then(({ Migration }) => {
			new Migration({
				selector: selectorMigration,
				outputSelector: `.${manifest.componentMigrationJsClass}-output`,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Transfer
	////////////////////////////////////////////////////////////////

	const selectorTransfer = `.${manifest.componentTransferJsClass}`;
	const elementsTransfer = document.querySelectorAll(selectorTransfer);

	if (elementsTransfer.length) {
		import('./transfer').then(({ Transfer }) => {
			new Transfer({
				selector: selectorTransfer,
				itemSelector: `.${manifest.componentTransferJsClass}-item`,
				uploadSelector: `.${manifest.componentTransferJsClass}-upload`,
				overrideExistingSelector: `.${manifest.componentTransferJsClass}-existing`,
				uploadConfirmMsg: esFormsLocalization.uploadConfirmMsg,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Test api
	////////////////////////////////////////////////////////////////

	const selectorTestApi = `.${manifest.componentTestApiJsClass}`;
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

	const selectorFilter = `.${adminListingManifest.componentJsFilterClass}`;
	const elementsFilter = document.querySelector(selectorFilter);

	if (elementsFilter) {
		import('./filter').then(({ Filter }) => {
			new Filter({
				filterSelector: selectorFilter,
				itemSelector: `.${adminListingManifest.componentJsItemClass}`,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Bullk
	////////////////////////////////////////////////////////////////

	const selectorBulk = `.${adminListingManifest.componentJsBulkClass}`;
	const elementsBulk = document.querySelector(selectorBulk);

	if (elementsBulk) {
		import('./bulk').then(({ Bulk }) => {
			new Bulk({
				selector: selectorBulk,
				itemsSelector: `${selectorBulk}-items`,
				itemSelector: `.${adminListingManifest.componentJsItemClass}`,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Locations
	////////////////////////////////////////////////////////////////

	const selectorLocations = `.${adminListingManifest.componentJsLocationsClass}`;
	const elementsLocations = document.querySelector(selectorLocations);

	if (elementsLocations) {
		import('./locations').then(({ Locations }) => {
			new Locations({
				selector: selectorLocations,
			}).init();
		});
	}

	////////////////////////////////////////////////////////////////
	// Manual import api
	////////////////////////////////////////////////////////////////

	const selectorManualImportApi = `.${manifest.componentManualImportApiJsClass}`;
	const elementsManualImportApi = document.querySelector(selectorManualImportApi);

	if (elementsManualImportApi) {
		import('./manual-import-api').then(({ ManualImportApi }) => {
			new ManualImportApi({
				selector: selectorManualImportApi,
				outputSelector: `.${manifest.componentManualImportApiJsClass}-output`,
				dataSelector: `.${manifest.componentManualImportApiJsClass}-data`,
			}).init();
		});
	}
});
