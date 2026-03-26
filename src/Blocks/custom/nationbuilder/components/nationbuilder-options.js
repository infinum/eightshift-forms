import React from 'react';
import { IntegrationsInternalOptions } from '../../../components/integrations/components/integrations-internal-options';
import manifest from '../manifest.json';

export const NationbuilderOptions = ({ attributes, setAttributes, clientId }) => {
	const { title } = manifest;

	return (
		<IntegrationsInternalOptions
			title={title}
			clientId={clientId}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
