import React, { useEffect, useState } from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { SelectEditor as SelectEditorComponent } from '../../../components/select/components/select-editor';
import globalManifest from '../../../manifest.json';

export const SelectEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('select');

	const { template } = manifest;

	const [parentBlock, setParentBlock] = useState([]);

	useEffect(() => {
		const parentBlockIds = select('core/block-editor').getBlockParents(clientId);
		const parents = select('core/block-editor').getBlocksByClientId(parentBlockIds);

		setParentBlock(parents.filter((parent) => globalManifest.allowedBlocksList.integrationsBuilder.includes(parent.name)));
	}, [clientId]);

	return (
		<SelectEditorComponent
			{...props('select', attributes, {
				setAttributes,
				clientId,
				selectContent: (
					<InnerBlocks
						allowedBlocks={['eightshift-forms/select-option']}
						templateLock={parentBlock.length > 0 ? 'insert' : false}
						template={template}
						renderAppender={() => <BlockInserter clientId={clientId} />}
					/>
				),
			})}
		/>
	);
};
