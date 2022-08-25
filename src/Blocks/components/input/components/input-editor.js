/* global esFormsLocalization */

import React, { useMemo, useEffect } from 'react';
import classnames from 'classnames';
import {
	selector,
	checkAttr,
	props,
	getUnique,
	getAttrKey
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import manifest from './../manifest.json';

export const InputEditor = (attributes) => {
	const unique = useMemo(() => getUnique(), []);
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		setAttributes,

		additionalFieldClass,
		additionalClass,
	} = attributes;

	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	let inputType = checkAttr('inputType', attributes, manifest);

	// For some reason React won't allow input type email.
	if (inputType === 'email' || inputType === 'url') {
		inputType = 'text';
	}

	// Populate ID manually and make it generic.
	useEffect(() => {
		setAttributes({ [getAttrKey('inputId', attributes, manifest)]: unique });
	}, []); // eslint-disable-line

	const inputClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	// Additional content filter.
	let additionalContent = '';

	if (
		typeof esFormsLocalization !== 'undefined' &&
		(esFormsLocalization?.inputBlockAdditionalContent) !== ''
	) {
		additionalContent = esFormsLocalization.inputBlockAdditionalContent;
	}

	const input = (
		<>
			<input
				className={inputClass}
				value={inputValue}
				placeholder={inputPlaceholder}
				type={inputType}
				readOnly
			/>

			<div dangerouslySetInnerHTML={{__html: additionalContent}} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: input,
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
