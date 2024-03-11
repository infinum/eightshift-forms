import React from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FormEditor } from '../../../components/form/components/form-editor';

export const CalculatorEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('calculator');

	const {
		blockClass,
	} = attributes;

	const calculatorAllowedBlocks = checkAttr('calculatorAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent:
						<InnerBlocks
							allowedBlocks={(typeof calculatorAllowedBlocks === 'undefined') || calculatorAllowedBlocks}
							templateLock={false}
							renderAppender={() => <BlockInserter clientId={clientId} />}
						/>
				})}
			/>
		</div>
	);
};
