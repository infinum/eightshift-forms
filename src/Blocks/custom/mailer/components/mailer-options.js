import React from 'react';
import manifest from './../manifest.json';
import { IntegrationsInternalOptions } from '../../../components/integrations/components/integrations-internal-options';

export const MailerOptions = ({
	attributes,
	setAttributes,
}) => {
	
	const {
		title,
	} = manifest;

	return (
		<IntegrationsInternalOptions
			title={title}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
