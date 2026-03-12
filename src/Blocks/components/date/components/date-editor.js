import React from 'react';
import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconConditionals, StatusIconMissingName } from './../../utils';
import manifest from '../manifest.json';

export const DateEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const dateValue = checkAttr('dateValue', attributes, manifest);
	const datePlaceholder = checkAttr('datePlaceholder', attributes, manifest);
	const dateType = checkAttr('dateType', attributes, manifest);
	const dateName = checkAttr('dateName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('dateName', attributes, manifest), dateName);

	const date = (
		<>
			<input
				className='esf-input'
				value={dateValue}
				placeholder={datePlaceholder}
				type={dateType}
				disabled
			/>
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: date,
					fieldIsRequired: checkAttr('dateIsRequired', attributes, manifest),
				})}
				statusSlog={[
					!dateName && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</>
	);
};
