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
import { FieldOptions } from '../../../components/field/components/field-options';
import manifest from '../manifest.json';

export const SelectOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const selectName = checkAttr('selectName', attributes, manifest);
	const selectIsDisabled = checkAttr('selectIsDisabled', attributes, manifest);
	const selectIsRequired = checkAttr('selectIsRequired', attributes, manifest);
	const selectTracking = checkAttr('selectTracking', attributes, manifest);

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
						value={selectName}
						onChange={(value) => setAttributes({ [getAttrKey('selectName', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Tracking Code', 'eightshift-forms')} />}
						help={__('Provide GTM tracking code.', 'eightshift-forms')}
						value={selectTracking}
						onChange={(value) => setAttributes({ [getAttrKey('selectTracking', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.play}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={selectIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('selectIsDisabled', attributes, manifest)]: value })}
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
						checked={selectIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('selectIsRequired', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
