/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { createBlock } from '@wordpress/blocks';
import { Button, Placeholder } from '@wordpress/components';
import { InnerBlocks } from '@wordpress/block-editor';
import { dispatch, useSelect } from '@wordpress/data';
import {
	checkAttr,
	BlockIcon
} from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const FormSelectorEditor = ({ attributes, clientId }) => {
	const {
		forms,
	} = manifest;

	const formSelectorAllowedBlocks = checkAttr('formSelectorAllowedBlocks', attributes, manifest);

	// Internal state to toggle buttons.
	const [hasInnerBlocks, setHasInnerBlocks] = useState(false);

	// Check if form selector has inner blocks.
	const hasInnerBlocksCheck = useSelect((select) => {
		const { innerBlocks } = select('core/block-editor').getBlock(clientId);

		return innerBlocks.length;
	});

	// If parent block has inner blocks set internal state.
	useEffect(() => {
		setHasInnerBlocks(hasInnerBlocksCheck);
	}, [hasInnerBlocksCheck]);

	// Create block from manifest.
	const createFormType = (slug) => {
		const {
			blockName,
			attributes = {},
			innerBlocks = [],
		} = forms.filter((form) => form.slug === slug)[0];

		// Build all inner blocks.
		const inner = innerBlocks.map((item) => createBlock(item[0], item[1] ?? {}, item[2] ?? []));

		// Build top level block.
		const block = createBlock(blockName, attributes, inner);

		// Insert built block in DOM.
		dispatch('core/block-editor').insertBlock(block, 0, clientId);

		// Set internal state to hide buttons.
		setHasInnerBlocks(!hasInnerBlocks);
	};

	// Additional content filter.
	let additionalContent = '';

	if (typeof esFormsLocalization !== 'undefined' && (esFormsLocalization?.formSelectorBlockAdditionalContent) !== '') {
		additionalContent = esFormsLocalization.formSelectorBlockAdditionalContent;
	}

	return (
		<>
			{!hasInnerBlocks &&
				<Placeholder
					icon={<BlockIcon iconName='esf-form' />}
					label={__('Eightshift Forms', 'productive')}
					instructions={__('Select a form type below to start.', 'productive')}
					className={attributes.blockClass}
				>
					<div className='esf-form-type-picker-group'>
						{forms.map((form, index) => {
							const {
								label,
								slug,
								icon
							} = form;

							return (
								<Button
									className='esf-form-type-picker-button'
									icon={<BlockIcon iconName={icon} />}
									key={index}
									isTertiary
									onClick={() => createFormType(slug)}
								>
									{sprintf(__('%s form', 'eightshift-forms'), label)}
								</Button>
							);
						})}
					</div>
				</Placeholder>
			}

			<div dangerouslySetInnerHTML={{ __html: additionalContent }} />

			<InnerBlocks
				allowedBlocks={(typeof formSelectorAllowedBlocks === 'undefined') || formSelectorAllowedBlocks}
				templateLock={hasInnerBlocks}
			/>
		</>
	);
};
