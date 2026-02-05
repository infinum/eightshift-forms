import React from 'react';
import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconMissingName, StatusIconConditionals } from './../../utils';
import manifest from '../manifest.json';

export const SelectEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const selectContent = checkAttr('selectContent', attributes, manifest);
	const selectName = checkAttr('selectName', attributes, manifest);
	const selectIsDisabled = checkAttr('selectIsDisabled', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('selectName', attributes, manifest), selectName);

	const selectComponent = <div className='esf-fieldset'>{selectContent}</div>;

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: selectComponent,
					fieldIsRequired: checkAttr('selectIsRequired', attributes, manifest),
				})}
				statusSlog={[
					!selectName && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</>
	);
};
