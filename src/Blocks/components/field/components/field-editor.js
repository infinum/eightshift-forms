/* global esFormsBlocksLocalization */

import React from 'react';
import classnames from 'classnames';
import { isObject } from 'lodash';
import { dispatch } from '@wordpress/data';
import {
	selector,
	checkAttr,
	outputCssVariables,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FieldEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
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
		dispatch(STORE_NAME).setSettingsGlobalVariablesBreakpoints(esFormsBlocksLocalization.mediaBreakpoints);
	}

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);
	const fieldContent = checkAttr('fieldContent', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldType = checkAttr('fieldType', attributes, manifest);
	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldStyle = checkAttr('fieldStyle', attributes, manifest);

	const fieldClass = classnames([
		selector(componentClass, componentClass),
		selector(componentClass, componentClass, '', selectorClass),
		selector(additionalFieldClass, additionalFieldClass),
		selector(fieldStyle && componentClass, componentClass, '', fieldStyle),
	]);

	const LabelDefault = () => (
		<>
			{!fieldHideLabel &&
				<label className={`${componentClass}__label`} htmlFor="id">
					<span className={`${componentClass}__label-inner`} dangerouslySetInnerHTML={{__html: fieldLabel}} />
				</label>
			}
		</>
	);

	const LegendDefault = () => (
		<>
			{!fieldHideLabel &&
				<legend className={`${componentClass}__label`}>
					<span className={`${componentClass}__label-inner`} dangerouslySetInnerHTML={{__html: fieldLabel}} />
				</legend>
			}
		</>
	);

	const Content = () => (
		<div className={`${componentClass}__content`}>
			{fieldBeforeContent &&
				<div className={`${componentClass}__before-content`}>
					{fieldBeforeContent}
				</div>
			}
			<div className={`${componentClass}__content-wrap`}>
				{fieldContent}
			</div>
			{fieldAfterContent &&
				<div className={`${componentClass}__after-content`}>
					{fieldAfterContent}
				</div>
			}
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
				{outputCssVariables(attributes, manifest, clientId, {}, 'wp-block')}

				<div className={`${componentClass}__inner`}>
					{fieldLabel &&
						<LabelDefault />
					}
					<Content />
					<Help />
				</div>
			</div>
		);
	};

	const FieldsetContent = () => {
		return(
			<fieldset className={fieldClass}>
				{outputCssVariables(attributes, manifest, clientId, {}, 'wp-block')}

				<div className={`${componentClass}__inner`}>
					{fieldLabel &&
						<LegendDefault />
					}
					<Content />
					<Help />
				</div>
			</fieldset>
		);
	};

	return (
		<>
		{fieldType === 'div' ? <DivContent /> : <FieldsetContent />}
		</>
	);
};
