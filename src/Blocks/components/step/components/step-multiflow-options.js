import { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { arrowsRight, chevronRight, conditionH, infoCircle, lightBulb, none, plusCircle, plusCircleFill, route, rows, Spinner, trash, treeAlt } from '@eightshift/ui-components/icons';
import { getAttrKey, checkAttr, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { BaseControl, RichLabel, Button, InputField, Toggle, ContainerGroup, Modal, Container, OptionSelect, HStack, VStack, NumberPicker } from '@eightshift/ui-components';
import { CONDITIONAL_TAGS_OPERATORS_LABELS } from './../../conditional-tags/components/conditional-tags-labels';
import { getConstantsOptions } from '../../utils';
import { getRestUrl } from '../../form/assets/state-init';
import { ProgressBarOptions } from '../../progress-bar/components/progress-bar-options';
import { MultiflowFormsReactFlow } from '../../react-flow';
import globalManifest from '../../../manifest.json';
import manifest from '../manifest.json';

const ConditionalTagsItem = ({ topParent, parent, index, total, stepMultiflowRules, formFields, attributes, setAttributes }) => {
	const operatorValue = stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[1] ?? globalManifest.comparator.IS;
	const fieldValue = stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[0];

	// Internal state due to rerendering issue.
	const [inputCheck, setInputCheck] = useState(stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[2]);

	const formFieldOptions = formFields?.find((item) => item.value === stepMultiflowRules[topParent][1])?.subItems ?? [];
	const formFieldSelectedItem = formFieldOptions?.find((item) => item.value === fieldValue)?.subItems ?? [];
	const showRuleValuePicker = formFieldSelectedItem?.length > 0 && (operatorValue === globalManifest.comparator.IS || operatorValue === globalManifest.comparator.ISN);

	return (
		<>
			<Container
				lessSpaceStart
				lessSpaceEnd
			>
				<HStack noWrap>
					<OptionSelect
						aria-label={__('Form field', 'eightshift-forms')}
						value={fieldValue}
						options={formFieldOptions}
						onChange={(value) => {
							stepMultiflowRules[topParent][2][parent][index][0] = value;
							stepMultiflowRules[topParent][2][parent][index][2] = '';
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						itemProps={{
							placeholder: __('Select field', 'eightshift-forms'),
						}}
						wrapperProps={{
							placeholder: __('Select field', 'eightshift-forms'),
						}}
						type='menu'
						inline
					/>

					<OptionSelect
						aria-label={__('Operator', 'eightshift-forms')}
						value={operatorValue}
						options={getConstantsOptions(CONDITIONAL_TAGS_OPERATORS_LABELS)}
						onChange={(value) => {
							stepMultiflowRules[topParent][2][parent][index][1] = value;
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						type='menu'
						inline
					/>

					<InputField
						aria-label={__('Value', 'eightshift-forms')}
						hidden={showRuleValuePicker}
						value={inputCheck}
						onBlur={() => setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] })}
						onChange={(value) => {
							stepMultiflowRules[topParent][2][parent][index][2] = value;
							setInputCheck(value);
						}}
						size='medium'
					/>

					<OptionSelect
						aria-label={__('Value', 'eightshift-forms')}
						hidden={!showRuleValuePicker}
						value={stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[2]}
						options={formFieldSelectedItem}
						onChange={(value) => {
							stepMultiflowRules[topParent][2][parent][index][2] = value;
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						type='menu'
						inline
					/>

					<Button
						icon={trash}
						onClick={() => {
							stepMultiflowRules[topParent][2][parent].splice(index, 1);

							if (stepMultiflowRules[topParent][2][parent].length === 0) {
								stepMultiflowRules[topParent][2].splice(parent, 1);
							}
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						label={__('Remove', 'eightshift-forms')}
						type='dangerGhost'
						className='esf:ml-auto'
					/>
				</HStack>
			</Container>

			<Container hidden={total !== index + 1}>
				<Button
					icon={plusCircle}
					onClick={() => {
						stepMultiflowRules[topParent][2][parent][index + 1] = [formFields?.[0]?.value ?? '', globalManifest.comparator.IS, ''];
						setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
					}}
					className='esf:ml-auto'
				>
					{__('Condition', 'eightshift-forms')}
				</Button>
			</Container>
		</>
	);
};

export const StepMultiflowOptions = (attributes) => {
	const { setAttributes } = attributes;

	const stepMultiflowUse = checkAttr('stepMultiflowUse', attributes, manifest);
	const stepMultiflowRules = checkAttr('stepMultiflowRules', attributes, manifest);
	const stepMultiflowPostId = checkAttr('stepMultiflowPostId', attributes, manifest);
	const stepProgressBarUse = checkAttr('stepProgressBarUse', attributes, manifest);

	const [formFields, setFormFields] = useState(null);
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

	if (formFields === null) {
		return <Spinner className='esf:size-20' />;
	}

	if (!formFields) {
		return (
			<RichLabel
				icon={infoCircle}
				label={__('Add a "Step" block to enable these options', 'eightshift-forms')}
			/>
		);
	}

	return (
		<>
			<ProgressBarOptions
				{...props('progressBar', attributes, {
					progressBarMultiflowUse: stepMultiflowUse,
				})}
				additionalControls={
					<>
						<Container>
							<Toggle
								icon={rows}
								label={__('Stepped multi-flow', 'eightshift-forms')}
								checked={stepMultiflowUse}
								onChange={(value) => {
									setAttributes({ [getAttrKey('stepMultiflowUse', attributes, manifest)]: value });

									if (!value) {
										setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: undefined });
									} else {
										setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [] });
									}
								}}
							/>
						</Container>
					</>
				}
			/>

			<ContainerGroup hidden={!stepMultiflowUse}>
				<Container>
					<BaseControl
						icon={conditionH}
						label={__('Flows', 'eightshift-forms')}
						// Translators: %d refers to the number of active rules
						subtitle={stepMultiflowRules?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), stepMultiflowRules.length)}
						inline
					>
						<Modal
							title={__('Multi-flow preview', 'eightshift-forms')}
							triggerLabel={__('Preview', 'eightshift-forms')}
						>
							<MultiflowFormsReactFlow
								formFields={formFieldsFull}
								stepMultiflowRules={stepMultiflowRules}
							/>
						</Modal>
					</BaseControl>
				</Container>

				{stepMultiflowRules?.map((_, index) => {
					const topParent = index;

					const hasRules = stepMultiflowRules?.[index]?.[2].length > 0;

					return (
						<Container>
							<BaseControl
								label={
									<HStack className='esf:[&_svg]:size-12'>
										<OptionSelect
											aria-label={__('Starting step', 'eightshift-forms')}
											value={stepMultiflowRules?.[index]?.[1]}
											options={formFields}
											onChange={(value) => {
												stepMultiflowRules[index][1] = value;
												stepMultiflowRules[index][2] = [];
												setAttributes({
													[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
												});
											}}
											itemProps={{ size: 'small' }}
											type='menu'
											inline
										/>

										{chevronRight}

										<OptionSelect
											aria-label={__('Target step', 'eightshift-forms')}
											value={stepMultiflowRules?.[index]?.[0]}
											options={formFieldsFull}
											onChange={(value) => {
												stepMultiflowRules[index][0] = value;
												setAttributes({
													[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
												});
											}}
											itemProps={{ size: 'small' }}
											type='menu'
											inline
										/>
									</HStack>
								}
								inline
							>
								<Modal
									title={__('Flow conditions', 'eightshift-forms')}
									triggerLabel={__('Edit', 'eightshift-forms')}
									triggerProps={{ disabled: stepMultiflowRules?.[index]?.[1] === stepMultiflowRules?.[index]?.[0] }}
									actions={
										<HStack noWrap>
											<RichLabel
												icon={lightBulb}
												label={__("If you can't find a field, make sure the form is saved, and all fields have a name set.", 'eightshift-forms')}
											/>

											<HStack
												className='esf:shrink-0'
												noWrap
											>
												<Button
													onClick={() => {
														stepMultiflowRules.splice(index, 1);
														setAttributes({
															[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
														});
													}}
													type='dangerGhost'
													className='esf:ml-auto'
													slot='close'
												>
													{__('Remove flow', 'eightshift-forms')}
												</Button>

												<Button
													slot='close'
													type='selected'
												>
													{__('Close', 'eightshift-forms')}
												</Button>
											</HStack>
										</HStack>
									}
									noCloseButton
								>
									<Container
										standalone
										centered
										elevated
										accent
									>
										<RichLabel
											icon={route}
											label={sprintf(__('Go from "%s" to "%s" if following conditions match', 'eightshift-forms'), formFields.find((field) => field.value === stepMultiflowRules?.[index]?.[1])?.label ?? '', formFields.find((field) => field.value === stepMultiflowRules?.[index]?.[0])?.label ?? '')}
										/>
									</Container>

									<VStack hidden={!formFields}>
										{stepMultiflowRules?.[topParent]?.[2]?.map((_, index) => (
											<ContainerGroup label={stepMultiflowRules?.[topParent]?.[2]?.length > 1 && index > 0 && __('or when', 'eightshift-forms')}>
												{stepMultiflowRules?.[topParent]?.[2]?.[index]?.map((_, innerIndex) => (
													<ConditionalTagsItem
														topParent={topParent}
														parent={index}
														index={innerIndex}
														total={stepMultiflowRules[topParent][2][index].length}
														stepMultiflowRules={stepMultiflowRules}
														formFields={formFields}
														attributes={attributes}
														setAttributes={setAttributes}
													/>
												))}
											</ContainerGroup>
										))}

										<Button
											icon={hasRules ? treeAlt : plusCircleFill}
											onClick={() => {
												stepMultiflowRules[topParent][2].push([[formFields?.[topParent]?.subItems?.[0]?.value ?? '', globalManifest.comparator.IS, '']]);
												setAttributes({
													[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
												});
											}}
											size={hasRules ? 'default' : 'large'}
											className='esf:mr-auto'
										>
											{hasRules ? __('Add alternate set of conditions', 'eightshift-forms') : __('Add set of conditions', 'eightshift-forms')}
										</Button>
									</VStack>

									<ContainerGroup
										hidden={!hasRules}
										label={__('Options', 'eightshift-forms')}
									>
										<Container
											hidden={!stepProgressBarUse}
											centered
										>
											<HStack>
												<RichLabel
													icon={arrowsRight}
													label={__('Advance', 'eightshift-forms')}
												/>

												<NumberPicker
													aria-label={__('Number of steps to advance', 'eightshift-forms')}
													value={stepMultiflowRules?.[index]?.[3]}
													onChange={(value) => {
														stepMultiflowRules[index][3] = value;
														setAttributes({
															[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
														});
													}}
													min={1}
													placeholder='1'
													size='medium'
												/>

												<span>{_n('step', 'steps', stepMultiflowRules?.[index]?.[3], 'eightshift-forms')}</span>
											</HStack>
										</Container>

										<Container>
											<Toggle
												icon={none}
												label={__('Disable next button', 'eightshift-forms')}
												checked={stepMultiflowRules[index][4]}
												onChange={() => {
													stepMultiflowRules[index][4] = !stepMultiflowRules[index][4];
													setAttributes({
														[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
													});
												}}
											/>
										</Container>
									</ContainerGroup>
								</Modal>
							</BaseControl>
						</Container>
					);
				})}

				<Container>
					<Button
						icon={plusCircle}
						onClick={() =>
							setAttributes({
								[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules, [formFields?.[0]?.value ?? '', formFields?.[0]?.value ?? '', []]],
							})
						}
						className='esf:w-full'
					>
						{__('Flow', 'eightshift-forms')}
					</Button>
				</Container>
			</ContainerGroup>
		</>
	);
};
