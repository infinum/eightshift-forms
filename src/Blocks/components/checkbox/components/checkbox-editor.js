import React from 'react';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import { MissingName, VisibilityHidden, preventSaveOnMissingProps } from './../../utils';

export const CheckboxEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('checkbox');

	const { blockClientId } = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsHidden = checkAttr('checkboxIsHidden', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('checkboxValue', attributes, manifest), checkboxValue);

	return (
		<div className={'es:p-3'}>
			<VisibilityHidden
				value={checkboxIsHidden}
				label={__('Checkbox', 'eightshift-forms')}
			/>

			<div>
				<div>
					<span
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
