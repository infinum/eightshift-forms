import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconConditionals, StatusIconMissingName } from './../../utils';
import manifest from '../manifest.json';

export const PhoneEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);
	const phoneName = checkAttr('phoneName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('phoneName', attributes, manifest), phoneName);

	const phone = (
		<div className='esf:flex! esf:flex-row! esf:gap-10!'>
			<select
				className='esf:max-w-100! esf-input'
				disabled
			>
				<option value=''>{__('Prefix', 'eightshift-forms')}</option>
			</select>
			<input
				className='esf-input esf:flex-1!'
				value={phoneValue}
				placeholder={phonePlaceholder}
				type={'tel'}
				disabled
			/>
		</div>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: phone,
					fieldIsRequired: checkAttr('phoneIsRequired', attributes, manifest),
				})}
				statusSlog={[
					!phoneName && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</>
	);
};
