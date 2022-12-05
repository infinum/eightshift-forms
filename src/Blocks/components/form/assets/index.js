/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';
import { Utils } from './utilities';

if (typeof esFormsLocalization === 'undefined') {
	throw 'Your project is missing global variable esFormsLocalization called from the enqueue script in the forms.';
}

const utils = new Utils();

window['esForms'] = {
	utils: utils,
};

// Load add data required for the forms to work.
function initAll() {
	import('./form').then(({ Form }) => {
		const form = new Form(utils);

		// Run forms.
		form.init();

		// Populate window object with the rest of the functions.
		window['esForms'] = {
			...window['esForms'],
			form,
		};
	});
}

// You can disable auto init from the admin.
const disableAutoInit = Boolean(esFormsLocalization.formDisableAutoInit);

// Load normal forms on dom ready event otherwise use manual trigger from the window object.
if (!disableAutoInit) {
	domReady(() => {
		const {
			componentJsClass,
		} = manifest;

		const elements = document.querySelectorAll(`.${componentJsClass}`);

		if (elements.length) {
			initAll();
		}
	});
} else {
	// Load initAll method in window object for manual trigger.
	window['esForms'] = {
		...window['esForms'],
		initAll: () => {
			initAll();
		},
	};
}
