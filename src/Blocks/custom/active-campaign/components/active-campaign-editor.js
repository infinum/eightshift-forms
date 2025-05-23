import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';

export const ActiveCampaignEditor = ({ attributes, setAttributes, itemIdKey }) => {
	const manifest = select(STORE_NAME).getBlock('active-campaign');

	return (
		<div>
			<IntegrationsEditor
				itemId={checkAttr(itemIdKey, attributes, manifest)}
				attributes={attributes}
				setAttributes={setAttributes}
			/>
		</div>
	);
};
