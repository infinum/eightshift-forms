import React, { useEffect, useState } from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { CheckboxesEditor as CheckboxesEditorComponent } from '../../../components/checkboxes/components/checkboxes-editor';
import globalManifest from '../../../manifest.json';

export const CheckboxesEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('checkboxes');

	const { template } = manifest;

	const { blockClass } = attributes;

	const [parentBlock, setParentBlock] = useState([]);

	useEffect(() => {
		const parentBlockIds = select('core/block-editor').getBlockParents(clientId);
		const parents = select('core/block-editor').getBlocksByClientId(parentBlockIds);

		setParentBlock(parents.filter((parent) => globalManifest.allowedBlocksList.integrationsBuilder.includes(parent.name)));
	}, [clientId]);

	return (
		<CheckboxesEditorComponent
			{...props('checkboxes', attributes, {
				setAttributes,
				blockClass,
				clientId,
				checkboxesContent: (
					<InnerBlocks
						allowedBlocks={['eightshift-forms/checkbox', 'eightshift-forms/input']}
						templateLock={parentBlock.length > 0 ? 'insert' : false}
						template={template}
						renderAppender={() => <BlockInserter clientId={clientId} />}
					/>
				),
			})}
		/>
	);
};
