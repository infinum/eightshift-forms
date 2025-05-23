import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from '../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const DynamicEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('dynamic');

	const { blockClientId } = attributes;

	const dynamicName = checkAttr('dynamicName', attributes, manifest);
	const dynamicCustomLabel = checkAttr('dynamicCustomLabel', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('dynamicName', attributes, manifest), dynamicName);

	const dynamic = (
		<div>
			{dynamicCustomLabel}

			<MissingName value={dynamicName} />

			{dynamicName && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
		</div>
	);

	return (
		<FieldEditor
			{...props('field', attributes, {
				fieldContent: dynamic,
				fieldIsRequired: checkAttr('dynamicIsRequired', attributes, manifest),
				fieldHidden: checkAttr('dynamicIsDeactivated', attributes, manifest),
			})}
		/>
	);
};
