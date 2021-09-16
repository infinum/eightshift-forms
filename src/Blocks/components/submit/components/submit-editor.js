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

	const submitValue = checkAttr('submitValue', attributes, manifest);
	const submitType = checkAttr('submitType', attributes, manifest);
	const submitId = checkAttr('submitId', attributes, manifest);

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
					id={submitId}
				>
					{submitValue}
				</button> :
				<input
					type='submit'
					value={submitValue}
					className={submitClass}
					id={submitId}
				/>
			}
		</>
	);
};
