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
		<div className={'es:flex es:items-center es:gap-2'}>
			<div className={'es:text-sm es:bg-secondary-100 es:p-2 es:border es:border-secondary-300 es:text-secondary-400'}>Prefix</div>
			<input
				value={phoneValue}
				placeholder={phonePlaceholder}
				type={'tel'}
				readOnly
				className={'es:w-full es:p-2 es:border es:border-secondary-300 es:bg-white es:text-sm'}
			/>

			<MissingName
				value={phoneName}
				isEditor={true}
			/>

			<ConditionalTagsEditor {...props('conditionalTags', attributes)} />
		</div>
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
