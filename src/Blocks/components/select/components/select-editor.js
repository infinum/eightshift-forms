import React from 'react';
import classnames from 'classnames';
import {
	selector,
	checkAttr,
	props,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { getAdditionalContentFilterContent } from './../../utils';
import manifest from '../manifest.json';

export const SelectEditor = (attributes) => {
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
	} = attributes;

	const selectContent = checkAttr('selectContent', attributes, manifest);

	const selectClass = classnames([
		selector(componentClass, componentClass),
		selector(componentClass, componentClass, '', 'disabled'),
		selector(additionalClass, additionalClass),
	]);

	const select = (
		<>
			<div className={selectClass}>
				{selectContent}
			</div>

			<div dangerouslySetInnerHTML={{__html: getAdditionalContentFilterContent(componentName)}} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: select
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
