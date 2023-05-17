/* global esFormsLocalization */

import React, { useState, useEffect } from 'react';
import { isEmpty } from 'lodash';
import { __, sprintf } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { TextControl, PanelBody, Button, Modal } from '@wordpress/components';
import { icons, getAttrKey, checkAttr, IconToggle, IconLabel, Select, Control, Section } from '@eightshift/frontend-libs/scripts';
import { CONDITIONAL_TAGS_OPERATORS_INTERNAL } from './conditional-tags-utils';
import { getConstantsOptions } from '../../utils';
import manifest from '../manifest.json';
import { CONDITIONAL_TAGS_ACTIONS, CONDITIONAL_TAGS_OPERATORS } from '../../form/assets/utilities';

export const ConditionalTagsOptions = (attributes) => {
	const {
		setAttributes,
		blockName,
	} = attributes;

	const postId = select('core/editor').getCurrentPostId();

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsRules = checkAttr('conditionalTagsRules', attributes, manifest);
	const conditionalTagsBlockName = checkAttr('conditionalTagsBlockName', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [isNewRuleAdded, setIsNewRuleAdded] = useState(false);
	const [formFields, setFormFields] = useState([]);

	// Reset old conditional tags to new one, object based.
	if (isEmpty(conditionalTagsRules)) {
		setAttributes({ [getAttrKey('conditionalTagsUse', attributes, manifest)]: false });
		setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: undefined });
	}

	useEffect(() => {
		apiFetch({ path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.formFields}/?id=${postId}` }).then((response) => {
			if (response.code === 200 && response.data) {
				setFormFields(response.data.fields);
			}
		});
	}, [isModalOpen, postId]);

	const ConditionalTagsType = ({ type }) => {
		if (!formFields) {
			return null;
		}

		const formFieldOptionsItem = formFields?.find((item) => item.type === blockName)?.subItems ?? [];

		return (
			<>
				{conditionalTagsRules[type].length > 0 &&
					<div className={`es-h-spaced es-pb-2 es-mb-2 es-border-b-cool-gray-300 ${type === CONDITIONAL_TAGS_ACTIONS.HIDE && 'es-mt-10'}`}>
						{(formFieldOptionsItem.length > 0) ?
							<>
								<span className='es-w-40'>{sprintf(__('%1$s "%2$s"', 'eightshift-forms'), type[0].toUpperCase() + type.slice(1), conditionalTagsBlockName)}</span>
								<span className='es-w-40'>{__('if field', 'eightshift-forms')}</span>
							</> :
							<span className='es-w-40'>{sprintf(__('%1$s "%2$s" if field', 'eightshift-forms'), type[0].toUpperCase() + type.slice(1), conditionalTagsBlockName)}</span>
						}
						<span className='es-w-40'>{__('with operator', 'eightshift-forms')}</span>
						<span className='es-w-40'>{__('value', 'eightshift-forms')}</span>
					</div>
				}

				{conditionalTagsRules?.[type]?.map((_, index) => {
					return (
						<>
							{(conditionalTagsRules?.[type]?.length > 1 && index > 0) &&
								<div className='es-font-weight-700 es-mt-3'>
									{__('OR', 'eightshift-forms')}
								</div>
							}

							{conditionalTagsRules?.[type]?.[index]?.map((_, innerIndex) => {
								const itemExists = formFields?.filter((item) => {
									return conditionalTagsRules?.[type]?.[index]?.[innerIndex]?.[0] === item?.value && item?.value !== '';
								});

								if (!itemExists.length && !isNewRuleAdded) {
									conditionalTagsRules[type][index].splice(innerIndex, 1);

									if (conditionalTagsRules[type][index].length === 0) {
										conditionalTagsRules[type].splice(index, 1);
									}

									setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {...conditionalTagsRules} });
									return null;
								}

								return (
									<div className='es-h-spaced'>
										<ConditionalTagsItem parent={index} index={innerIndex} type={type} total={conditionalTagsRules[type][index].length} />
									</div>
								);
							})}
						</>
					);
				})}

				<Button
					icon={icons.plusCircleFillAlt}
					onClick={() => {
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {
							...conditionalTagsRules,
							[type]: [
								...conditionalTagsRules[type],
								[
									[formFields?.[0]?.value ?? '', CONDITIONAL_TAGS_OPERATORS.IS, '', '']
								],
							]
						}});
						setIsNewRuleAdded(true);
					}}
					className='es-rounded-1 es-mt-4'
				>
					{sprintf(__('Add "%s" rule', 'eightshift-forms'), type)}
				</Button>
			</>
		);
	}

	const ConditionalTagsItem = ({ parent, index, type, total }) => {
		if (!formFields) {
			return null;
		}

		const operatorValue = conditionalTagsRules?.[type]?.[parent]?.[index]?.[1] ?? CONDITIONAL_TAGS_OPERATORS.IS;
		const fieldValue = conditionalTagsRules?.[type]?.[parent]?.[index]?.[0];

		// Internal state due to rerendering issue.
		const [inputCheck, setInputCheck] = useState(conditionalTagsRules?.[type]?.[parent]?.[index]?.[2]);

		const formFieldOptions = formFields?.find((item) => item.value === conditionalTagsRules[type][parent][index][0])?.subItems ?? [];
		const formFieldOptionsItem = formFields?.find((item) => item.type === blockName)?.subItems ?? [];
		const showRuleValuePicker = formFieldOptions?.length > 0 && (operatorValue === CONDITIONAL_TAGS_OPERATORS.IS || operatorValue === CONDITIONAL_TAGS_OPERATORS.ISN);

		return (
			<>
				{formFieldOptionsItem.length > 0 &&
					<Select
						value={conditionalTagsRules?.[type]?.[parent]?.[index]?.[3]}
						options={formFieldOptionsItem.map((item) => {
							if (item.value === '' ) {
								return {
									...item,
									label: __('All fields', 'eightshift-forms'),
								};
							}
							return item;
						})}
						onChange={(value) => {
							conditionalTagsRules[type][parent][index][3] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {...conditionalTagsRules} });
						}}
						noBottomSpacing
						simpleValue
						noSearch
						additionalSelectClasses='es-w-40'
					/>
				}

				<Select
					value={fieldValue}
					options={formFields.filter((item) => {
						// Remove current field from selection.
						if (item.value !== conditionalTagsBlockName) {
							return item
						}
					})}
					onChange={(value) => {
						conditionalTagsRules[type][parent][index][0] = value;
						conditionalTagsRules[type][parent][index][2] = '';
						conditionalTagsRules[type][parent][index][3] = '';
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {...conditionalTagsRules} });
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
						conditionalTagsRules[type][parent][index][1] = value;
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {...conditionalTagsRules} });
					}}
					noBottomSpacing
					simpleValue
					noSearch
					additionalSelectClasses='es-w-40'
				/>


				{!showRuleValuePicker ?
					<TextControl
						value={inputCheck}
						onBlur={() => setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {...conditionalTagsRules} })}
						onChange={(value) => {
							conditionalTagsRules[type][parent][index][2] = value;
							setInputCheck(value);
						}}
						className='es-w-40 es-m-0-bcf!'
					/> :
					<Select
						value={conditionalTagsRules?.[type]?.[parent]?.[index]?.[2]}
						options={formFieldOptions}
						onChange={(value) => {
							conditionalTagsRules[type][parent][index][2] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {...conditionalTagsRules} });
						}}
						noBottomSpacing
						simpleValue
						noSearch
						additionalSelectClasses='es-w-40'
					/>
				}

				{(total === index + 1) &&
					<Button
						icon={icons.plusCircleFillAlt}
						onClick={() => {
							conditionalTagsRules[type][parent][index + 1] = [formFields?.[0]?.value ?? '', CONDITIONAL_TAGS_OPERATORS.IS, '', ''];
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {...conditionalTagsRules} });

							setIsNewRuleAdded(true);
						}}
						className='es-rounded-1'
					>
						{sprintf(__('AND', 'eightshift-forms'), type)}
					</Button>
				}

				<Button
					icon={icons.trash}
					onClick={() => {
						conditionalTagsRules[type][parent].splice(index, 1);

						if (conditionalTagsRules[type][parent].length === 0) {
							conditionalTagsRules[type].splice(parent, 1);
						}
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {...conditionalTagsRules} });
					}}
					label={__('Remove', 'eightshift-forms')}
					className='es-ml-auto es-rounded-1!'
				/>
			</>
		);
	};

	const hideCount = conditionalTagsRules?.[CONDITIONAL_TAGS_ACTIONS.HIDE]?.length && conditionalTagsRules?.[CONDITIONAL_TAGS_ACTIONS.HIDE]?.flat()?.length;
	const showCount = conditionalTagsRules?.[CONDITIONAL_TAGS_ACTIONS.SHOW]?.length && conditionalTagsRules?.[CONDITIONAL_TAGS_ACTIONS.SHOW]?.flat()?.length;

	return (
		<>
		{formFields?.length < 1 ?
			<PanelBody>
				<Control
					icon={icons.conditionalVisibility}
					label={__('Conditional visibility', 'eightshift-forms')}
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
			</PanelBody> :
			<PanelBody>
				<IconToggle
					icon={icons.conditionalVisibility}
					label={__('Conditional visibility', 'eightshift-forms')}
					checked={conditionalTagsUse}
					onChange={(value) => {
						setAttributes({ [getAttrKey('conditionalTagsUse', attributes, manifest)]: value });

						if (!value) {
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: undefined });
						} else {
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: {
								[CONDITIONAL_TAGS_ACTIONS.SHOW]: [],
								[CONDITIONAL_TAGS_ACTIONS.HIDE]: [],
							}});
						}
					}}
					noBottomSpacing={!conditionalTagsUse}
					additionalClasses='es-font-weight-500'
				/>
	
				<Section showIf={conditionalTagsUse} noBottomSpacing>
					<Control
						icon={icons.conditionH}
						label={__('Rules', 'eightshift-forms')}
						// Translators: %d refers to the number of active rules
						subtitle={(showCount > 0 || hideCount > 0) && sprintf(__('%1$d show, %2$d hide', 'eightshift-forms'), showCount, hideCount)}
						noBottomSpacing
						inlineLabel
					>
						<Button
							variant='tertiary'
							onClick={() => setIsModalOpen(true)}
							className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
						>
							{(showCount > 0 || hideCount > 0) ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
						</Button>
					</Control>
	
					{isModalOpen &&
						<Modal
							overlayClassName='es-conditional-tags-modal es-geolocation-modal'
							className='es-modal-max-width-3xl es-rounded-3!'
							title={<IconLabel icon={icons.conditionalVisibility} label={__('Conditional visibility', 'eightshift-forms')} standalone />}
							onRequestClose={() => {
								setIsModalOpen(false);
								setIsNewRuleAdded(false);
							}}
						>
							<div className='es-v-spaced'>
								<ConditionalTagsType type={CONDITIONAL_TAGS_ACTIONS.SHOW} />
								<ConditionalTagsType type={CONDITIONAL_TAGS_ACTIONS.HIDE} />
							</div>

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
		}
		</>
	);
};
