import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import { select } from '@wordpress/data';
import {
	selector,
	checkAttr,
	props,
	STORE_NAME,
	getAttrKey,
} from '@eightshift/frontend-libs/scripts';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import { MissingName, VisibilityHidden, preventSaveOnMissingProps } from './../../utils';

export const SelectOptionEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('select-option');

	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
		blockClientId,
	} = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionAsPlaceholder = checkAttr('selectOptionAsPlaceholder', attributes, manifest);
	const selectOptionIsHidden = checkAttr('selectOptionIsHidden', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('selectOptionValue', attributes, manifest), selectOptionValue);

	const selectOptionClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
		selector(selectOptionIsHidden, 'es-form-is-hidden'),
		selector(selectOptionLabel === '', componentClass, '', 'placeholder'),
	]);

	return (
		<div className={selectOptionClass}>
			<VisibilityHidden value={selectOptionIsHidden} label={__('Option', 'eightshift-forms')} />

			{selectOptionLabel ? selectOptionLabel : __('Enter option label in sidebar.', 'eightshift-forms')}

			<MissingName value={selectOptionValue} asPlaceholder={selectOptionAsPlaceholder} />

			{selectOptionValue &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}
		</div>
	);
};
