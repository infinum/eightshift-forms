import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr, props } from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import manifest from '../manifest.json';

export const FileEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const fileId = checkAttr('fileId', attributes, manifest);
	const fileIsMultiple = checkAttr('fileIsMultiple', attributes, manifest);

	const fileClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	const file = (
		<input
			className={fileClass}
			type={'file'}
			id={fileId}
			{...fileIsMultiple ? 'multiple' : ''}
		/>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: file,
					fieldId: fileId
				})}
			/>
		</>
	);
};
