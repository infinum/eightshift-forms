import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';

export const AirtableEditor = ({
	attributes,
	setAttributes,
	itemIdKey,
	innerIdKey,
	clientId,
}) => {
	const manifest = select(STORE_NAME).getBlock('airtable');

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
