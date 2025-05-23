import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import { MissingName, VisibilityHidden, preventSaveOnMissingProps } from './../../utils';

export const SelectOptionEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('select-option');

	const { blockClientId } = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionAsPlaceholder = checkAttr('selectOptionAsPlaceholder', attributes, manifest);
	const selectOptionIsHidden = checkAttr('selectOptionIsHidden', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('selectOptionValue', attributes, manifest), selectOptionValue);

	return (
		<div>
			<VisibilityHidden
				value={selectOptionIsHidden}
				label={__('Option', 'eightshift-forms')}
			/>

			{selectOptionLabel ? selectOptionLabel : __('Enter option label in sidebar.', 'eightshift-forms')}

			<MissingName
				value={selectOptionValue}
				asPlaceholder={selectOptionAsPlaceholder}
			/>

			{selectOptionValue && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
		</div>
	);
};
