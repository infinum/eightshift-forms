import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { form } from '@eightshift/ui-components/icons';
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';
import manifest from '../manifest.json';

export const ResultOutputEditor = ({ attributes, setAttributes }) => {
	const resultOutputFormPostId = checkAttr('resultOutputFormPostId', attributes, manifest);
	const resultOutputFormPostIdRaw = checkAttr('resultOutputFormPostIdRaw', attributes, manifest);
	const resultOutputPostIdRaw = checkAttr('resultOutputPostIdRaw', attributes, manifest);
	const resultOutputPostId = checkAttr('resultOutputPostId', attributes, manifest);

	return (
		<Placeholder
			icon={form}
			label={<span>{__('Eightshift Forms - Result output', 'eightshift-forms')}</span>}
		/>
	);
};
