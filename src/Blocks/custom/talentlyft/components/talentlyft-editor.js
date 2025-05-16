import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsEditor } from '../../../components/integrations/components/integrations-editor';

export const TalentlyftEditor = ({ attributes, setAttributes, itemIdKey }) => {
	const manifest = select(STORE_NAME).getBlock('talentlyft');

	const { blockClass } = attributes;

	return (
		<div className={blockClass}>
			<IntegrationsEditor
				itemId={checkAttr(itemIdKey, attributes, manifest)}
				attributes={attributes}
				setAttributes={setAttributes}
				allowedBlocks={checkAttr('talentlyftAllowedBlocks', attributes, manifest)}
			/>
		</div>
	);
};
