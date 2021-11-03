import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	props,
	ComponentUseToggle
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';

export const FileOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileAccept = checkAttr('fileAccept', attributes, manifest);
	const fileIsMultiple = checkAttr('fileIsMultiple', attributes, manifest);
	const fileIsRequired = checkAttr('fileIsRequired', attributes, manifest);
	const fileTracking = checkAttr('fileTracking', attributes, manifest);
	const fileMinSize = checkAttr('fileMinSize', attributes, manifest);
	const fileMaxSize = checkAttr('fileMaxSize', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);
	const [showValidation, setShowValidation] = useState(false);

	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>

			<ComponentUseToggle
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
				showUseToggle={true}
				showLabel={true}
			/>

			{showAdvanced &&
				<>
					<TextControl
						label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
						help={__('Set unique field name. If not set field will have an generic name.', 'eightshift-forms')}
						value={fileName}
						onChange={(value) => setAttributes({ [getAttrKey('fileName', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Tracking code', 'eightshift-forms')} />}
						help={__('Provide GTM tracking code.', 'eightshift-forms')}
						value={fileTracking}
						onChange={(value) => setAttributes({ [getAttrKey('fileTracking', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.play}
						label={__('Is Multiple', 'eightshift-forms')}
						checked={fileIsMultiple}
						onChange={(value) => setAttributes({ [getAttrKey('fileIsMultiple', attributes, manifest)]: value })}
					/>
				</>
			}

			<ComponentUseToggle
				label={__('Show validation options', 'eightshift-forms')}
				checked={showValidation}
				onChange={() => setShowValidation(!showValidation)}
				showUseToggle={true}
				showLabel={true}
			/>

			{showValidation &&
				<>
					<IconToggle
						icon={icons.play}
						label={__('Is Required', 'eightshift-forms')}
						checked={fileIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('fileIsRequired', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Accept', 'eightshift-forms')} />}
						value={fileAccept}
						help={__('Use comma as separator. Example: .jpg,.png,.pdf', 'eightshift-forms')}
						onChange={(value) => setAttributes({ [getAttrKey('fileAccept', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Min Size', 'eightshift-forms')} />}
						help={__('Min size of the file in kilobytes.', 'eightshift-forms')}
						value={fileMinSize}
						type={'number'}
						onChange={(value) => setAttributes({ [getAttrKey('fileMinSize', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Max Size', 'eightshift-forms')} />}
						help={__('Max size of the file in kilobytes.', 'eightshift-forms')}
						value={fileMaxSize}
						type={'number'}
						onChange={(value) => setAttributes({ [getAttrKey('fileMaxSize', attributes, manifest)]: value })}
					/>

				</>
			}

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>
		</>
	);
};
