import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Modal } from '@wordpress/components';
import { icons } from '@eightshift/ui-components/icons';
import { getAttrKey, checkAttr, props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	BaseControl,
	Select,
	RichLabel,
	Button,
	ContainerPanel,
	InputField,
	Toggle,
	ContainerGroup,
	Spacer,
	Notice,
} from '@eightshift/ui-components';
import { CONDITIONAL_TAGS_OPERATORS_LABELS } from './../../conditional-tags/components/conditional-tags-labels';
import { getConstantsOptions } from '../../utils';
import { getRestUrl } from '../../form/assets/state-init';
import { ProgressBarOptions } from '../../progress-bar/components/progress-bar-options';
import { MultiflowFormsReactFlow } from '../../react-flow';
import globalManifest from '../../../manifest.json';
import manifest from '../manifest.json';

export const StepMultiflowOptions = (attributes) => {
	const { setAttributes } = attributes;

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
						<div key={index}>
							<div>
								<span>{__('Go to from step', 'eightshift-forms')}</span>
								<Select
									value={stepMultiflowRules?.[index]?.[1]}
									options={formFields}
									onChange={(value) => {
										stepMultiflowRules[index][1] = value;
										stepMultiflowRules[index][2] = [];
										setAttributes({
											[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
										});
									}}
									simpleValue
									noSearch
								/>

								<span>{__('to step', 'eightshift-forms')}</span>

								<Select
									value={stepMultiflowRules?.[index]?.[0]}
									options={formFieldsFull}
									onChange={(value) => {
										stepMultiflowRules[index][0] = value;
										setAttributes({
											[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
										});
									}}
									simpleValue
									noSearch
								/>

								<span>{__('if the following conditions match:', 'eightshift-forms')}</span>

								<Button
									icon={icons.trash}
									onClick={() => {
										stepMultiflowRules.splice(index, 1);
										setAttributes({
											[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
										});
									}}
									label={__('Remove', 'eightshift-forms')}
								/>
							</div>

							<ConditionalTagsType topParent={index} />

							{stepProgressBarUse && (
								<div>
									<span>{__('and show', 'eightshift-forms')}</span>
									<InputField
										type={'number'}
										value={stepMultiflowRules?.[index]?.[3]}
										onChange={(value) => {
											stepMultiflowRules[index][3] = value;
											setAttributes({
												[getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules],
											});
										}}
									/>
									<span>{__('steps in the progress bar.', 'eightshift-forms')}</span>
								</div>
							)}
						</div>
					);
				})}

				<Button
					icon={icons.plusCircleFillAlt}
					onClick={() =>
						setAttributes({
							[getAttrKey('stepMultiflowRules', attributes, manifest)]: [
								...stepMultiflowRules,
								[formFields?.[0]?.value ?? '', formFields?.[0]?.value ?? '', []],
							],
						})
					}
				>
					{__('Add flow', 'eightshift-forms')}
				</Button>
			</>
		);
	};

	const ConditionalTagsType = ({ topParent }) => {
		if (!formFields) {
			return null;
		}

		return (
			<>
				<div>
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
								})}

								{stepMultiflowRules?.[topParent]?.[2]?.length > 1 && index + 1 < total && (
									<div>{__('OR', 'eightshift-forms')}</div>
								)}
							</>
						);
					})}
				</div>
				<div>
					<Button
						icon={icons.plusCircleFillAlt}
						onClick={() => {
							stepMultiflowRules[topParent][2].push([
								[formFields?.[topParent]?.subItems?.[0]?.value ?? '', globalManifest.comparator.IS, ''],
							]);
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
					>
						{__('Add step rules', 'eightshift-forms')}
					</Button>

					<Toggle
						icon={icons.visible}
						type={'button'}
						label={__('Disable next button', 'eightshift-forms')}
						checked={stepMultiflowRules[topParent][4]}
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

		const formFieldOptions =
			formFields?.find((item) => item.value === stepMultiflowRules[topParent][1])?.subItems ?? [];
		const formFieldSelectedItem = formFieldOptions?.find((item) => item.value === fieldValue)?.subItems ?? [];
		const showRuleValuePicker =
			formFieldSelectedItem?.length > 0 &&
			(operatorValue === globalManifest.comparator.IS || operatorValue === globalManifest.comparator.ISN);

		return (
			<>
				<div>
					<Select
						value={fieldValue}
						options={formFieldOptions}
						onChange={(value) => {
							stepMultiflowRules[topParent][2][parent][index][0] = value;
							stepMultiflowRules[topParent][2][parent][index][2] = '';
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						simpleValue
						noSearch
					/>

					<Select
						value={operatorValue}
						options={getConstantsOptions(CONDITIONAL_TAGS_OPERATORS_LABELS)}
						onChange={(value) => {
							stepMultiflowRules[topParent][2][parent][index][1] = value;
							setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
						}}
						simpleValue
						noSearch
					/>

					<span>{'='}</span>
					{!showRuleValuePicker ? (
						<InputField
							value={inputCheck}
							onBlur={() =>
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] })
							}
							onChange={(value) => {
								stepMultiflowRules[topParent][2][parent][index][2] = value;
								setInputCheck(value);
							}}
						/>
					) : (
						<Select
							value={stepMultiflowRules?.[topParent]?.[2]?.[parent]?.[index]?.[2]}
							options={formFieldSelectedItem}
							onChange={(value) => {
								stepMultiflowRules[topParent][2][parent][index][2] = value;
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
							}}
							simpleValue
							noSearch
						/>
					)}

					{total === index + 1 && (
						<Button
							icon={icons.plusCircleFillAlt}
							onClick={() => {
								stepMultiflowRules[topParent][2][parent][index + 1] = [
									formFields?.[0]?.value ?? '',
									globalManifest.comparator.IS,
									'',
								];
								setAttributes({ [getAttrKey('stepMultiflowRules', attributes, manifest)]: [...stepMultiflowRules] });
							}}
						>
							{__('AND', 'eightshift-forms')}
						</Button>
					)}

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
					/>
				</div>
			</>
		);
	};

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('Multi step/flow form', 'eightshift-forms')}
			/>

			{formFields?.length > 1 ? (
				<>
					<ProgressBarOptions
						{...props('progressBar', attributes, {
							progressBarMultiflowUse: stepMultiflowUse,
						})}
					/>

					<Toggle
						icon={icons.visible}
						label={__('Flow preview', 'eightshift-forms')}
						checked={isModalPreviewOpen}
						onChange={() => setIsModalPreviewOpen(true)}
					/>

					<Toggle
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
					/>

					<ContainerGroup showIf={stepMultiflowUse}>
						<BaseControl
							icon={icons.conditionH}
							label={__('Rules', 'eightshift-forms')}
							// Translators: %d refers to the number of active rules
							subtitle={
								stepMultiflowRules?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), stepMultiflowRules.length)
							}
						>
							<Button
								variant='tertiary'
								onClick={() => setIsModalOpen(true)}
							>
								{stepMultiflowRules?.length > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
							</Button>
						</BaseControl>
					</ContainerGroup>

					{isModalPreviewOpen && (
						<Modal
							title={
								<RichLabel
									icon={icons.anchor}
									label={__('Multi-flow preview', 'eightshift-forms')}
								/>
							}
							onRequestClose={() => {
								setIsModalPreviewOpen(false);
							}}
						>
							<MultiflowFormsReactFlow
								formFields={formFieldsFull}
								stepMultiflowRules={stepMultiflowRules}
							/>
						</Modal>
					)}

					{isModalOpen && (
						<Modal
							title={
								<RichLabel
									icon={icons.anchor}
									label={__('Multi-flow setup', 'eightshift-forms')}
								/>
							}
							onRequestClose={() => {
								setIsModalOpen(false);
							}}
						>
							<div>
								<MultiflowType />
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
									onClick={() => {
										setIsModalOpen(false);
									}}
								>
									{__('Save', 'eightshift-forms')}
								</Button>
							</div>
						</Modal>
					)}
				</>
			) : (
				<Notice
					label={__('Feature unavailable', 'eightshift-forms')}
					subtitle={__('It looks like you are missing step blocks.', 'eightshift-forms')}
					type='warning'
				/>
			)}
		</ContainerPanel>
	);
};
