import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import classnames from 'classnames';
import { selector,
	checkAttr,
	props,
	STORE_NAME,
	getAttrKey,
} from '@eightshift/frontend-libs/scripts';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import { MissingName, VisibilityHidden, preventSaveOnMissingProps } from './../../utils';

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

	return (
		<div className={radioClass}>
			<VisibilityHidden value={radioIsHidden} label={__('Radio', 'eightshift-forms')} />

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
