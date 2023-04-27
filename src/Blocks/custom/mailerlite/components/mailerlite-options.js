import React from 'react';
import { PanelBody } from '@wordpress/components';
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
		<PanelBody title={title}>
			<IntegrationsOptions
				block={blockName}
				setAttributes={setAttributes}
				clientId={clientId}
				itemId={checkAttr(itemIdKey, attributes, manifest)}
				itemIdKey={itemIdKey}
			/>
		</PanelBody>
	);
};
