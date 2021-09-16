import React from 'react';
import classnames from 'classnames';
import { InnerBlocks } from '@wordpress/editor';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const SelectEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const selectId = checkAttr('selectId', attributes, manifest);
	const selectAllowedBlocks = checkAttr('selectAllowedBlocks', attributes, manifest);

	const selectClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<>
			<div
				className={selectClass}
				id={selectId}
			>
				Select
					<InnerBlocks
						allowedBlocks={(typeof selectAllowedBlocks === 'undefined') || selectAllowedBlocks}
					/>
			</div>
		</>
	);
};
