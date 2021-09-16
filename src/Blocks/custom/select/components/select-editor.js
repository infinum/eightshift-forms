import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { SelectEditor as SelectEditorComponent } from '../../../components/select/components/select-editor';

export const SelectEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<SelectEditorComponent
				{...props('select', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</div>
	);
}
