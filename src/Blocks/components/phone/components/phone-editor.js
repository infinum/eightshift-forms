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

export const PhoneEditor = (attributes) => {
	const unique = useMemo(() => getUnique(), []);
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
	} = attributes;

	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);

	const phoneClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	// Additional content filter.
	let additionalContent = '';

	if (
		typeof esFormsLocalization !== 'undefined' &&
		(esFormsLocalization?.phoneBlockAdditionalContent) !== ''
	) {
		additionalContent = esFormsLocalization.phoneBlockAdditionalContent;
	}

	const phone = (
		<>
			<input
				className={phoneClass}
				value={phoneValue}
				placeholder={phonePlaceholder}
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
					fieldContent: phone,
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
