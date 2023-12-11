/* global esFormsLocalization */

import React from 'react';
import classnames from 'classnames';
import { isObject } from 'lodash';
import { __ } from '@wordpress/i18n';
import { dispatch, select } from '@wordpress/data';
import {
	selector,
	checkAttr,
	outputCssVariables,
	STORE_NAME,
	props,
} from '@eightshift/frontend-libs/scripts';
import { MissingName, VisibilityHidden } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const FieldEditorExternalBlocks = ({
	attributes,
	children,
	clientId,
	fieldName,
}) => {
	const manifest = select(STORE_NAME).getComponent('field');

	const {
		componentClass,
	} = manifest;

	const fieldClass = classnames([
		selector(componentClass, componentClass),
		selector(componentClass, componentClass, '', 'field'),
	]);

	return (
		<div className={fieldClass}>
			{outputCssVariables(attributes, manifest, clientId, {}, 'wp-block')}
			<div className={`${componentClass}__inner`}>
				<div className={`${componentClass}__content`}>
					<div className={`${componentClass}__content-wrap`}>
						{children}

						<MissingName value={fieldName} />

						{fieldName &&
							<ConditionalTagsEditor
								{...props('conditionalTags', attributes)}
								conditionalTagsUse={attributes?.conditionalTagsUse}
								useCustom
							/>
						}
					</div>
				</div>
			</div>
		</div>
	);
};

export const FieldEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		additionalFieldClass,
		clientId,
	} = attributes;

	const fieldContent = checkAttr('fieldContent', attributes, manifest);
	const fieldSkip = checkAttr('fieldSkip', attributes, manifest);
	const fieldIsRequired = checkAttr('fieldIsRequired', attributes, manifest);

	// Enable option to skip field and just render content.
	if (fieldSkip) {
		return fieldContent;
	}

	// Update media breakpoints from the filter.
	if (
		typeof esFormsLocalization !== 'undefined' &&
		isObject(esFormsLocalization?.mediaBreakpoints) &&
		Object.prototype.hasOwnProperty.call(esFormsLocalization?.mediaBreakpoints, "mobile") &&
		Object.prototype.hasOwnProperty.call(esFormsLocalization?.mediaBreakpoints, "tablet") &&
		Object.prototype.hasOwnProperty.call(esFormsLocalization?.mediaBreakpoints, "desktop") &&
		Object.prototype.hasOwnProperty.call(esFormsLocalization?.mediaBreakpoints, "large")
	) {
		dispatch(STORE_NAME).setSettingsGlobalVariablesBreakpoints(esFormsLocalization.mediaBreakpoints);
	}

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldType = checkAttr('fieldType', attributes, manifest);
	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldStyle = checkAttr('fieldStyle', attributes, manifest);
	const fieldHidden = checkAttr('fieldHidden', attributes, manifest);

	const fieldClass = classnames([
		selector(componentClass, componentClass),
		selector(componentClass, componentClass, '', selectorClass),
		selector(additionalFieldClass, additionalFieldClass),
		selector(fieldHidden, 'es-form-is-hidden'),
		selector(fieldStyle && componentClass, componentClass, '', fieldStyle),
	]);

	const labelClass = classnames([
		selector(componentClass, componentClass, 'label'),
		selector(fieldIsRequired && componentClass, componentClass, 'label', 'is-required'),
	]);

	const LabelDefault = () => (
		<>
			{!fieldHideLabel &&
				<div className={labelClass}>
					<span className={`${componentClass}__label-inner`} dangerouslySetInnerHTML={{ __html: fieldLabel }} />
				</div>
			}
		</>
	);

	const LegendDefault = () => (
		<>
			{!fieldHideLabel &&
				<div className={labelClass}>
					<span className={`${componentClass}__label-inner`} dangerouslySetInnerHTML={{ __html: fieldLabel }} />
				</div>
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
		return (
			<div className={fieldClass}>
				{outputCssVariables(attributes, manifest, clientId, {}, 'wp-block')}

				<div className={`${componentClass}__inner`}>
					{fieldLabel &&
						<LabelDefault />
					}
					<Content />
					<Help />
				</div>

				<VisibilityHidden value={fieldHidden} label={__('Field', 'eightshift-forms')} />
			</div>
		);
	};

	const FieldsetContent = () => {
		return (
			<fieldset className={fieldClass}>
				{outputCssVariables(attributes, manifest, clientId, {}, 'wp-block')}

				<div className={`${componentClass}__inner`}>
					{fieldLabel &&
						<LegendDefault />
					}
					<Content />
					<Help />
				</div>

				<VisibilityHidden value={fieldHidden} label={__('Field', 'eightshift-forms')} />
			</fieldset>
		);
	};

	return fieldType === 'div' ? <DivContent /> : <FieldsetContent />;
};
