/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';
import { Utils } from './../../form/assets/utilities';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		throw 'Your project is missing the global "esFormsLocalization" variable called from the enqueue script.';
	}

	const {
		componentJsFilterClass,
		componentJsItemClass,
		componentJsSyncClass,
	} = manifest;

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

	const selectorSync = `.${componentJsSyncClass}`;
	const elementsSync = document.querySelector(selectorSync);

	if (elementsSync) {
		import('./sync').then(({ Sync }) => {
			const sync = new Sync({
				utils: new Utils(),
				selector: selectorSync,
				syncRestUrl: `${esFormsLocalization.restPrefix}${esFormsLocalization.restRoutes.syncDirect}`,
			});

			sync.init();
		});
	}

});
