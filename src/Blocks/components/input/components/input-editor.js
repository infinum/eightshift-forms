import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { preventSaveOnMissingProps } from './../../utils';
import manifest from '../manifest.json';

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
		<input
			className='esf-input'
			value={inputValue}
			placeholder={inputPlaceholder}
			type={inputType}
			disabled
			{...additionalProps}
		/>
	);

	return (
		<FieldEditor
			{...props('field', attributes, {
				fieldContent: input,
				fieldIsRequired: checkAttr('inputIsRequired', attributes, manifest),
			})}
			statusSlot={[
				!inputName && 'missingName',
				inputIsDisabled && 'disabled',
				inputIsRequired && 'required',
				attributes?.[`${prefix}ConditionalTagsUse`] && 'conditionals',
			].filter(Boolean)}
		/>
	);
};
