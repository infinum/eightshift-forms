import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { ChoiceEditor as ChoiceEditorComponent } from '../../../components/choice/components/choice-editor';

export const ChoiceEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<ChoiceEditorComponent
				{...props('choice', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</div>
	);
}
