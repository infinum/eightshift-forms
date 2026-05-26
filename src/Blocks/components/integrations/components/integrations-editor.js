import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';
import { BlockInserter, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { additionalBlocksIntegration, FormEditor } from './../../form/components/form-editor';
import { InvalidEditor } from './../../invalid/components/invalid-editor';
import { form } from '@eightshift/ui-components/icons';

const InvalidPlaceholder = () => {
	return (
		<InvalidEditor
			icon={form}
			heading={__('Select a form in the sidebar', 'eightshift-forms')}
		/>
	);
};

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

	const OutputDefault = () => {
		if (itemId) {
			return <Output />;
		}

		return <InvalidPlaceholder />;
	};

	const OutputWithInner = () => {
		if (itemId && innerId) {
			return <Output />;
		}

		return <InvalidPlaceholder />;
	};

	const Output = () => {
		return (
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: (
						<InnerBlocks
							allowedBlocks={[...allowedBlocks, ...additionalBlocksIntegration]}
							renderAppender={() => <BlockInserter clientId={clientId} />}
						/>
					),
				})}
			/>
		);
	};

	if (hasInnerBlocks) {
		if (useInnerId) {
			return <OutputWithInner />;
		}

		return <OutputDefault />;
	}

	return <InvalidPlaceholder />;
};
