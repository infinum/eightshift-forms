import React from 'react';
import { select } from '@wordpress/data';
import {
	STORE_NAME,
	checkAttr,
	props,
	getAttrKey,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const CheckboxesEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('checkboxes');

	const {
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		blockClientId,
	} = attributes;

	const checkboxesContent = checkAttr('checkboxesContent', attributes, manifest);
	const checkboxesName = checkAttr('checkboxesName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('checkboxesName', attributes, manifest), checkboxesName);

	const checkboxes = (
		<>
			{checkboxesContent}

			<MissingName value={checkboxesName} />

			{checkboxesName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: checkboxes,
					fieldIsRequired: checkAttr('checkboxesIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
