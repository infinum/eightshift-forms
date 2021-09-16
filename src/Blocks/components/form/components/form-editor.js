import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const formName = checkAttr('formName', attributes, manifest);
	const formAction = checkAttr('formAction', attributes, manifest);
	const formMethod = checkAttr('formMethod', attributes, manifest);
	const formTarget = checkAttr('formTarget', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);
	const formContent = checkAttr('formContent', attributes, manifest);

	const formClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<>
			<form
				className={formClass}
				id={formId}
				name={formName}
				action={formAction}
				method={formMethod}
				target={formTarget}
			>
				{formContent}
			</form>
		</>
	);
};
