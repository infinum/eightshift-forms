import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { IntegrationsInternalOptions } from './../../../components/integrations/components/integrations-internal-options';

export const PardotOptions = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('pardot');

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
