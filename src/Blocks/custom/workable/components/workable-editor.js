import React from 'react';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';
import manifest from './../manifest.json';

export const WorkableEditor = ({
	attributes,
	setAttributes,
	itemIdKey,
}) => {

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<IntegrationsEditor
				itemId={checkAttr(itemIdKey, attributes, manifest)}
				attributes={attributes}
				setAttributes={setAttributes}
				allowedBlocks={checkAttr('workableAllowedBlocks', attributes, manifest)}
			/>
		</div>
	);
};
