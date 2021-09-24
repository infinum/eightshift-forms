import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
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

	const formContent = checkAttr('formContent', attributes, manifest);
	const formIntegration = checkAttr('formIntegration', attributes, manifest);

	const formClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<>
			{formIntegration === 'none' ?
				<form className={formClass}>
					<div className={`${componentClass}__fields`}>
						{formContent}
					</div>
				</form> :
				<div className={`${componentClass}__integration`}>
					{sprintf(__('Your %s form will be displayed here on the frontend.', 'eightshift-forms'), formIntegration)}
				</div>
				}
		</>
	);
};
