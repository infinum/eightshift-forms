import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr, props } from '@eightshift/frontend-libs/scripts';
import { ErrorEditor } from '../../error/components/error-editor';
import manifest from '../manifest.json';

export const FieldEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const fieldId = checkAttr('fieldId', attributes, manifest);
	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldContent = checkAttr('fieldContent', attributes, manifest);

	const fieldClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<div
			className={fieldClass}
		>
			{fieldLabel &&
				<label className={`${componentClass}__label`}>
					{fieldLabel}
				</label>
			}
			<div className={`${componentClass}__content`}>
				{fieldContent}
			</div>

			<ErrorEditor
				{...props('error', attributes, {
					errorId: fieldId
				})}
			/>
		</div>
	);
};
