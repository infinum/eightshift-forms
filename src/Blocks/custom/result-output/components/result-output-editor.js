import { form } from '@eightshift/ui-components/icons';
import { __ } from '@wordpress/i18n';
import { Notice } from '@eightshift/ui-components';

export const ResultOutputEditor = () => {
	return (
		<div className='esf:p-8 es:font-sans'>
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
