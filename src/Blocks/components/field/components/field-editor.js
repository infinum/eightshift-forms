/* global esFormsBlocksLocalization */

import React from 'react';
import classnames from 'classnames';
import { isObject } from 'lodash';
import {
	selector,
	checkAttr,
	outputCssVariables
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import globalManifest from './../../../manifest.json';

export const FieldEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalFieldClass,
		clientId,
	} = attributes;

	// Update media breakpoints from the filter.
	if (
		typeof esFormsBlocksLocalization !== 'undefined' &&
		isObject(esFormsBlocksLocalization?.mediaBreakpoints) &&
		Object.prototype.hasOwnProperty.call(esFormsBlocksLocalization?.mediaBreakpoints, "mobile") &&
		Object.prototype.hasOwnProperty.call(esFormsBlocksLocalization?.mediaBreakpoints, "tablet") &&
		Object.prototype.hasOwnProperty.call(esFormsBlocksLocalization?.mediaBreakpoints, "desktop") &&
		Object.prototype.hasOwnProperty.call(esFormsBlocksLocalization?.mediaBreakpoints, "large")
	) {
		Object.assign(globalManifest.globalVariables.breakpoints, esFormsBlocksLocalization.mediaBreakpoints);
	}

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldContent = checkAttr('fieldContent', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldType = checkAttr('fieldType', attributes, manifest);
	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);

	const fieldClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalFieldClass, additionalFieldClass),
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
				{fieldBeforeContent}
				{fieldContent}
				{fieldAfterContent}
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
				{outputCssVariables(attributes, manifest, clientId, globalManifest, 'wp-block')}

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
				{outputCssVariables(attributes, manifest, clientId, globalManifest, 'wp-block')}

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
