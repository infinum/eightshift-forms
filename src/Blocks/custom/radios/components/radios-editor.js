import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { RadiosEditor as RadiosEditorComponent } from '../../../components/radios/components/radios-editor';
import manifest from '../manifest.json';

export const RadiosEditor = ({ attributes, setAttributes, clientId }) => {
	const {
		template,
	} = manifest;

	const radiosAllowedBlocks = checkAttr('radiosAllowedBlocks', attributes, manifest);

	return (
		<RadiosEditorComponent
			{...props('radios', attributes, {
				setAttributes,
				clientId,
				radiosContent: <InnerBlocks
												allowedBlocks={(typeof radiosAllowedBlocks === 'undefined') || radiosAllowedBlocks}
												template={template}
											/>
			})}
		/>
	);
};
