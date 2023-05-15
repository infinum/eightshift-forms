import React from 'react';
import classnames from 'classnames';
import {
	selector,
	checkAttr,
	props,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import manifest from '../manifest.json';

export const DateEditor = (attributes) => {
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
	} = attributes;

	const dateValue = checkAttr('dateValue', attributes, manifest);
	const datePlaceholder = checkAttr('datePlaceholder', attributes, manifest);
	const dateType = checkAttr('dateType', attributes, manifest);
	const dateName = checkAttr('dateName', attributes, manifest);

	const dateClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	const date = (
		<>
			<input
				className={dateClass}
				value={dateValue}
				placeholder={datePlaceholder}
				type={dateType}
				readOnly
			/>

			<MissingName value={dateName} />

			{dateName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}

			<div dangerouslySetInnerHTML={{ __html: getAdditionalContentFilterContent(componentName) }} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: date,
					fieldIsRequired: checkAttr('dateIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
