import React from 'react';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsOptions } from './../../../components/integrations/components/integrations-options';
import manifest from './../manifest.json';

export const MailerliteOptions = ({
	attributes,
	setAttributes,
	clientId,
	itemIdKey,
}) => {

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
		/>
	);
};
