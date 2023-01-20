/* global esFormsLocalization */

import React, { useMemo } from 'react';
import classnames from 'classnames';
import {
	selector,
	checkAttr,
	props,
	getUnique,
	getAttrKey
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import manifest from '../manifest.json';

export const DateEditor = (attributes) => {
	const unique = useMemo(() => getUnique(), []);
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

	const dateClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	// Additional content filter.
	let additionalContent = '';

	if (
		typeof esFormsLocalization !== 'undefined' &&
		(esFormsLocalization?.dateBlockAdditionalContent) !== ''
	) {
		additionalContent = esFormsLocalization.dateBlockAdditionalContent;
	}

	const date = (
		<>
			<input
				className={dateClass}
				value={dateValue}
				placeholder={datePlaceholder}
				type={'tel'}
				readOnly
			/>

			<div dangerouslySetInnerHTML={{__html: additionalContent}} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: date,
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
