import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { RadiosEditor as RadiosEditorComponent } from '../../../components/radios/components/radios-editor';
import manifest from '../manifest.json';

export const RadiosEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	const radiosAllowedBlocks = checkAttr('radiosAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<RadiosEditorComponent
				{...props('radios', attributes, {
					setAttributes: setAttributes,
					radiosContent: <InnerBlocks
														allowedBlocks={(typeof radiosAllowedBlocks === 'undefined') || radiosAllowedBlocks}
														templateLock={false}
													/>
				})}
			/>
		</div>
	);
}
