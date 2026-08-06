import { form } from '@eightshift/ui-components/icons';
import { __ } from '@wordpress/i18n';
import { Notice } from '@eightshift/ui-components';
import { useBlockProps } from '@wordpress/block-editor';

export const ResultOutputEditor = () => {
	const blockProps = useBlockProps({
		className: 'esf:p-8 es:font-sans',
	});

	return (
		<div {...blockProps}>
			<Notice
				type='placeholder'
				icon={form}
				label={__('Eightshift Forms', 'eightshift-forms')}
				subtitle={__('Result output', 'eightshift-forms')}
				className='esf:w-fit'
			/>
		</div>
	);
};
