import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
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

export const TextareaOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const textareaName = checkAttr('textareaName', attributes, manifest);
	const textareaValue = checkAttr('textareaValue', attributes, manifest);
	const textareaId = checkAttr('textareaId', attributes, manifest);
	const textareaPlaceholder = checkAttr('textareaPlaceholder', attributes, manifest);
	const textareaIsDisabled = checkAttr('textareaIsDisabled', attributes, manifest);
	const textareaIsReadOnly = checkAttr('textareaIsReadOnly', attributes, manifest);
	const textareaIsRequired = checkAttr('textareaIsRequired', attributes, manifest);

	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
				value={textareaName}
				onChange={(value) => setAttributes({ [getAttrKey('textareaName', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Value', 'eightshift-forms')} />}
				value={textareaValue}
				onChange={(value) => setAttributes({ [getAttrKey('textareaValue', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Id', 'eightshift-forms')} />}
				value={textareaId}
				onChange={(value) => setAttributes({ [getAttrKey('textareaId', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Placeholder', 'eightshift-forms')} />}
				value={textareaPlaceholder}
				onChange={(value) => setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: value })}
			/>

			<IconToggle
				icon={icons.play}
				label={__('Is Disabled', 'eightshift-forms')}
				checked={textareaIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('textareaIsDisabled', attributes, manifest)]: value })}
			/>

			<IconToggle
				icon={icons.play}
				label={__('Is Read Only', 'eightshift-forms')}
				checked={textareaIsReadOnly}
				onChange={(value) => setAttributes({ [getAttrKey('textareaIsReadOnly', attributes, manifest)]: value })}
			/>

			<IconToggle
				icon={icons.play}
				label={__('Is Required', 'eightshift-forms')}
				checked={textareaIsRequired}
				onChange={(value) => setAttributes({ [getAttrKey('textareaIsRequired', attributes, manifest)]: value })}
			/>
		</>
	);
};
