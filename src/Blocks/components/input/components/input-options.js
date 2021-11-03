import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, SelectControl } from '@wordpress/components';
import {
	icons,
	getOption,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	props,
	ComponentUseToggle
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from './../manifest.json';

export const InputOptions = (attributes) => {
	const {
		setAttributes,

		showInputName = true,
		showInputValue = true,
		showInputAdvancedOptions = true,
		showInputPlaceholder = true,
		showInputType = true,
		showInputValidationOptions = true,
		showInputIsDisabled = true,
		showInputIsReadOnly = true,
		showInputIsRequired = true,
		showInputTracking = true,
		showInputIsEmail = true,
		showInputIsUrl = true,
	} = attributes;

	const inputName = checkAttr('inputName', attributes, manifest);
	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	const inputType = checkAttr('inputType', attributes, manifest);
	const inputIsDisabled = checkAttr('inputIsDisabled', attributes, manifest);
	const inputIsReadOnly = checkAttr('inputIsReadOnly', attributes, manifest);
	const inputIsRequired = checkAttr('inputIsRequired', attributes, manifest);
	const inputTracking = checkAttr('inputTracking', attributes, manifest);
	const inputIsEmail = checkAttr('inputIsEmail', attributes, manifest);
	const inputIsUrl = checkAttr('inputIsUrl', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);
	const [showValidation, setShowValidation] = useState(false);

	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>

			{showInputPlaceholder &&
				<TextControl
					label={<IconLabel icon={icons.id} label={__('Placeholder', 'eightshift-forms')} />}
					help={__('Set text used as a placeholder before user starts typing.', 'eightshift-forms')}
					value={inputPlaceholder}
					onChange={(value) => setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: value })}
				/>
			}

			{showInputType &&
				<SelectControl
					label={<IconLabel icon={icons.id} label={__('Type', 'eightshift-forms')} />}
					help={__('Set what type of input filed it is used.', 'eightshift-forms')}
					value={inputType}
					options={getOption('inputType', attributes, manifest)}
					onChange={(value) => setAttributes({ [getAttrKey('inputType', attributes, manifest)]: value })}
				/>
			}

			{showInputAdvancedOptions &&
				<>
					<ComponentUseToggle
						label={__('Show advanced options', 'eightshift-forms')}
						checked={showAdvanced}
						onChange={() => setShowAdvanced(!showAdvanced)}
						showUseToggle={true}
						showLabel={true}
					/>

					{showAdvanced &&
						<>
							{showInputName &&
								<TextControl
									label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
									help={__('Set unique field name. If not set field will have an generic name.', 'eightshift-forms')}
									value={inputName}
									onChange={(value) => setAttributes({ [getAttrKey('inputName', attributes, manifest)]: value })}
								/>
							}

							{showInputValue &&
								<TextControl
									label={<IconLabel icon={icons.id} label={__('Value', 'eightshift-forms')} />}
									help={__('Provide value that is going to be preset for the field.', 'eightshift-forms')}
									value={inputValue}
									onChange={(value) => setAttributes({ [getAttrKey('inputValue', attributes, manifest)]: value })}
								/>
							}

							{showInputTracking &&
								<TextControl
									label={<IconLabel icon={icons.id} label={__('Tracking Code', 'eightshift-forms')} />}
									help={__('Provide GTM tracking code.', 'eightshift-forms')}
									value={inputTracking}
									onChange={(value) => setAttributes({ [getAttrKey('inputTracking', attributes, manifest)]: value })}
								/>
							}

							{showInputIsDisabled &&
								<IconToggle
									icon={icons.play}
									label={__('Is Disabled', 'eightshift-forms')}
									checked={inputIsDisabled}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsDisabled', attributes, manifest)]: value })}
								/>
							}

							{showInputIsReadOnly &&
								<IconToggle
									icon={icons.play}
									label={__('Is Read Only', 'eightshift-forms')}
									checked={inputIsReadOnly}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsReadOnly', attributes, manifest)]: value })}
								/>
							}
						</>
					}
				</>
			}

			{showInputValidationOptions &&
				<>
					<ComponentUseToggle
						label={__('Show validation options', 'eightshift-forms')}
						checked={showValidation}
						onChange={() => setShowValidation(!showValidation)}
						showUseToggle={true}
						showLabel={true}
					/>

					{showValidation &&
						<>
							{showInputIsRequired &&
								<IconToggle
									icon={icons.play}
									label={__('Is Required', 'eightshift-forms')}
									checked={inputIsRequired}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsRequired', attributes, manifest)]: value })}
								/>
							}

							{showInputIsEmail &&
								<IconToggle
									icon={icons.play}
									label={__('Is Email', 'eightshift-forms')}
									checked={inputIsEmail}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsEmail', attributes, manifest)]: value })}
								/>
							}

							{showInputIsUrl &&
								<IconToggle
									icon={icons.play}
									label={__('Is Url', 'eightshift-forms')}
									checked={inputIsUrl}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsUrl', attributes, manifest)]: value })}
								/>
							}
						</>
					}
				</>
			}

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>
		</>
	);
};
