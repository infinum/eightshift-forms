import React from 'react';
import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';
import manifest from '../manifest.json';

export const GoodbitsEditor = ({ attributes, setAttributes, itemIdKey }) => {
	return (
		<IntegrationsEditor
			itemId={checkAttr(itemIdKey, attributes, manifest)}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
