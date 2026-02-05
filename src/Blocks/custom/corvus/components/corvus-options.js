import React from 'react';
import { IntegrationsInternalOptions } from '../../../components/integrations/components/integrations-internal-options';
import manifest from '../manifest.json';

export const CorvusOptions = ({ attributes, setAttributes, clientId }) => {
	return (
		<IntegrationsInternalOptions
			title={manifest.title}
			clientId={clientId}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
