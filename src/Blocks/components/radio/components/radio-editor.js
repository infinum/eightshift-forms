import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import { MissingName, VisibilityHidden, preventSaveOnMissingProps } from './../../utils';

export const RadioEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('radio');

	const { blockClientId } = attributes;

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsHidden = checkAttr('radioIsHidden', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('radioValue', attributes, manifest), radioValue);

	return (
		<div className={'es:p-3'}>
			<VisibilityHidden
				value={radioIsHidden}
				label={__('Radio', 'eightshift-forms')}
			/>

			<div>
				<div>
					<span
						dangerouslySetInnerHTML={{
							__html: radioLabel ? radioLabel : __('Please enter radio label in sidebar or this radio will not show on the frontend.', 'eightshift-forms'),
						}}
					/>
				</div>

				<MissingName value={radioValue} />

				{radioValue && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
			</div>
		</div>
	);
};
