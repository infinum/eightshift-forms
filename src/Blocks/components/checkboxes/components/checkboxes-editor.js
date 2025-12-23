import React from 'react';
import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconConditionals, StatusIconMissingName } from './../../utils';
import manifest from '../manifest.json';

export const CheckboxesEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const checkboxesContent = checkAttr('checkboxesContent', attributes, manifest);
	const checkboxesName = checkAttr('checkboxesName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('checkboxesName', attributes, manifest), checkboxesName);

	const checkboxes = <div className='esf-fieldset'>{checkboxesContent}</div>;

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: checkboxes,
					fieldIsRequired: checkAttr('checkboxesIsRequired', attributes, manifest),
				})}
				statusSlog={[
					!checkboxesName && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</>
	);
};
