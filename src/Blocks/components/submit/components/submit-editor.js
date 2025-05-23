import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { props, STORE_NAME, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';

export const SubmitEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('submit');

	const submitValue = checkAttr('submitValue', attributes, manifest);

	const submitComponent = <button>{submitValue}</button>;

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
