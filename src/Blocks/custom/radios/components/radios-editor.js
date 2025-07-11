import React, { useEffect, useState } from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { RadiosEditor as RadiosEditorComponent } from '../../../components/radios/components/radios-editor';
import globalManifest from '../../../manifest.json';

export const RadiosEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('radios');

	const { template } = manifest;

	const [parentBlock, setParentBlock] = useState([]);

	useEffect(() => {
		const parentBlockIds = select('core/block-editor').getBlockParents(clientId);
		const parents = select('core/block-editor').getBlocksByClientId(parentBlockIds);

		setParentBlock(parents.filter((parent) => globalManifest.allowedBlocksList.integrationsBuilder.includes(parent.name)));
	}, [clientId]);

	return (
		<RadiosEditorComponent
			{...props('radios', attributes, {
				setAttributes,
				clientId,
				radiosContent: (
					<InnerBlocks
						allowedBlocks={['eightshift-forms/radio', 'eightshift-forms/input']}
						templateLock={parentBlock.length > 0 ? 'insert' : false}
						template={template}
						renderAppender={() => <BlockInserter clientId={clientId} />}
					/>
				),
			})}
		/>
	);
};
