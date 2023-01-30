/* global esFormsLocalization */

import React from 'react';
import classnames from 'classnames';
import { select } from '@wordpress/data';
import {
	selector,
	checkAttr,
	props,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent } from './../../utils';
import manifest from '../manifest.json';

export const PhoneEditor = (attributes) => {
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
	} = attributes;

	const manifestSelect = select(STORE_NAME).getComponent('select');

	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);

	const phoneClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	const selectClass = classnames([
		selector(manifestSelect.componentClass, manifestSelect.componentClass),
		selector(additionalClass, additionalClass),
	]);

	const phone = (
		<>
			<select className={selectClass}></select>
			<input
				className={phoneClass}
				value={phoneValue}
				placeholder={phonePlaceholder}
				type={'tel'}
				readOnly
			/>

			<div dangerouslySetInnerHTML={{__html: getAdditionalContentFilterContent(componentName)}} />
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
