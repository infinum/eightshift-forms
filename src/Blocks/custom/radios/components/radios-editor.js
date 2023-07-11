import React from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { RadiosEditor as RadiosEditorComponent } from '../../../components/radios/components/radios-editor';

export const RadiosEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('radios');

	const {
		template,
	} = manifest;

	const radiosAllowedBlocks = checkAttr('radiosAllowedBlocks', attributes, manifest);

	return (
		<RadiosEditorComponent
			{...props('radios', attributes, {
				setAttributes,
				clientId,
				radiosContent:
					<InnerBlocks
						allowedBlocks={(typeof radiosAllowedBlocks === 'undefined') || radiosAllowedBlocks}
						template={template}
						renderAppender={() => <BlockInserter clientId={clientId} small />}
					/>
			})}
		/>
	);
};
