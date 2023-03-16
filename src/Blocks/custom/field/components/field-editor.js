import React from 'react';
import { FieldEditorExternalBlocks } from '../../../components/field/components/field-editor';

export const FieldEditor = ({ attributes, children, clientId }) => {
	return (
		<FieldEditorExternalBlocks
			attributes={attributes}
			children={children}
			clientId={clientId}
		/>
	);
};
