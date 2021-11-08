/* global esFormsBlocksLocalization */

import React from 'react';
import { isObject } from 'lodash';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, SelectControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	ComponentUseToggle
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FieldOptionsAdvanced = (attributes) => {
	const {
		blockName,
		setAttributes,
	} = attributes;

	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldStyle = checkAttr('fieldStyle', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	let fieldStyleOptions = [];

	if (typeof esFormsBlocksLocalization !== 'undefined' && isObject(esFormsBlocksLocalization?.fieldBlockStyleOptions)) {
		fieldStyleOptions = esFormsBlocksLocalization.fieldBlockStyleOptions[blockName];
	}

	return (
		<>
			<ComponentUseToggle
				label={__('Show field options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
				showUseToggle={true}
				showLabel={true}
			/>

			{showAdvanced &&
				<>
					{fieldStyleOptions &&
						<SelectControl
							label={<IconLabel icon={icons.color} label={__('Style', 'eightshift-forms')} />}
							help={__('Set what style type is your form.', 'eightshift-forms')}
							value={fieldStyle}
							options={fieldStyleOptions}
							onChange={(value) => setAttributes({ [getAttrKey('fieldStyle', attributes, manifest)]: value })}
						/>
					}

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Help', 'eightshift-forms')} />}
						help={__('Set field help info text.', 'eightshift-forms')}
						value={fieldHelp}
						onChange={(value) => setAttributes({ [getAttrKey('fieldHelp', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Before Content', 'eightshift-forms')} />}
						help={__('Set some additional text before main field content.', 'eightshift-forms')}
						value={fieldBeforeContent}
						onChange={(value) => setAttributes({ [getAttrKey('fieldBeforeContent', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('After Content', 'eightshift-forms')} />}
						help={__('Set some additional text after main field content.', 'eightshift-forms')}
						value={fieldAfterContent}
						onChange={(value) => setAttributes({ [getAttrKey('fieldAfterContent', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
