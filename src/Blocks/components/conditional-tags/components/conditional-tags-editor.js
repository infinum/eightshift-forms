import React from 'react';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { icons } from '@eightshift/ui-components/icons';
import { Tooltip } from '@eightshift/ui-components';

export const ConditionalTagsEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('conditional-tags');

	const { isFormPicker = false, useCustom } = attributes;

	const conditionalTagsUse = !useCustom ? checkAttr('conditionalTagsUse', attributes, manifest) : attributes?.conditionalTagsUse;

	if (!conditionalTagsUse) {
		return null;
	}

	return (
		<div>
			<Tooltip text={__('This field has conditional tags set', 'eightshift-forms')}>{isFormPicker ? icons.visibilityAlt : icons.conditionalVisibility}</Tooltip>
		</div>
	);
};
