import React from 'react';
import manifest from './../manifest.json';
import { IntegrationsInternalOptions } from '../../../components/integrations/components/integrations-internal-options';

export const JiraOptions = ({
	attributes,
	setAttributes,
	clientId,
}) => {
	
	const {
		title,
	} = manifest;

	return (
		<IntegrationsInternalOptions
			title={title}
			clientId={clientId}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
