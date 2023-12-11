import React from 'react';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Tooltip } from '@wordpress/components';
import { STORE_NAME, checkAttr, icons } from '@eightshift/frontend-libs/scripts';

export const ConditionalTagsEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('conditional-tags');

	const {
		isFormPicker = false,
		useCustom,
	} = attributes;

	const conditionalTagsUse = !useCustom ? checkAttr('conditionalTagsUse', attributes, manifest) : attributes?.conditionalTagsUse;

	if (!conditionalTagsUse) {
		return null;
	}

	return (
		<div className='es-position-absolute es-right-2 es-top-0 es-nested-color-pure-white es-bg-cool-gray-650 es-nested-w-5 es-nested-h-5 es-w-8 es-h-8 es-rounded-full es-has-enhanced-contrast-icon es-display-flex es-items-center es-content-center'>
			<Tooltip text={__('This field has conditional tags set', 'eightshift-forms')}>
				{isFormPicker ? icons.visibilityAlt : icons.conditionalVisibility}
			</Tooltip>
		</div>
	);
};
