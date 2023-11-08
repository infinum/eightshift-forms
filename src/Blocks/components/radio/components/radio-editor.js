import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import classnames from 'classnames';
import { Tooltip } from '@wordpress/components';
import { selector,
	checkAttr,
	props,
	STORE_NAME,
	getAttrKey,
	icons,
} from '@eightshift/frontend-libs/scripts';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';

export const RadioEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('radio');

	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
		blockClientId,
	} = attributes;

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsHidden = checkAttr('radioIsHidden', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('radioValue', attributes, manifest), radioValue);

	const radioClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
		selector(radioIsHidden, 'es-form-is-hidden'),
	]);

	const radioLabelClass = classnames([
		selector(componentClass, componentClass, 'label'),
		selector(radioLabel === '', componentClass, 'label', 'placeholder'),
	]);

	const hideIndicator = radioIsHidden && (
		<div className='es-position-absolute es-right-7 es-top-0 es-nested-color-pure-white es-bg-cool-gray-650 es-nested-w-6 es-nested-h-6 es-w-8 es-h-8 es-rounded-full es-has-enhanced-contrast-icon es-display-flex es-items-center es-content-center'>
			<Tooltip text={__('Radio is hidden', 'eightshift-forms')}>
				{icons.hide}
			</Tooltip>
		</div>
	);

	return (
		<div className={radioClass}>
			{hideIndicator}

			<div className={`${componentClass}__content`}>
				<label className={radioLabelClass} htmlFor="id">
					<span className={`${componentClass}__label-inner`} dangerouslySetInnerHTML={{__html: radioLabel ? radioLabel : __('Please enter radio label in sidebar or this radio will not show on the frontend.', 'eightshift-forms')}} />
				</label>

				<MissingName value={radioValue} />

				{radioValue &&
					<ConditionalTagsEditor
						{...props('conditionalTags', attributes)}
					/>
				}
			</div>
		</div>
	);
};
