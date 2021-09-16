import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const SubmitEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const submitName = checkAttr('submitName', attributes, manifest);
	const submitValue = checkAttr('submitValue', attributes, manifest);
	const submitId = checkAttr('submitId', attributes, manifest);
	const submitType = checkAttr('submitType', attributes, manifest);
	const submitIsDisabled = checkAttr('submitIsDisabled', attributes, manifest);

	const submitClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<>
			{submitType === 'button' ?
				<button
					className={submitClass}
					name={submitName}
					id={submitId}
					disabled={submitIsDisabled}
				>
						{submitValue}
				</button> :
				<input
					type='submit'
					value={submitValue}
					className={submitClass}
					name={submitName}
					id={submitId}
					disabled={submitIsDisabled}
				/>
			}
		</>
	);
};
