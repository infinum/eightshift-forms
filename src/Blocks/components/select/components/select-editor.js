import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const SelectEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('select');

	const { blockClientId } = attributes;

	const selectContent = checkAttr('selectContent', attributes, manifest);
	const selectName = checkAttr('selectName', attributes, manifest);
	const selectIsDisabled = checkAttr('selectIsDisabled', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('selectName', attributes, manifest), selectName);

	const selectComponent = (
		<>
			<div>
				{selectContent}

				<MissingName value={selectName} />

				{selectName && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
			</div>
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: selectComponent,
					fieldIsRequired: checkAttr('selectIsRequired', attributes, manifest),
				})}
			/>
		</>
	);
};
