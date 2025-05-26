import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const InputEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('input');

	const { blockClientId } = attributes;

	const inputName = checkAttr('inputName', attributes, manifest);
	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	const inputType = checkAttr('inputType', attributes, manifest);
	const inputMin = checkAttr('inputMin', attributes, manifest);
	const inputMax = checkAttr('inputMax', attributes, manifest);
	const inputStep = checkAttr('inputStep', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('inputName', attributes, manifest), inputName);

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
				value={inputValue}
				placeholder={inputPlaceholder}
				type={inputType}
				readOnly
				className={'es:min-h-10 es:w-full es:border es:border-secondary-300 es:bg-white es:p-2 es:text-sm'}
				{...additionalProps}
			/>

			<MissingName value={inputName} />

			{inputName && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: input,
					fieldIsRequired: checkAttr('inputIsRequired', attributes, manifest),
				})}
			/>
		</>
	);
};
