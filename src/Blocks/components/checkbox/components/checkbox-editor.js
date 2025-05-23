import React from 'react';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { selector, checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { clsx } from '@eightshift/ui-components/utilities';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import { MissingName, VisibilityHidden, preventSaveOnMissingProps } from './../../utils';

export const CheckboxEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('checkbox');

	const { componentClass } = manifest;

	const { selectorClass = componentClass, blockClass, additionalClass, blockClientId } = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsHidden = checkAttr('checkboxIsHidden', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('checkboxValue', attributes, manifest), checkboxValue);

	const checkboxClass = clsx(
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
		selector(checkboxIsHidden, 'es-form-is-hidden'),
	);

	const checkboxLabelClass = clsx(
		selector(componentClass, componentClass, 'label'),
		selector(checkboxLabel === '', componentClass, 'label', 'placeholder'),
		selector(checkboxIsChecked, componentClass, 'label', 'checked'),
	);

	return (
		<div className={checkboxClass}>
			<VisibilityHidden
				value={checkboxIsHidden}
				label={__('Checkbox', 'eightshift-forms')}
			/>

			<div className={`${componentClass}__content`}>
				<div className={checkboxLabelClass}>
					<span
						className={`${componentClass}__label-inner`}
						dangerouslySetInnerHTML={{
							__html: checkboxLabel ? checkboxLabel : __('Please enter checkbox label in sidebar or this checkbox will not show on the frontend.', 'eightshift-forms'),
						}}
					/>
				</div>

				<MissingName value={checkboxValue} />

				{checkboxValue && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
			</div>
		</div>
	);
};
