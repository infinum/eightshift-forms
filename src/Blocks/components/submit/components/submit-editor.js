import React from 'react';
import { __ } from '@wordpress/i18n';
import { clsx } from '@eightshift/ui-components/utilities';
import { select } from '@wordpress/data';
import {
	selector,
	props,
	STORE_NAME,
	checkAttr,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';

export const SubmitEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('submit');

	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
	} = attributes;

	const submitValue = checkAttr('submitValue', attributes, manifest);

	const submitClass = clsx(
		componentClass,
		additionalClass,
	);

	const submitComponent = (
		<button className={submitClass}>
			{submitValue}
		</button>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: submitComponent,
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
