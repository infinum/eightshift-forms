import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, ToggleControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	props
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import manifest from '../manifest.json';

export const FileOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileAccept = checkAttr('fileAccept', attributes, manifest);
	const fileId = checkAttr('fileId', attributes, manifest);
	const fileIsMultiple = checkAttr('fileIsMultiple', attributes, manifest);
	const fileIsRequired = checkAttr('fileIsRequired', attributes, manifest);
	const fileTracking = checkAttr('fileTracking', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
				value={fileName}
				onChange={(value) => setAttributes({ [getAttrKey('fileName', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
			/>

			{showAdvanced &&
				<>
					<TextControl
						label={<IconLabel icon={icons.id} label={__('Tracking code', 'eightshift-forms')} />}
						value={fileTracking}
						onChange={(value) => setAttributes({ [getAttrKey('fileTracking', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Accept', 'eightshift-forms')} />}
						value={fileAccept}
						onChange={(value) => setAttributes({ [getAttrKey('fileAccept', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Id', 'eightshift-forms')} />}
						value={fileId}
						onChange={(value) => setAttributes({ [getAttrKey('fileId', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.play}
						label={__('Is Multiple', 'eightshift-forms')}
						checked={fileIsMultiple}
						onChange={(value) => setAttributes({ [getAttrKey('fileIsMultiple', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Required', 'eightshift-forms')}
						checked={fileIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('fileIsRequired', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
