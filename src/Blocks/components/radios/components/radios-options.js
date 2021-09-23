import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey, props, ComponentUseToggle, IconToggle } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import manifest from '../manifest.json';

export const RadiosOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const radiosName = checkAttr('radiosName', attributes, manifest);
	const radiosIsRequired = checkAttr('radiosIsRequired', attributes, manifest);

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
						label={__('Name', 'eightshift-forms')}
						help={__('Set unique field name. If not set field will have an generic name.', 'eightshift-forms')}
						value={radiosName}
						onChange={(value) => setAttributes({ [getAttrKey('radiosName', attributes, manifest)]: value })}
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
						label={__('Is Required', 'eightshift-forms')}
						checked={radiosIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('radiosIsRequired', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
