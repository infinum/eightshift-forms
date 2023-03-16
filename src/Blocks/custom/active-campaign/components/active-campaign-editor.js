import React from 'react';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';
import manifest from './../manifest.json';

export const ActiveCampaignEditor = ({
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
				allowedBlocks={checkAttr('activeCampaignAllowedBlocks', attributes, manifest)}
			/>
		</div>
	);
};
