import { form } from '@eightshift/ui-components/icons';
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

export const ResultOutputEditor = () => {
	return (
		<Placeholder
			icon={form}
			label={<span>{__('Eightshift Forms - Result output', 'eightshift-forms')}</span>}
		/>
	);
};
