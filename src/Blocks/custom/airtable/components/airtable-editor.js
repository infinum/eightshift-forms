import React from 'react';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';
import manifest from './../manifest.json';

export const AirtableEditor = ({
	attributes,
	setAttributes,
	itemIdKey,
	innerIdKey,
	clientId,
}) => {

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<IntegrationsEditor
				clientId={clientId}
				itemId={checkAttr(itemIdKey, attributes, manifest)}
				innerId={checkAttr(innerIdKey, attributes, manifest)}
				useInnerId={true}
				attributes={attributes}
				setAttributes={setAttributes}
				allowedBlocks={checkAttr('airtableAllowedBlocks', attributes, manifest)}
			/>
		</div>
	);
};
