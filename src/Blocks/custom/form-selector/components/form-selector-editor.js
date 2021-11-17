import React, { useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { createBlock } from '@wordpress/blocks';
import { Button } from '@wordpress/components';
import { InnerBlocks } from '@wordpress/block-editor';
import { dispatch, useSelect } from '@wordpress/data';
import {
	checkAttr,
	icons,
	IconLabel
} from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const FormSelectorEditor = ({ attributes, clientId }) => {
	const {
		forms,
	} = manifest;

	const {
		blockClass,
	} = attributes;

	const formSelectorAllowedBlocks = checkAttr('formSelectorAllowedBlocks', attributes, manifest);

	// Internal state to toggle buttons.
	const [hasInnerBlocks, setHasInnerBlocks] = useState(false);

	// Check if form selector has inner blocks.
	const hasInnerBlocksCheck = useSelect((select) => {
		const {innerBlocks} = select('core/block-editor').getBlock(clientId);

		if (!innerBlocks.length) {
			return false;
		}

		return true;
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

	return (
		<>
			{!hasInnerBlocks &&
				<>
					<div className={blockClass}>
						<div className={`${blockClass}__title`}>
							{__('Select one of our templates', 'eightshift-forms')}
						</div>
						<div className={`${blockClass}__items`}>
							{forms.map((form, index) => {
								const {
									label,
									slug,
								} = form;

								return (
									<Button
										className={`${blockClass}__button`}
										key={index}
										isPrimary
										onClick={() => createFormType(slug)}
										>
										<IconLabel
											icon={icons.options}
											label={sprintf(__('%s form', 'eightshift-forms'), label)}
										/>
									</Button>
								);
							})}
						</div>
						<div className={`${blockClass}__title-after`}>
							{__('or start building your own form', 'eightshift-forms')}
						</div>
					</div>
				</>
			}

			<InnerBlocks
				allowedBlocks={(typeof formSelectorAllowedBlocks === 'undefined') || formSelectorAllowedBlocks}
				templateLock={hasInnerBlocks}
			/>
		</>
	);
};
