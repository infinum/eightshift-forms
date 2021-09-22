import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FieldEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldContent = checkAttr('fieldContent', attributes, manifest);
	const fieldType = checkAttr('fieldType', attributes, manifest);
	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);

	const fieldClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	const LabelDefault = () => (
		<label className={`${componentClass}__label`}>
			{fieldLabel}
		</label>
	);

	const LegendDefault = () => (
		<legend className={`${componentClass}__label`}>
			{fieldLabel}
		</legend>
	);

	const Content = () => (
		<div className={`${componentClass}__content`}>
				{fieldContent}
			</div>
	);

	const Help = () => (
		<div className={`${componentClass}__help`}>
				{fieldHelp}
			</div>
	);

	const DivContent = () => {
		return(
			<div className={fieldClass}>
				{fieldLabel &&
					<LabelDefault />
				}
				<Content />
				<Help />
			</div>
		)
	};

	const FieldsetContent = () => {
		return(
			<fieldset className={fieldClass}>
				{fieldLabel &&
					<LegendDefault />
				}
				<Content />
				<Help />
			</fieldset>
		)
	};

	return (
		<>
		{fieldType === 'div' ? <DivContent /> : <FieldsetContent />}
		</>
	);
};
