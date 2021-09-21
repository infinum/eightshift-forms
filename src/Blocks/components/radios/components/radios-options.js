import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, ToggleControl } from '@wordpress/components';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import manifest from '../manifest.json';

export const RadiosOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const radiosName = checkAttr('radiosName', attributes, manifest);
	const radiosIsRequired = checkAttr('radiosIsRequired', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>

			<ToggleControl
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
			/>

			{showAdvanced &&
				<>
					<TextControl
						label={__('Name', 'eightshift-forms')}
						value={radiosName}
						onChange={(value) => setAttributes({ [getAttrKey('radiosName', attributes, manifest)]: value })}
					/>

					<ToggleControl
						label={__('Is Required', 'eightshift-forms')}
						checked={radiosIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('radiosIsRequired', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
