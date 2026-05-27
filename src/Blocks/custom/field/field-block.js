import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { FieldEditor } from './components/field-editor';
import { FieldOptions } from './components/field-options';
import { ContainerPanel } from '@eightshift/ui-components';
import { form } from '@eightshift/ui-components/icons';

export const Field = (props) => {
	const { setAttributes, attributes, children, clientId } = props;

	return (
		<>
			<InspectorControls>
				<ContainerPanel
					icon={form}
					title={__('Eightshift Forms', 'eightshift-forms')}
					className='esf:border-b esf:border-b-gray-200'
					closable
				>
					<FieldOptions
						attributes={attributes}
						setAttributes={setAttributes}
					/>
				</ContainerPanel>
			</InspectorControls>
			<FieldEditor
				attributes={attributes}
				children={children}
				clientId={clientId}
			/>
		</>
	);
};
