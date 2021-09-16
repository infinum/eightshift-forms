import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { FieldsetEditor } from '../../../components/fieldset/components/fieldset-editor';

export const RadioEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<FieldsetEditor
				{...props('fieldset', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</div>
	);
}
