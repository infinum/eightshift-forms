import React from 'react';
import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';
import manifest from '../manifest.json';

export const AirtableEditor = ({ attributes, setAttributes, itemIdKey, innerIdKey, clientId }) => {
	return (
		<IntegrationsEditor
			clientId={clientId}
			itemId={checkAttr(itemIdKey, attributes, manifest)}
			innerId={checkAttr(innerIdKey, attributes, manifest)}
			useInnerId={true}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
