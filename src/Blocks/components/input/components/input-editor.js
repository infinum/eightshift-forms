import React from 'react';
import classnames from 'classnames';
import { select } from '@wordpress/data';
import { selector, checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const InputEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('input');

	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		additionalClass,
		blockClientId,
	} = attributes;

	const inputName = checkAttr('inputName', attributes, manifest);
	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	const inputType = checkAttr('inputType', attributes, manifest);
	const inputMin = checkAttr('inputMin', attributes, manifest);
	const inputMax = checkAttr('inputMax', attributes, manifest);
	const inputStep = checkAttr('inputStep', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('inputName', attributes, manifest), inputName);

	const inputClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	let additionalProps = {};

	if (inputType === 'range') {
		additionalProps = {
			min: inputMin,
			max: inputMax,
			step: inputStep,
			value: inputValue ?? inputMin,
		};
	}

	const input = (
		<>
			<input
				className={inputClass}
				value={inputValue}
				placeholder={inputPlaceholder}
				type={inputType}
				readOnly
				{...additionalProps}
			/>

			<MissingName value={inputName} />

			{inputName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: input,
					fieldIsRequired: checkAttr('inputIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
