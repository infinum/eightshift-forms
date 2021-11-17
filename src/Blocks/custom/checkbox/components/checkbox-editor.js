import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { CheckboxEditor as CheckboxEditorComponent } from '../../../components/checkbox/components/checkbox-editor';

export const CheckboxEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<CheckboxEditorComponent
				{...props('checkbox', attributes, {
					setAttributes,
				})}
			/>
		</div>
	);
};
