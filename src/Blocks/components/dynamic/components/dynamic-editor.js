import React from 'react';
import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconMissingName, StatusIconConditionals } from '../../utils';
import manifest from '../manifest.json';

export const DynamicEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const dynamicName = checkAttr('dynamicName', attributes, manifest);
	const dynamicCustomLabel = checkAttr('dynamicCustomLabel', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('dynamicName', attributes, manifest), dynamicName);

	const dynamic = <div>{dynamicCustomLabel}</div>;

	return (
		<FieldEditor
			{...props('field', attributes, {
				fieldContent: dynamic,
				fieldIsRequired: checkAttr('dynamicIsRequired', attributes, manifest),
				fieldHidden: checkAttr('dynamicIsDeactivated', attributes, manifest),
			})}
			statusSlog={[
				!dynamicName && <StatusIconMissingName />,
				attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
			]}
		/>
	);
};
