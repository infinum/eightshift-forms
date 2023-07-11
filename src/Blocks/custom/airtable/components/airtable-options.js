import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsOptions } from './../../../components/integrations/components/integrations-options';

export const AirtableOptions = ({
	attributes,
	setAttributes,
	clientId,
	itemIdKey,
	innerIdKey,
}) => {
	const manifest = select(STORE_NAME).getBlock('airtable');

	const {
		title,
		blockName,
	} = manifest;

	return (
		<IntegrationsOptions
			title={title}
			block={blockName}
			attributes={attributes}
			setAttributes={setAttributes}
			clientId={clientId}
			itemId={checkAttr(itemIdKey, attributes, manifest)}
			itemIdKey={itemIdKey}
			innerId={checkAttr(innerIdKey, attributes, manifest)}
			innerIdKey={innerIdKey}
		/>
	);
};
