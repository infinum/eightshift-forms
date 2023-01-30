import React from 'react';
import classnames from 'classnames';
import {
	selector,
	props,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent } from './../../utils';
import manifest from '../manifest.json';

export const FileEditor = (attributes) => {
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
	} = attributes;

	const fileClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	const file = (
		<>
			<input
				className={fileClass}
				type={'file'}
				readOnly
				disabled
			/>

			<div dangerouslySetInnerHTML={{__html: getAdditionalContentFilterContent(componentName)}} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: file,
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
