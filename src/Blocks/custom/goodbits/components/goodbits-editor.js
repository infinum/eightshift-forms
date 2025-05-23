import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';

export const GoodbitsEditor = ({ attributes, setAttributes, itemIdKey }) => {
	const manifest = select(STORE_NAME).getBlock('goodbits');

	return (
			<IntegrationsEditor
				itemId={checkAttr(itemIdKey, attributes, manifest)}
				attributes={attributes}
				setAttributes={setAttributes}
			/>
	);
};
