import { props, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import manifest from '../manifest.json';

export const SubmitEditor = (attributes) => {
	const submitValue = checkAttr('submitValue', attributes, manifest);

	const submitComponent = (
		<button className='esf:w-full esf:p-10 esf:rounded-md esf:text-sm esf:text-white esf:bg-accent-600 esf:font-bold'>
			{submitValue}
		</button>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: submitComponent,
				})}
			/>
		</>
	);
};
