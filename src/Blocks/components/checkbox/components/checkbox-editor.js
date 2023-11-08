import React  from 'react';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import { Tooltip } from '@wordpress/components';
import {
	selector,
	checkAttr,
	props,
	STORE_NAME,
	getAttrKey,
	icons,
} from '@eightshift/frontend-libs/scripts';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';

export const CheckboxEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('checkbox');

	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
		blockClientId,
	} = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsHidden = checkAttr('checkboxIsHidden', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('checkboxValue', attributes, manifest), checkboxValue);

	const checkboxClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
		selector(checkboxIsHidden, 'es-form-is-hidden'),
	]);

	const checkboxLabelClass = classnames([
		selector(componentClass, componentClass, 'label'),
		selector(checkboxLabel === '', componentClass, 'label', 'placeholder'),
	]);

	const hideIndicator = checkboxIsHidden && (
		<div className='es-position-absolute es-right-7 es-top-0 es-nested-color-pure-white es-bg-cool-gray-650 es-nested-w-6 es-nested-h-6 es-w-8 es-h-8 es-rounded-full es-has-enhanced-contrast-icon es-display-flex es-items-center es-content-center'>
			<Tooltip text={__('Checkbox is hidden', 'eightshift-forms')}>
				{icons.hide}
			</Tooltip>
		</div>
	);

	return (
		<div className={checkboxClass}>
			{hideIndicator}

			<div className={`${componentClass}__content`}>
				<label className={checkboxLabelClass} htmlFor="id">
					<span className={`${componentClass}__label-inner`} dangerouslySetInnerHTML={{__html: checkboxLabel ? checkboxLabel : __('Please enter checkbox label in sidebar or this checkbox will not show on the frontend.', 'eightshift-forms')}} />
				</label>

				<MissingName value={checkboxValue} />

				{checkboxValue &&
					<ConditionalTagsEditor
						{...props('conditionalTags', attributes)}
					/>
				}
			</div>
		</div>
	);
};
