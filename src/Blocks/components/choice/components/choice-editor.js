import React from 'react';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ChoiceEditor = (attributes) => {
	const choiceLabel = checkAttr('choiceLabel', attributes, manifest);
	const choiceValue = checkAttr('choiceValue', attributes, manifest);
	const choiceName = checkAttr('choiceName', attributes, manifest);
	const choiceType = checkAttr('choiceType', attributes, manifest);
	const choiceIsDisabled = checkAttr('choiceIsDisabled', attributes, manifest);
	const choiceIsChecked = checkAttr('choiceIsChecked', attributes, manifest);
	const choiceIsReadOnly = checkAttr('choiceIsReadOnly', attributes, manifest);

	return (
		<>
			<label htmlFor={choiceName}>
				{choiceLabel}
			</label>
			<input
				type={choiceType}
				value={choiceValue}
				disabled={choiceIsDisabled}
				checked={choiceIsChecked}
				readOnly={choiceIsReadOnly}
			/>
		</>
	);
};
