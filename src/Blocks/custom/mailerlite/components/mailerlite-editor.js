import React from 'react';
import { select } from '@wordpress/data';
import { STORE_NAME, checkAttr } from '@eightshift/frontend-libs/scripts';
import { IntegrationsEditor } from './../../../components/integrations/components/integrations-editor';

export const MailerliteEditor = ({
	attributes,
	setAttributes,
	itemIdKey,
}) => {
	const manifest = select(STORE_NAME).getBlock('mailerlite');

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<IntegrationsEditor
				itemId={checkAttr(itemIdKey, attributes, manifest)}
				attributes={attributes}
				setAttributes={setAttributes}
			/>
		</div>
	);
};
