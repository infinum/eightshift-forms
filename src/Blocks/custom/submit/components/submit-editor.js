import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { SubmitEditor as SubmitEditorComponent } from '../../../components/submit/components/submit-editor';

export const SubmitEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<SubmitEditorComponent
			{...props('submit', attributes, {
				setAttributes,
				clientId,
			})}
		/>
	);
};
