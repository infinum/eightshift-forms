/* global esFormsLocalization */

import React, { useState, useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { TextControl, PanelBody, Button, Modal } from '@wordpress/components';
import { icons, getAttrKey, checkAttr, IconToggle, IconLabel, Select, Control, Section } from '@eightshift/frontend-libs/scripts';
import { CONDITIONAL_TAGS_OPERATORS_INTERNAL, CONDITIONAL_TAGS_ACTIONS_INTERNAL, CONDITIONAL_TAGS_LOGIC_INTERNAL } from './conditional-tags-utils';
import { getConstantsOptions } from '../../utils';
import manifest from '../manifest.json';

export const ConditionalTagsOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const postId = select('core/editor').getCurrentPostId();

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsAction = checkAttr('conditionalTagsAction', attributes, manifest);
	const conditionalTagsLogic = checkAttr('conditionalTagsLogic', attributes, manifest);
	const conditionalTagsRules = checkAttr('conditionalTagsRules', attributes, manifest);
	const conditionalTagsParentName = checkAttr('conditionalTagsParentName', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [isNewRuleAdded, setIsNewRuleAdded] = useState(false);
	const [formFields, setFormFields] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.formFields}/?id=${postId}` }).then((response) => {

			if (response.code === 200 && response.data) {
				setFormFields(response.data);
			}
		});
	}, [isModalOpen, postId]);

	if (formFields?.length < 1) {
		return (
			<PanelBody>
				<Control
					icon={icons.conditionalVisibility}
					label={__('Conditional visibility', 'eightshift-frontend-libs')}
					additionalLabelClasses='es-font-weight-500'
					noBottomSpacing
				>
					<IconLabel
						icon={icons.warningFillTransparent}
						label={__('Feature unavailable', 'eightshift-forms')}
						subtitle={__('No fields have a name set', 'eightshift-forms')}
						additionalClasses='es-nested-color-yellow-500!'
						addSubtitleGap
						standalone
					/>
				</Control>
			</PanelBody>
		);
	}

	const ConditionalTagsItem = ({ index }) => {
		if (!formFields) {
			return null;
		}

		const operatorValue = conditionalTagsRules?.[index]?.[1] ?? 'is';
		const fieldValue = conditionalTagsRules?.[index]?.[0];

		// Internal state due to rerendering issue.
		const [inputCheck, setInputCheck] = useState(conditionalTagsRules?.[index]?.[2]);

		const options = formFields?.find((item) => item.value === conditionalTagsRules[index][0])?.subItems ?? [];
		const optionsItem = formFields?.find((item) => item.value === conditionalTagsParentName)?.subItems ?? [];

		const showRuleValuePicker = options?.length > 0 && (operatorValue === 'is' || operatorValue === 'isn');

		return (
			<>
				<Select
					value={fieldValue}
					options={formFields}
					onChange={(value) => {
						conditionalTagsRules[index][0] = value;
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
					}}
					noBottomSpacing
					simpleValue
					noSearch
					additionalSelectClasses='es-w-40'
				/>

				<Select
					value={operatorValue}
					options={getConstantsOptions(CONDITIONAL_TAGS_OPERATORS_INTERNAL)}
					onChange={(value) => {
						conditionalTagsRules[index][1] = value;
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
					}}
					noBottomSpacing
					simpleValue
					noSearch
					additionalSelectClasses='es-w-40'
				/>

				{!showRuleValuePicker &&
					<TextControl
						value={inputCheck}
						onBlur={() => setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] })}
						onChange={(value) => {
							conditionalTagsRules[index][2] = value;
							setInputCheck(value);
						}}
						className='es-w-40 es-m-0-bcf!'
					/>
				}

				{showRuleValuePicker &&
					<Select
						value={conditionalTagsRules?.[index]?.[2]}
						options={options}
						onChange={(value) => {
							conditionalTagsRules[index][2] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
						}}
						noBottomSpacing
						simpleValue
						noSearch
						additionalSelectClasses='es-w-40'
					/>
				}

				{conditionalTagsRules?.[index]?.[3]?.length > 1 &&
					<Select
						value={conditionalTagsRules?.[index]?.[3]}
						options={optionsItem.map((item) => {
							if (item.value === '') {
								return {
									...item,
									label: __('All fields', 'eightshift-forms'),
								};
							}
							return item;
						})}
						onChange={(value) => {
							conditionalTagsRules[index][3] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
						}}
						noBottomSpacing
						simpleValue
						noSearch
						additionalSelectClasses='es-w-40'
					/>
				}
			</>
		);
	};

	const optionsItem = formFields?.find((item) => item.value === conditionalTagsParentName)?.subItems ?? [];

	return (
		<PanelBody>
			<IconToggle
				icon={icons.conditionalVisibility}
				label={__('Conditional visibility', 'eightshift-frontend-libs')}
				checked={conditionalTagsUse}
				onChange={(value) => {
					setAttributes({ [getAttrKey('conditionalTagsUse', attributes, manifest)]: value });

					if (!value) {
						setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('conditionalTagsLogic', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: undefined });
					} else {
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [] });
					}
				}}
				noBottomSpacing={!conditionalTagsUse}
				additionalClasses='es-font-weight-500'
			/>

			<Section showIf={conditionalTagsUse}>
				<Control
					icon={icons.conditionH}
					label={__('Rules', 'eightshift-forms')}
					// Translators: %d refers to the number of active rules
					subtitle={conditionalTagsRules?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), conditionalTagsRules.length)}
					noBottomSpacing
					inlineLabel
				>
					<Button
						variant='tertiary'
						onClick={() => setIsModalOpen(true)}
						className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
					>
						{conditionalTagsRules?.length > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
					</Button>
				</Control>

				{isModalOpen &&
					<Modal
						overlayClassName='es-conditional-tags-modal es-geolocation-modal'
						className='es-modal-max-width-xxl es-rounded-3!'
						title={<IconLabel icon={icons.conditionalVisibility} label={__('Conditional visibility', 'eightshift-forms')} standalone />}
						onRequestClose={() => {
							setIsModalOpen(false);
							setIsNewRuleAdded(false);
						}}
						isDismissible={false}
					>
						<div className='es-display-flex es-items-baseline es-gap-2 es-mb-6'>
							<Select
								value={conditionalTagsAction}
								options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_INTERNAL)}
								onChange={(value) => setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: value })}
								noBottomSpacing
								simpleValue
								noSearch
							/>
							<span>{__('this field if', 'eightshift-forms')}</span>
							<Select
								value={conditionalTagsLogic}
								options={getConstantsOptions(CONDITIONAL_TAGS_LOGIC_INTERNAL)}
								onChange={(value) => setAttributes({ [getAttrKey('conditionalTagsLogic', attributes, manifest)]: value })}
								noBottomSpacing
								simpleValue
								noSearch
							/>
							<span>{__('of the following match:', 'eightshift-forms')}</span>
						</div>

						<div className='es-h-spaced es-pb-2 es-mb-2 es-border-b-cool-gray-300'>
							<span className='es-w-40'>{__('Field', 'eightshift-forms')}</span>
							<span className='es-w-40'>{__('Condition', 'eightshift-forms')}</span>
							<span className='es-w-40'>{__('Value', 'eightshift-forms')}</span>
							{optionsItem.length === 3 &&
								<span className='es-w-40'>{__('Inner fields', 'eightshift-forms')}</span>
							}
						</div>

						<div className='es-v-spaced'>
							{conditionalTagsRules?.map((_, index) => {
								const itemExists = formFields?.filter((item) => {
									return conditionalTagsRules?.[index]?.[0] === item?.value && item?.value !== '';
								});

								if (!itemExists.length && !isNewRuleAdded) {
									conditionalTagsRules.splice(index, 1);
									setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
									return null;
								}

								return (
									<div key={index} className='es-h-spaced'>
										<ConditionalTagsItem index={index} />

										<Button
											icon={icons.trash}
											onClick={() => {
												conditionalTagsRules.splice(index, 1);
												setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
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
								setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules, [formFields?.[0]?.value ?? '', 'is', '', '']] });
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
			</Section>
		</PanelBody>
	);
};
