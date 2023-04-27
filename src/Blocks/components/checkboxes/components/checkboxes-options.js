import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __, _n } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, props, icons, Section, IconToggle, AnimatedContentVisibility } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, NameFieldLabel } from './../../utils';
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
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={checkboxesName} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={checkboxesName}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxesName', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('checkboxesName', attributes, manifest), checkboxesDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: checkboxesDisabledOptions,
					})}
					additionalControls={<FieldOptionsAdvanced {...props('field', attributes)} />}
				/>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
					<IconToggle
						icon={icons.required}
						label={__('Required', 'eightshift-forms')}
						checked={checkboxesIsRequired}
						onChange={(value) => {
							setAttributes({ [getAttrKey('checkboxesIsRequired', attributes, manifest)]: value });

							if (!value) {
								setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: 1 });
							}
						}}
						reducedBottomSpacing={checkboxesIsRequired}
						noBottomSpacing={!checkboxesIsRequired}
					/>

					<AnimatedContentVisibility showIf={checkboxesIsRequired}>
						<div className='es-h-spaced'>
							<span>{__('At least', 'eightshift-forms')}</span>
							<TextControl
								value={checkboxesIsRequiredCount}
								onChange={(value) => setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: value })}
								min={options.checkboxesIsRequiredCount.min}
								max={countInnerBlocks}
								type='number'
								className='es-no-field-spacing'
								disabled={isOptionDisabled(getAttrKey('checkboxesIsRequiredCount', attributes, manifest), checkboxesDisabledOptions)}
							/>
							<span>{_n(__('item needs to be checked', 'eightshift-forms'), __('items need to be checked', 'eightshift-forms'), checkboxesIsRequiredCount, 'eightshift-forms')}</span>
						</div>
					</AnimatedContentVisibility>
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: checkboxesName,
				})}
			/>
		</>
	);
};
