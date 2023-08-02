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

export const RadiosEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('radios');

	const {
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		blockClientId,
	} = attributes;

	const radiosContent = checkAttr('radiosContent', attributes, manifest);
	const radiosName = checkAttr('radiosName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('radiosName', attributes, manifest), radiosName);

	const radios = (
		<>
			{radiosContent}

			<MissingName value={radiosName} />

			{radiosName &&
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
					fieldContent: radios,
					fieldIsRequired: checkAttr('radiosIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
