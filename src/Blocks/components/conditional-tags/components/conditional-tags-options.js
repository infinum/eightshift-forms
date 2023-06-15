import React, { useState, useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { TextControl, PanelBody, Button, Modal } from '@wordpress/components';
import { icons, getAttrKey, checkAttr, IconToggle, IconLabel, Select, Control, Section } from '@eightshift/frontend-libs/scripts';
import { getConstantsOptions } from '../../utils';
import manifest from '../manifest.json';
import { CONDITIONAL_TAGS_ACTIONS, CONDITIONAL_TAGS_OPERATORS } from '../assets/utils';
import {
	CONDITIONAL_TAGS_ACTIONS_LABELS,
	CONDITIONAL_TAGS_ACTIONS_INVERSE_LABELS,
	CONDITIONAL_TAGS_OPERATORS_LABELS,
} from './conditional-tags-labels';
import { ROUTES, getRestUrl } from '../../form/assets/state';

export const ConditionalTagsOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const postId = select('core/editor').getCurrentPostId();

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [formFields, setFormFields] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${getRestUrl(ROUTES.FORM_FIELDS, true)}?id=${postId}` }).then((response) => {
			if (response.code === 200 && response.data) {
				setFormFields(response.data.fields);
			}
		});
	}, [isModalOpen, postId]);

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsRules = checkAttr('conditionalTagsRules', attributes, manifest);
	const conditionalTagsBlockName = checkAttr('conditionalTagsBlockName', attributes, manifest);

	const ConditionalTagsType = () => {
		if (!formFields) {
			return null;
		}

		return (
			<>
				<div>{sprintf(__('This field will be %s by default, but you can provide exception to this rule.', 'eightshift-forms'), CONDITIONAL_TAGS_ACTIONS_LABELS[conditionalTagsRules[0]])}</div>
				<Select
					value={conditionalTagsRules[0]}
					options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_LABELS)}
					onChange={(value) => {
						conditionalTagsRules[0] = value;
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
					}}
					noBottomSpacing
					simpleValue
					noSearch
					additionalSelectClasses='es-w-40'
				/>

				<div className='es-font-weight-700 es-mt-5'>
					{__('Set field exception rules', 'eightshift-forms')}
				</div>

				<div className='es-mb-2'>{sprintf(__('%s "%s" field if:', 'eightshift-forms'), CONDITIONAL_TAGS_ACTIONS_INVERSE_LABELS[conditionalTagsRules[0]], conditionalTagsBlockName)}</div>

				{conditionalTagsRules?.[1]?.map((_, index) => {
					const total = conditionalTagsRules[1].length;
					return (
						<>
							{conditionalTagsRules?.[1]?.[index]?.map((_, innerIndex) => {
								return (<ConditionalTagsItem key={innerIndex} parent={index} index={innerIndex} total={conditionalTagsRules[1][index].length} />);
							})}

							{(conditionalTagsRules?.[1]?.length > 1 && (index + 1) < total) &&
								<div className='es-font-weight-700'>
									{__('OR', 'eightshift-forms')}
								</div>
							}
						</>
					);
				})}

				<Button
					icon={icons.plusCircleFillAlt}
					onClick={() => {
						conditionalTagsRules[1].push([[formFields?.[0]?.value ?? '', CONDITIONAL_TAGS_OPERATORS.IS, '']]);
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
					}}
					className='es-rounded-1 es-mt-4'
				>
					{(__('Add exception rule', 'eightshift-forms'))}
				</Button>
			</>
		);
	};

	const ConditionalTagsItem = ({ parent, index, total }) => {
		if (!formFields) {
			return null;
		}

		const operatorValue = conditionalTagsRules?.[1]?.[parent]?.[index]?.[1] ?? CONDITIONAL_TAGS_OPERATORS.IS;
		const fieldValue = conditionalTagsRules?.[1]?.[parent]?.[index]?.[0];

		// Internal state due to rerendering issue.
		const [inputCheck, setInputCheck] = useState(conditionalTagsRules?.[1]?.[parent]?.[index]?.[2]);

		const formFieldOptions = formFields?.find((item) => item.value === conditionalTagsRules[1][parent][index][0])?.subItems ?? [];
		const showRuleValuePicker = formFieldOptions?.length > 0 && (operatorValue === CONDITIONAL_TAGS_OPERATORS.IS || operatorValue === CONDITIONAL_TAGS_OPERATORS.ISN);

		return (
			<div className='es-h-spaced'>

				<Select
					value={fieldValue}
					options={formFields.filter((item) => {
						// Remove current field from selection.
						if (item.value !== conditionalTagsBlockName) {
							return item;
						}

						return null;
					})}
					onChange={(value) => {
						conditionalTagsRules[1][parent][index][0] = value;
						conditionalTagsRules[1][parent][index][2] = '';
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
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
						conditionalTagsRules[1][parent][index][1] = value;
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
					}}
					noBottomSpacing
					simpleValue
					noSearch
					additionalSelectClasses='es-w-40'
				/>

				<span>{'='}</span>
				{!showRuleValuePicker ?
					<TextControl
						value={inputCheck}
						onBlur={() => setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] })}
						onChange={(value) => {
							conditionalTagsRules[1][parent][index][2] = value;
							setInputCheck(value);
						}}
						className='es-w-40 es-m-0-bcf!'
					/> :
					<Select
						value={conditionalTagsRules?.[1]?.[parent]?.[index]?.[2]}
						options={formFieldOptions}
						onChange={(value) => {
							conditionalTagsRules[1][parent][index][2] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
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
							conditionalTagsRules[1][parent][index + 1] = [formFields?.[0]?.value ?? '', CONDITIONAL_TAGS_OPERATORS.IS, ''];
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
						}}
						className='es-rounded-1'
					>
						{__('AND', 'eightshift-forms')}
					</Button>
				}

				<Button
					icon={icons.trash}
					onClick={() => {
						conditionalTagsRules[1][parent].splice(index, 1);

						if (conditionalTagsRules[1][parent].length === 0) {
							conditionalTagsRules[1].splice(parent, 1);
						}
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
					}}
					label={__('Remove', 'eightshift-forms')}
					className='es-ml-auto es-rounded-1!'
				/>
			</div>
		);
	};

	const rulesCount = conditionalTagsRules?.[1]?.length && conditionalTagsRules?.[1]?.flat()?.length;

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
						subtitle={__('It looks like your field has a missing name.', 'eightshift-forms')}
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

							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: !value ? undefined : [CONDITIONAL_TAGS_ACTIONS.HIDE, []]});
					}}
					noBottomSpacing={!conditionalTagsUse}
					additionalClasses='es-font-weight-500'
				/>
	
				<Section showIf={conditionalTagsUse} noBottomSpacing>
					<Control
						icon={icons.conditionH}
						label={__('Rules', 'eightshift-forms')}
						// Translators: %d refers to the number of active rules
						subtitle={(rulesCount > 0) && sprintf(__('%d rules', 'eightshift-forms'), rulesCount)}
						noBottomSpacing
						inlineLabel
					>
						<Button
							variant='tertiary'
							onClick={() => setIsModalOpen(true)}
							className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
						>
							{(rulesCount > 0) ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
						</Button>
					</Control>
	
					{isModalOpen &&
						<Modal
							overlayClassName='es-conditional-tags-modal es-geolocation-modal'
							className='es-modal-max-width-5xl es-rounded-3!'
							title={<IconLabel icon={icons.conditionalVisibility} label={__('Conditional visibility', 'eightshift-forms')} standalone />}
							onRequestClose={() => setIsModalOpen(false)}
						>
							<div className='es-v-spaced'>
								<ConditionalTagsType />
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
									onClick={() => setIsModalOpen(false)}
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
