import React from 'react';
import { select } from '@wordpress/data';
import { IntegrationsInternalOptions } from '../../../components/integrations/components/integrations-internal-options';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts';

export const NationbuilderOptions = ({
	attributes,
	setAttributes,
	clientId,
}) => {
	const manifest = select(STORE_NAME).getBlock('nationbuilder');

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
