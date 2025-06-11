import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';

export const MailchimpEditor = ({ attributes, setAttributes, itemIdKey }) => {
	const manifest = select(STORE_NAME).getBlock('mailchimp');

	return (
		<IntegrationsEditor
			itemId={checkAttr(itemIdKey, attributes, manifest)}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
