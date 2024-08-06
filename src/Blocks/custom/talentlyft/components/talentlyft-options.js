import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsOptions } from '../../../components/integrations/components/integrations-options';

export const TalentlyftOptions = ({
	attributes,
	setAttributes,
	clientId,
	itemIdKey,
}) => {
	const manifest = select(STORE_NAME).getBlock('talentlyft');

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
