import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { TextControl, RangeControl } from '@wordpress/components';
import {
	checkAttr,
	getAttrKey,
	props,
	ComponentUseToggle,
	IconToggle
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';

export const CheckboxesOptions = (attributes) => {
	const {
		options,
	} = manifest;

	const {
		setAttributes,
		clientId,
	} = attributes;

	const checkboxesName = checkAttr('checkboxesName', attributes, manifest);
	const checkboxesIsRequired = checkAttr('checkboxesIsRequired', attributes, manifest);
	const checkboxesIsRequiredCount = checkAttr('checkboxesIsRequiredCount', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);
	const [showValidation, setShowValidation] = useState(false);
	const [countInnerBlocks, setCountInnerBlocks] = useState(0);

	// Check if form selector has inner blocks.
	const countInnerBlocksCheck = useSelect((select) => {
		const {innerBlocks} = select('core/block-editor').getBlock(clientId);

		return innerBlocks.length;
	});

	// If parent block has inner blocks set internal state.
	useEffect(() => {
		setCountInnerBlocks(countInnerBlocksCheck);
	}, [countInnerBlocksCheck]);

	const requiredCountDefaultValue = manifest.attributes.checkboxesIsRequiredCount.default;

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
						value={checkboxesName}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxesName', attributes, manifest)]: value })}
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
						checked={checkboxesIsRequired}
						onChange={(value) => {
							setAttributes({ [getAttrKey('checkboxesIsRequired', attributes, manifest)]: value });

							if (!value) {
								setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: requiredCountDefaultValue });
							}
						}}
					/>

					{checkboxesIsRequired &&
						<RangeControl
							label={__('Set minimal number of required boxes', 'eightshift-forms')}
							allowReset={true}
							value={checkboxesIsRequiredCount}
							onChange={(value) => setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: value })}
							min={options.checkboxesIsRequiredCount.min}
							max={countInnerBlocks}
							step={options.checkboxesIsRequiredCount.step}
							resetFallbackValue={requiredCountDefaultValue}
						/>
					}
				</>
			}

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>
		</>
	);
};
