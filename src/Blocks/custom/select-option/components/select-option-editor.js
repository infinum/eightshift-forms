import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { SelectOptionEditor as SelectOptionEditorComponent } from '../../../components/select-option/components/select-option-editor';

export const SelectOptionEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<SelectOptionEditorComponent
				{...props('selectOption', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</div>
	);
}
