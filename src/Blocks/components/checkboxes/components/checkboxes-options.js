import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { TextControl, Button, PanelBody } from '@wordpress/components';
import {
	checkAttr,
	getAttrKey,
	props,
	IconLabel,
	icons,
	FancyDivider,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, MissingName } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

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
	const checkboxesDisabledOptions = checkAttr('checkboxesDisabledOptions', attributes, manifest);

	const [countInnerBlocks, setCountInnerBlocks] = useState(0);

	// Check if form selector has inner blocks.
	const countInnerBlocksCheck = useSelect((select) => {
		const { innerBlocks } = select('core/block-editor').getBlock(clientId);

		return innerBlocks.length;
	});

	// If parent block has inner blocks set internal state.
	useEffect(() => {
		setCountInnerBlocks(countInnerBlocksCheck);
	}, [countInnerBlocksCheck]);

	return (
		<>
			<PanelBody title={__('Checkboxes', 'eightshift-forms')}>
				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: checkboxesDisabledOptions,
					})}
				/>

				<FancyDivider label={__('Validation', 'eightshift-forms')} />

				<Button
					icon={icons.fieldRequired}
					isPressed={checkboxesIsRequired}
					onClick={() => {
						const value = !checkboxesIsRequired;

						setAttributes({ [getAttrKey('checkboxesIsRequired', attributes, manifest)]: value });

						if (!value) {
							setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: 1 });
						}
					}}
					disabled={isOptionDisabled(getAttrKey('checkboxesIsRequired', attributes, manifest), checkboxesDisabledOptions)}
				>
					{__('Required', 'eightshift-forms')}
				</Button>

				{checkboxesIsRequired &&
					<>
						<div className='es-h-spaced es-has-wp-field-t-space'>
							<span>Min.</span>
							<TextControl
								value={checkboxesIsRequiredCount}
								onChange={(value) => setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: value })}
								min={options.checkboxesIsRequiredCount.min}
								max={countInnerBlocks}
								type='number'
								className='es-no-field-spacing'
								disabled={isOptionDisabled(getAttrKey('checkboxesIsRequiredCount', attributes, manifest), checkboxesDisabledOptions)}
							/>
							<span>{checkboxesIsRequiredCount > 1 ? __('items need to be selected', 'eightshift-forms') : __('item needs to be checked', 'eightshift-forms')}</span>
						</div>
					</>
				}

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
					help={__('Should be unique! Used to identify the field within form submission data.', 'eightshift-forms')}
					value={checkboxesName}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxesName', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('checkboxesName', attributes, manifest), checkboxesDisabledOptions)}
				/>

				<MissingName value={checkboxesName} />
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: checkboxesName,
				})}
			/>
		</>
	);
};
