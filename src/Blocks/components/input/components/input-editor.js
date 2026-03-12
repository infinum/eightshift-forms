import React from 'react';
import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconConditionals, StatusIconMissingName } from './../../utils';
import manifest from '../manifest.json';
import { clsx } from '@eightshift/ui-components/utilities';

export const InputEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const inputName = checkAttr('inputName', attributes, manifest);
	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	const inputType = checkAttr('inputType', attributes, manifest);
	const inputMin = checkAttr('inputMin', attributes, manifest);
	const inputMax = checkAttr('inputMax', attributes, manifest);
	const inputStep = checkAttr('inputStep', attributes, manifest);
	const inputIsDisabled = checkAttr('inputIsDisabled', attributes, manifest);
	const inputIsReadOnly = checkAttr('inputIsReadOnly', attributes, manifest);
	const inputIsRequired = checkAttr('inputIsRequired', attributes, manifest);

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
				className={clsx(
					'esf-input',
					inputIsDisabled && 'esf-input-disabled',
					inputIsReadOnly && 'esf-input-readonly',
					inputIsRequired && 'esf-input-required',
				)}
				value={inputValue}
				placeholder={inputPlaceholder}
				type={inputType}
				disabled
				{...additionalProps}
			/>
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: input,
					fieldIsRequired: checkAttr('inputIsRequired', attributes, manifest),
				})}
				statusSlog={[
					!inputName && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</>
	);
};
