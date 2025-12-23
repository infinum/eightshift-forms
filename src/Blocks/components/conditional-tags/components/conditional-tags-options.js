import React, { useState, useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { Modal } from '@wordpress/components';
import { getAttrKey, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	BaseControl,
	Select,
	RichLabel,
	Notice,
	Button,
	InputField,
	Toggle,
	ContainerGroup,
	Spacer,
} from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import { getConstantsOptions } from '../../utils';
import {
	CONDITIONAL_TAGS_ACTIONS_LABELS,
	CONDITIONAL_TAGS_ACTIONS_INVERSE_LABELS,
	CONDITIONAL_TAGS_OPERATORS_LABELS,
} from './conditional-tags-labels';
import { getRestUrl } from '../../form/assets/state-init';
import globalManifest from '../../../manifest.json';
import manifest from '../manifest.json';

export const ConditionalTagsOptions = (attributes) => {
	const { setAttributes } = attributes;

	const postId = select('core/editor').getCurrentPostId();

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [formFields, setFormFields] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${getRestUrl('formFields', true)}?id=${postId}` }).then((response) => {
			if (response.code === 200 && response.data) {
				setFormFields(response.data.fields);
			}
		});
	}, [postId]);

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsRules = checkAttr('conditionalTagsRules', attributes, manifest);
	const conditionalTagsBlockName = checkAttr('conditionalTagsBlockName', attributes, manifest);
	const conditionalTagsIsHidden = checkAttr('conditionalTagsIsHidden', attributes, manifest);

	const conditionalTagsUseKey = getAttrKey('conditionalTagsUse', attributes, manifest);
	const conditionalTagsRulesKey = getAttrKey('conditionalTagsRules', attributes, manifest);

	const ConditionalTagsType = () => {
		if (!formFields) {
			return null;
		}

		return (
			<>
				<div>
					{sprintf(
						__('This field will be %s by default, but you can provide exception to this rule.', 'eightshift-forms'),
						CONDITIONAL_TAGS_ACTIONS_LABELS[conditionalTagsRules[0]],
					)}
				</div>
				<Select
					value={conditionalTagsRules[0]}
					options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_LABELS)}
					onChange={(value) => {
						conditionalTagsRules[0] = value;
						setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
					}}
					simpleValue
					noSearch
				/>

				<div>{__('Set field exception rules', 'eightshift-forms')}</div>

				<div>
					{sprintf(
						__('%s "%s" field if:', 'eightshift-forms'),
						CONDITIONAL_TAGS_ACTIONS_INVERSE_LABELS[conditionalTagsRules[0]],
						conditionalTagsBlockName,
					)}
				</div>

				{conditionalTagsRules?.[1]?.map((_, index) => {
					const total = conditionalTagsRules[1].length;

					return (
						<>
							{conditionalTagsRules?.[1]?.[index]?.map((_, innerIndex) => {
								return (
									<ConditionalTagsItem
										key={innerIndex}
										parent={index}
										index={innerIndex}
										total={conditionalTagsRules[1][index].length}
									/>
								);
							})}

							{conditionalTagsRules?.[1]?.length > 1 && index + 1 < total && <div>{__('OR', 'eightshift-forms')}</div>}
						</>
					);
				})}

				<Button
					icon={icons.plusCircleFillAlt}
					onClick={() => {
						conditionalTagsRules[1].push([[formFields?.[0]?.value ?? '', globalManifest.comparator.IS, '']]);
						setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
					}}
				>
					{__('Add exception rule', 'eightshift-forms')}
				</Button>
			</>
		);
	};

	const ConditionalTagsItem = ({ parent, index, total }) => {
		if (!formFields) {
			return null;
		}

		const operatorValue = conditionalTagsRules?.[1]?.[parent]?.[index]?.[1] ?? globalManifest.comparator.IS;
		const fieldValue = conditionalTagsRules?.[1]?.[parent]?.[index]?.[0];

		// Internal state due to rerendering issue.
		const [inputCheck, setInputCheck] = useState(conditionalTagsRules?.[1]?.[parent]?.[index]?.[2] ?? '');

		const formFieldOptions =
			formFields?.find((item) => item.value === conditionalTagsRules[1][parent][index][0])?.subItems ?? [];
		const showRuleValuePicker =
			formFieldOptions?.length > 0 &&
			(operatorValue === globalManifest.comparator.IS || operatorValue === globalManifest.comparator.ISN);

		return (
			<div>
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
						setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
					}}
					simpleValue
					noSearch
				/>

				<Select
					value={operatorValue}
					options={getConstantsOptions(CONDITIONAL_TAGS_OPERATORS_LABELS)}
					onChange={(value) => {
						conditionalTagsRules[1][parent][index][1] = value;
						setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
					}}
					simpleValue
					noSearch
				/>

				<span>{'='}</span>
				{!showRuleValuePicker ? (
					<InputField
						value={inputCheck}
						onBlur={() => setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] })}
						onChange={(value) => {
							conditionalTagsRules[1][parent][index][2] = value;
							setInputCheck(value);
						}}
					/>
				) : (
					<Select
						value={conditionalTagsRules?.[1]?.[parent]?.[index]?.[2]}
						options={formFieldOptions}
						onChange={(value) => {
							conditionalTagsRules[1][parent][index][2] = value;
							setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
						}}
						simpleValue
						noSearch
					/>
				)}

				{total === index + 1 && (
					<Button
						icon={icons.plusCircleFillAlt}
						onClick={() => {
							conditionalTagsRules[1][parent][index + 1] = [
								formFields?.[0]?.value ?? '',
								globalManifest.comparator.IS,
								'',
							];
							setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
						}}
					>
						{__('AND', 'eightshift-forms')}
					</Button>
				)}

				<Button
					icon={icons.trash}
					onClick={() => {
						conditionalTagsRules[1][parent].splice(index, 1);

						if (conditionalTagsRules[1][parent].length === 0) {
							conditionalTagsRules[1].splice(parent, 1);
						}
						setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
					}}
					label={__('Remove', 'eightshift-forms')}
				/>
			</div>
		);
	};

	const rulesCount = conditionalTagsRules?.[1]?.length && conditionalTagsRules?.[1]?.flat()?.length;

	return (
		<>
			<Spacer
				border
				icon={icons.conditionalVisibility}
				text={__('Conditional visibility', 'eightshift-forms')}
			/>
			<>
				{formFields?.length < 1 ? (
					<RichLabel
						icon={icons.warningFillTransparent}
						label={__('Feature unavailable', 'eightshift-forms')}
						subtitle={__('It looks like your field has a missing name.', 'eightshift-forms')}
					/>
				) : (
					<>
						<Toggle
							label={__('Use conditional visibility', 'eightshift-forms')}
							checked={conditionalTagsUse}
							onChange={(value) => {
								setAttributes({ [conditionalTagsUseKey]: value });
								setAttributes({
									[conditionalTagsRulesKey]: !value ? undefined : [globalManifest.comparatorActions.HIDE, []],
								});
							}}
						/>

						<ContainerGroup showIf={conditionalTagsUse}>
							{conditionalTagsIsHidden && (
								<Notice
									label={__(
										'Field is hidden. This might introduce issues if used with conditional tags.',
										'eightshift-forms',
									)}
									type='warning'
								/>
							)}

							<BaseControl
								icon={icons.conditionH}
								label={__('Rules', 'eightshift-forms')}
								// Translators: %d refers to the number of active rules
								subtitle={rulesCount > 0 && sprintf(__('%d rules', 'eightshift-forms'), rulesCount)}
							>
								<Button
									variant='tertiary'
									onClick={() => setIsModalOpen(true)}
								>
									{rulesCount > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
								</Button>
							</BaseControl>

							{isModalOpen && (
								<Modal
									title={
										<RichLabel
											icon={icons.conditionalVisibility}
											label={__('Conditional visibility', 'eightshift-forms')}
										/>
									}
									onRequestClose={() => setIsModalOpen(false)}
								>
									<div>
										<ConditionalTagsType />
									</div>

									<div>
										<RichLabel
											icon={icons.lightBulb}
											label={__(
												"If you can't find a field, make sure the form is saved, and all fields have a name set.",
												'eightshift-forms',
											)}
										/>

										<Button
											variant='primary'
											onClick={() => setIsModalOpen(false)}
										>
											{__('Save', 'eightshift-forms')}
										</Button>
									</div>
								</Modal>
							)}
						</ContainerGroup>
					</>
				)}
			</>
		</>
	);
};
