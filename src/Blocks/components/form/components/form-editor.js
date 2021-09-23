import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr, ServerSideRender } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
		blockFullName,
	} = attributes;

	const formContent = checkAttr('formContent', attributes, manifest);
	const formIntegration = checkAttr('formIntegration', attributes, manifest);

	const formClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<>
			<form className={formClass}>
				{formIntegration === 'none' &&
					<div className={`${componentClass}__fields`}>
						{formContent}
					</div>
				}
			</form>
		</>
	);
};
