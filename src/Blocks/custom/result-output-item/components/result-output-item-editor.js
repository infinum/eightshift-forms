import React from 'react';
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';
import { checkAttr, BlockInserter, selector } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ResultOutputItemEditor = ({ attributes, clientId }) => {
	const {
		blockClass,
	} = attributes;

	const resultOutputItemName = checkAttr('resultOutputItemName', attributes, manifest);
	const resultOutputItemValue = checkAttr('resultOutputItemValue', attributes, manifest);

	return (
		<div className={blockClass}>
			<div className={selector(blockClass, blockClass, 'intro')}>
				<div className={selector(blockClass, blockClass, 'intro-inner')}>
					{__('Show if the following condition matches:', 'eightshift-forms')}
					<br/>
					<br/>
					{resultOutputItemName} = {resultOutputItemValue}
				</div>
			</div>

			<InnerBlocks
				renderAppender={() => <BlockInserter clientId={clientId} />}
			/>
		</div>
	);
};
