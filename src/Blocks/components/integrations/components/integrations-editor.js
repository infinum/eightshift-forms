import React from 'react';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';
import { props } from '@eightshift/frontend-libs/scripts';
import { FormEditor } from './../../form/components/form-editor';
import { InvalidEditor } from './../../invalid/components/invalid-editor';

export const IntegrationsEditor = ({
	attributes,
	setAttributes,
	itemId,
	innerId,
	clientId,
	useInnerId = false,
	allowedBlocks = [],
}) => {

	// Check if form selector has inner blocks.
	const hasInnerBlocks = useSelect((select) => {
		const blocks = select('core/block-editor').getBlock(clientId);

		return blocks?.innerBlocks.length !== 0;
	});

	const InvalidPlaceholder = () => {
		return (
			<InvalidEditor
				heading={__('Form not selected', 'eightshift-forms')}
			/>
		);
	};

	const OutputDefault = () => {
		return (
			<>
				{itemId ?
					<Output /> :
					<InvalidPlaceholder />
				}
			</>
		);
	};

	const OutputWithInner = () => {
		return (
			<>
				{(itemId && innerId) ?
					<Output /> :
					<InvalidPlaceholder />
				}
			</>
		);
	};

	const Output = () => {
		return (
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
						allowedBlocks={allowedBlocks}
						templateLock={!allowedBlocks.length}
					/>
				})}
			/>
		);
	};

	if (hasInnerBlocks) {
		return (
			useInnerId ? <OutputWithInner /> : <OutputDefault />
		);
	}

	return (
		<InvalidPlaceholder />
	);
};
