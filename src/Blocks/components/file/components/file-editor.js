import React from 'react';
import classnames from 'classnames';
import {
	selector,
	props,
	checkAttr,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName } from './../../utils';
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

	const fileName = checkAttr('fileName', attributes, manifest);

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

			<MissingName value={fileName} isEditor={true} />

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
