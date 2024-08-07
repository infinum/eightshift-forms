import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { TextControl, PanelBody, Button, Modal } from '@wordpress/components';
import { icons, getAttrKey, checkAttr, IconToggle, props, Select, Control, Section, IconLabel, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { CONDITIONAL_TAGS_OPERATORS_LABELS } from './../../conditional-tags/components/conditional-tags-labels';
import { getConstantsOptions } from '../../utils';
import { getRestUrl } from '../../form/assets/state-init';
import { ProgressBarOptions } from '../../progress-bar/components/progress-bar-options';
import { MultiflowFormsReactFlow } from '../../react-flow';
import globalManifest from '../../../manifest.json';

export const StepMultiflowOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('step');

	const {
		setAttributes,
	} = attributes;

	const stepMultiflowUse = checkAttr('stepMultiflowUse', attributes, manifest);
	const stepMultiflowRules = checkAttr('stepMultiflowRules', attributes, manifest);
	const stepMultiflowPostId = checkAttr('stepMultiflowPostId', attributes, manifest);
	const stepProgressBarUse = checkAttr('stepProgressBarUse', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [isModalPreviewOpen, setIsModalPreviewOpen] = useState(false);
	const [formFields, setFormFields] = useState([]);
	const [formFieldsFull, setFormFieldsFull] = useState([]);

	useEffect(() => {
		apiFetch({
			path: `${getRestUrl('formFields', true)}?id=${stepMultiflowPostId}&useMultiflow=true`,
		}).then((response) => {
			if (response.code === 200 && response.data) {
				setFormFields(response.data.steps);
				setFormFieldsFull(response.data.stepsFull);
			}
		});
	}, [stepMultiflowPostId]);

	const MultiflowType = () => {
		return (
			<>
				{stepMultiflowRules?.map((_, index) => {
					return (
						<div key={index} className='es-border-b-cool-gray-100 es-pb-7 es-mb-7'>
							<div className='es-h-spaced es-mb-3'>
								<span>{__('Go to from step', 'eightshift-forms')}</span>
								<Select
									value={stepMultiflowRules?.[index]?.[1]}
									options={formFields}
									onChange={(value) => {
										stepMultiflowRules[index][1] = value;
										stepMultiflowRules[index][2] = [];
										setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
									}}
									noBottomSpacing
									simpleValue
									noSearch
								/>

								<span>{__('to step', 'eightshift-forms')}</span>

								<Select
									value={stepMultiflowRules?.[index]?.[0]}
									options={formFieldsFull}
									onChange={(value) => {
										stepMultiflowRules[index][0] = value;
										setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
									}}
									noBottomSpacing
									simpleValue
									noSearch
								/>

								<span>{__('if the following conditions match:', 'eightshift-forms')}</span>

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

							<ConditionalTagsType topParent={index}/>

							{stepProgressBarUse &&
								<div className='es-h-spaced es-mt-3'>
									<span>{__('and show', 'eightshift-forms')}</span>
										<TextControl
											type={'number'}
											value={stepMultiflowRules?.[index]?.[3]}
											onChange={(value) => {
												stepMultiflowRules[index][3] = value;
												setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
											}}
											className='es-w-15 es-mb-0'
										/>
									<span>{__('steps in the progress bar.', 'eightshift-forms')}</span>
								</div>
							}
						</div>
					);
				})}

				<Button
					icon={icons.plusCircleFillAlt}
					onClick={() => setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules, [formFields?.[0]?.value ?? '', formFields?.[0]?.value ?? '', []]] })}
					className='es-rounded-1'
				>
					{__('Add flow', 'eightshift-forms')}
				</Button>
			</>
		);
	};

	const ConditionalTagsType = ({topParent}) => {
		if (!formFields) {
			return null;
		}

		return (
			<>
				<div className='es-v-spaced'>
					{stepMultiflowRules?.[topParent]?.[2]?.map((_, index) => {
						const total = stepMultiflowRules[topParent][1].length;

						return (
							<>
								{stepMultiflowRules?.[topParent]?.[2]?.[index]?.map((_, innerIndex) => {
										return (
											<ConditionalTagsItem
												key={innerIndex}
												topParent={topParent}
												parent={index}
												index={innerIndex}
												total={stepMultiflowRules[topParent][2][index].length}
											/>
										);
									})
								}

								{(stepMultiflowRules?.[topParent]?.[2]?.length > 1 && (index + 1) < total) &&
									<div className='es-font-weight-700'>
										{__('OR', 'eightshift-forms')}
									</div>
								}
							</>
						);
					})}
				</div>
				<div className='es-v-spaced'>
					<Button
						icon={icons.plusCircleFillAlt}
						onClick={() => {
							stepMultiflowRules[topParent][2].push([[formFields?.[topParent]?.subItems?.[0]?.value ?? '', globalManifest.comparator.IS, '']]);
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						className='es-rounded-1 es-mt-4'
					>
						{(__('Add step rules', 'eightshift-forms'))}
					</Button>

					<IconToggle
						icon={icons.visible}
						type={'button'}
						label={__('Disable next button', 'eightshift-forms')}
						checked={stepMultiflowRules[topParent][4]}
						noBottomSpacing
						additionalClasses={'es-w-fit'}
						onChange={() => {
							stepMultiflowRules[topParent][4] = !stepMultiflowRules[topParent][4];
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
					/>
				</div>
			</>
		);
	};

	const ConditionalTagsItem = ({ topParent, parent, index, total }) => {
		const operatorValue = stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[1] ?? globalManifest.comparator.IS;
		const fieldValue = stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[0];

		// Internal state due to rerendering issue.
		const [inputCheck, setInputCheck] = useState(stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[2]);

		const formFieldOptions = formFields?.find((item) => item.value === stepMultiflowRules[topParent][1])?.subItems ?? [];
		const formFieldSelectedItem = formFieldOptions?.find((item) => item.value === fieldValue)?.subItems ?? [];
		const showRuleValuePicker = formFieldSelectedItem?.length > 0 && (operatorValue === globalManifest.comparator.IS || operatorValue === globalManifest.comparator.ISN);

		return (
			<>
				<div className='es-h-spaced'>
					<Select
						value={fieldValue}
						options={formFieldOptions}
						onChange={(value) => {
							stepMultiflowRules[topParent][2][parent][index][0] = value;
							stepMultiflowRules[topParent][2][parent][index][2] = '';
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
							stepMultiflowRules[topParent][2][parent][index][1] = value;
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
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
							onBlur={() => setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] })}
							onChange={(value) => {
								stepMultiflowRules[topParent][2][parent][index][2] = value;
								setInputCheck(value);
							}}
							className='es-w-40 es-m-0-bcf!'
						/> :
						<Select
							value={stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[2]}
							options={formFieldSelectedItem}
							onChange={(value) => {
								stepMultiflowRules[topParent][2][parent][index][2] = value;
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
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
								stepMultiflowRules[topParent][2][parent][index + 1] = [formFields?.[0]?.value ?? '', globalManifest.comparator.IS, ''];
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
							}}
							className='es-rounded-1'
						>
							{__('AND', 'eightshift-forms')}
						</Button>
					}

					<Button
						icon={icons.trash}
						onClick={() => {
							stepMultiflowRules[topParent][2][parent].splice(index, 1);

							if (stepMultiflowRules[topParent][2][parent].length === 0) {
								stepMultiflowRules[topParent][2].splice(parent, 1);
							}
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						label={__('Remove', 'eightshift-forms')}
						className='es-ml-auto es-rounded-1!'
					/>
				</div>
			</>
		);
	};

	return (
		<PanelBody title={__('Multi step/flow form', 'eightshift-forms')}>
			{(formFields?.length > 1) ?
				<>
					<ProgressBarOptions
						{...props('progressBar', attributes, {
							progressBarMultiflowUse: stepMultiflowUse,
						})}
					/>

					<IconToggle
						icon={icons.visible}
						label={__('Flow preview', 'eightshift-forms')}
						checked={isModalPreviewOpen}
						onChange={() => setIsModalPreviewOpen(true)}
					/>

					<IconToggle
						icon={icons.anchor}
						label={__('Use steps multi-flow', 'eightshift-forms')}
						checked={stepMultiflowUse}
						onChange={(value) => {
							setAttributes({ [getAttrKey('stepMultiflowUse', attributes, manifest)]: value });

							if (!value) {
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: undefined });
							} else {
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [] });
							}
						}}
						additionalClasses='es-font-weight-500 es-mb-5'
					/>

					<Section
						showIf={stepMultiflowUse}
						noBottomSpacing
						additionalClasses='es-pl-5'
					>
						<Control
							icon={icons.conditionH}
							label={__('Rules', 'eightshift-forms')}
							// Translators: %d refers to the number of active rules
							subtitle={stepMultiflowRules?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), stepMultiflowRules.length)}
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

					{isModalPreviewOpen &&
						<Modal
								overlayClassName='es-conditional-tags-modal es-geolocation-modal'
								className='es-modal-max-width-5xl es-rounded-3!'
								title={<IconLabel icon={icons.anchor} label={__('Multi-flow preview', 'eightshift-forms')} standalone />}
								onRequestClose={() => {
									setIsModalPreviewOpen(false);
								}}
							>
								<MultiflowFormsReactFlow
									formFields={formFieldsFull}
									stepMultiflowRules={stepMultiflowRules}
								/>
						</Modal>
					}

					{isModalOpen &&
						<Modal
							overlayClassName='es-conditional-tags-modal es-geolocation-modal'
							className='es-modal-max-width-5xl es-rounded-3!'
							title={<IconLabel icon={icons.anchor} label={__('Multi-flow setup', 'eightshift-forms')} standalone />}
							onRequestClose={() => {
								setIsModalOpen(false);
							}}
						>
							<div className='es-v-spaced'>
								<MultiflowType />
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
									}}
									className='es-rounded-1.5!'
								>
									{__('Save', 'eightshift-forms')}
								</Button>
							</div>
						</Modal>
					}
				</> :
				<IconLabel
					icon={icons.warningFillTransparent}
					label={__('Feature unavailable', 'eightshift-forms')}
					subtitle={__('It looks like you are missing step blocks.', 'eightshift-forms')}
					additionalClasses='es-nested-color-yellow-500!'
					addSubtitleGap
					standalone
				/>
			}
		</PanelBody>
	);
};
