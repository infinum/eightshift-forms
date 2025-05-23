import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const PhoneEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('phone');

	const { blockClientId } = attributes;

	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);
	const phoneName = checkAttr('phoneName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('phoneName', attributes, manifest), phoneName);

	const phone = (
		<>
			<select />
			<input
				value={phoneValue}
				placeholder={phonePlaceholder}
				type={'tel'}
				readOnly
			/>

			<MissingName
				value={phoneName}
				isEditor={true}
			/>

			<ConditionalTagsEditor {...props('conditionalTags', attributes)} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: phone,
					fieldIsRequired: checkAttr('phoneIsRequired', attributes, manifest),
				})}
			/>
		</>
	);
};
