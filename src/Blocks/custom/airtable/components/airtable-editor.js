import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';

export const AirtableEditor = ({ attributes, setAttributes, itemIdKey, innerIdKey, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('airtable');

	return (
		<div>
			<IntegrationsEditor
				clientId={clientId}
				itemId={checkAttr(itemIdKey, attributes, manifest)}
				innerId={checkAttr(innerIdKey, attributes, manifest)}
				useInnerId={true}
				attributes={attributes}
				setAttributes={setAttributes}
			/>
		</div>
	);
};
