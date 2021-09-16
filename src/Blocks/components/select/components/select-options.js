import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl} from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	props
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';
import manifest from '../manifest.json';

export const SelectOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const selectName = checkAttr('selectName', attributes, manifest);
	const selectId = checkAttr('selectId', attributes, manifest);
	const selectIsDisabled = checkAttr('selectIsDisabled', attributes, manifest);

	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
				value={selectName}
				onChange={(value) => setAttributes({ [getAttrKey('selectName', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Id', 'eightshift-forms')} />}
				value={selectId}
				onChange={(value) => setAttributes({ [getAttrKey('selectId', attributes, manifest)]: value })}
			/>

			<IconToggle
				icon={icons.play}
				label={__('Is Disabled', 'eightshift-forms')}
				checked={selectIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('selectIsDisabled', attributes, manifest)]: value })}
			/>
		</>
	);
};
