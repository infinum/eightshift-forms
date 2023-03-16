import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { FieldEditor } from './components/field-editor';
import { FieldOptions } from './components/field-options';

export const Field = (props) => {
	const {
		setAttributes,
		attributes,
		children,
		clientId,
	} = props;

	return (
		<>
			<InspectorControls>
				<FieldOptions 
					attributes={attributes}
					setAttributes={setAttributes}
				/>
			</InspectorControls>
			<FieldEditor
				attributes={attributes}
				children={children}
				clientId={clientId}
			/>
		</>
	);
};
