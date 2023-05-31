/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { TextControl, PanelBody, Button, Modal } from '@wordpress/components';
import { icons, getAttrKey, checkAttr, IconToggle, Select, Control, Section, IconLabel } from '@eightshift/frontend-libs/scripts';
import { CONDITIONAL_TAGS_OPERATORS_LABELS, CONDITIONAL_TAGS_LOGIC_LABELS } from '../../conditional-tags/components/conditional-tags-labels';
import { getConstantsOptions } from '../../utils';
import manifest from '../manifest.json';

export const StepMultiflowOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const stepMultiflowUse = checkAttr('stepMultiflowUse', attributes, manifest);
	const stepMultiflowAction = checkAttr('stepMultiflowAction', attributes, manifest);
	const stepMultiflowLogic = checkAttr('stepMultiflowLogic', attributes, manifest);
	const stepMultiflowRules = checkAttr('stepMultiflowRules', attributes, manifest);
	const stepMultiflowPostId = checkAttr('stepMultiflowPostId', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [isNewRuleAdded, setIsNewRuleAdded] = useState(false);
	const [formFields, setFormFields] = useState([]);
	const [formSteps, setFormSteps] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.formFields}/?id=${stepMultiflowPostId}&useMultiflow=true` }).then((response) => {
			if (response.code === 200 && response.data) {
				setFormFields(response.data.fields);
				setFormSteps(response.data.steps);
			}
		});
	}, [stepMultiflowPostId, isModalOpen]);

	// console.log(formFields);
	// console.log(formSteps);

	const ConditionalTagsItem = ({ index }) => {
		if (!formFields) {
			return null;
		}

		if (!formSteps) {
			return null;
		}

		const operatorValue = stepMultiflowRules?.[index]?.[1] ?? 'is';
		const fieldValue = stepMultiflowRules?.[index]?.[0];

		// Internal state due to rerendering issue.
		const [inputCheck, setInputCheck] = useState(stepMultiflowRules?.[index]?.[2]);

		const options = formFields?.find((item) => item.value === stepMultiflowRules[index][0])?.subItems ?? [];

		const showRuleValuePicker = options?.length > 0 && (operatorValue === 'is' || operatorValue === 'isn');

		return (
			<>
				<div className='es-display-flex es-items-baseline es-gap-2 es-mb-6'>
					<span>{__('Go to', 'eightshift-forms')}</span>
					<Select
						value={stepMultiflowAction}
						options={formSteps}
						onChange={(value) => {
							stepMultiflowRules[index][0] = value;
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						noBottomSpacing
						simpleValue
						noSearch
					/>
					<span>{__('step if ', 'eightshift-forms')}</span>

					<span>{__('of the following match:', 'eightshift-forms')}</span>
				</div>

				<div className='es-h-spaced es-pb-2 es-mb-2 es-border-b-cool-gray-300'>
					<span className='es-w-40'>{__('Field', 'eightshift-forms')}</span>
					<span className='es-w-40'>{__('Condition', 'eightshift-forms')}</span>
					<span className='es-w-40'>{__('Value', 'eightshift-forms')}</span>
					<span className='es-w-40'>{__('Operator', 'eightshift-forms')}</span>
				</div>

				{stepMultiflowRules?.map((_, index) => {
					return(
						<div>
						<Select
							value={fieldValue}
							options={formFields}
							onChange={(value) => {
								stepMultiflowRules[index][0] = value;
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
							}}
							noBottomSpacing
							simpleValue
							noSearch
							additionalSelectClasses='es-w-40'
						/>

						<Select
							value={operatorValue}
							options={getConstantsOptions(CONDITIONAL_TAGS_OPERATORS_LABELS)}
							onChange={(value) => {
								stepMultiflowRules[index][1] = value;
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
							}}
							noBottomSpacing
							simpleValue
							noSearch
							additionalSelectClasses='es-w-40'
						/>

						{!showRuleValuePicker &&
							<TextControl
								value={inputCheck}
								onBlur={() => setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] })}
								onChange={(value) => {
									stepMultiflowRules[index][2] = value;
									setInputCheck(value);
								}}
								className='es-w-40 es-m-0-bcf!'
							/>
						}

						{showRuleValuePicker &&
							<Select
								value={stepMultiflowRules?.[index]?.[2]}
								options={options}
								onChange={(value) => {
									stepMultiflowRules[index][2] = value;
									setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
								}}
								noBottomSpacing
								simpleValue
								noSearch
								additionalSelectClasses='es-w-40'
							/>
						}

						<Select
							value={stepMultiflowLogic}
							options={getConstantsOptions(CONDITIONAL_TAGS_LOGIC_LABELS)}
							onChange={(value) => {
								stepMultiflowRules[index][3] = value;
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
							}}
							noBottomSpacing
							simpleValue
							noSearch
						/>
					</div>
					);
				})}
			</>
		);
	};

	return (
		<>
		{(formFields?.length < 1) ? 
			<PanelBody>
				<Control
					icon={icons.anchor}
					label={__('Multi-flow setup', 'eightshift-forms')}
					additionalLabelClasses='es-font-weight-500'
					noBottomSpacing
				>
					<IconLabel
						icon={icons.warningFillTransparent}
						label={__('Feature unavailable', 'eightshift-forms')}
						subtitle={__('No fields have a name set or you are missing step blocks.', 'eightshift-forms')}
						additionalClasses='es-nested-color-yellow-500!'
						addSubtitleGap
						standalone
					/>
				</Control>
			</PanelBody> :
			<PanelBody>
				<IconToggle
					icon={icons.anchor}
					label={__('Use steps multi-flow', 'eightshift-forms')}
					checked={stepMultiflowUse}
					onChange={(value) => {
						setAttributes({ [getAttrKey('stepMultiflowUse', attributes, manifest)]: value });

						if (!value) {
							// setAttributes({ [getAttrKey('stepMultiflowAction', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: undefined });
						} else {
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [] });
						}
					}}
					noBottomSpacing={!stepMultiflowUse}
					additionalClasses='es-font-weight-500'
				/>

				<Section showIf={stepMultiflowUse} noBottomSpacing>
					<Control
						icon={icons.conditionH}
						label={__('Rules', 'eightshift-forms')}
						// Translators: %d refers to the number of active rules
						subtitle={stepMultiflowRules?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), stepMultiflowRules.length)}
						noBottomSpacing
						inlineLabel
					>
						<Button
							variant='tertiary'
							onClick={() => setIsModalOpen(true)}
							className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
						>
							{stepMultiflowRules?.length > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
						</Button>
					</Control>
				</Section>

				{isModalOpen &&
					<Modal
						overlayClassName='es-conditional-tags-modal es-geolocation-modal'
						className='es-modal-max-width-3xl es-rounded-3!'
						title={<IconLabel icon={icons.anchor} label={__('Multi-flow setup', 'eightshift-forms')} standalone />}
						onRequestClose={() => {
							setIsModalOpen(false);
							setIsNewRuleAdded(false);
						}}
					>
						<div className='es-v-spaced'>
							{stepMultiflowRules?.map((_, index) => {

								// Remove condition if field value is missing.
								const itemExists = formFields?.filter((item) => {
									return stepMultiflowRules?.[index]?.[0] === item?.value && item?.value !== '';
								});

								if (!itemExists.length && !isNewRuleAdded) {
									stepMultiflowRules.splice(index, 1);
									setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
									return null;
								}

								return (
									<div key={index}>
										<ConditionalTagsItem index={index} />

										<Button
											icon={icons.trash}
											onClick={() => {
												stepMultiflowRules.splice(index, 1);
												setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
											}}
											label={__('Remove', 'eightshift-forms')}
											className='es-ml-auto es-rounded-1!'
										/>
									</div>
								);
							})}
						</div>

						<Button
							icon={icons.plusCircleFillAlt}
							onClick={() => {
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules, [formFields?.[0]?.value ?? '', 'is', '', '']] });
								setIsNewRuleAdded(true);
							}}
							className='es-rounded-1 es-mt-4'
						>
							{__('Add rule', 'eightshift-forms')}
						</Button>

						<div className='es-mt-8 -es-mx-8 es-px-8 es-pt-8 es-border-t-cool-gray-100 es-h-between es-gap-8!'>
							<IconLabel
								icon={icons.lightBulb}
								label={__('If you can\'t find a field, make sure the form is saved, and all fields have a name set.', 'eightshift-forms')}
								additionalClasses='es-nested-color-yellow-500!'
								standalone
							/>

							<Button
								variant='primary'
								onClick={() => {
									setIsModalOpen(false);
									setIsNewRuleAdded(false);
								}}
								className='es-rounded-1.5!'
							>
								{__('Save', 'eightshift-forms')}
							</Button>
						</div>
					</Modal>
				}
			</PanelBody>
		}
		</>
	);
}
