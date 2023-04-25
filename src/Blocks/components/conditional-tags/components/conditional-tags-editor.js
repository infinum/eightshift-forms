import React from 'react';
import { __ } from '@wordpress/i18n';
import { Tooltip } from '@wordpress/components';
import { checkAttr, icons } from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const ConditionalTagsEditor = (attributes) => {
	const {
		isFormPicker = false,
	} = attributes;

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);

	if (!conditionalTagsUse) {
		return null;
	}

	return (
		<div className='es-position-absolute es-right-2 es-top-0 es-nested-color-pure-white es-bg-cool-gray-650 es-nested-w-6 es-nested-h-6 es-w-10 es-h-10 es-rounded-full es-has-enhanced-contrast-icon es-display-flex es-items-center es-content-center'>
			<Tooltip text={__('This field has conditional tags set', 'eightshift-forms')}>
				{isFormPicker ? icons.visibilityAlt : icons.conditionalVisibility}
			</Tooltip>
		</div>
	);
};
