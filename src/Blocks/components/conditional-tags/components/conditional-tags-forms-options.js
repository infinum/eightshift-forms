import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { getAttrKey, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	BaseControl,
	Select,
	RichLabel,
	Notice,
	OptionSelect,
	Button,
	ContainerPanel,
	Toggle,
	ContainerGroup,
	Modal,
} from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import { CONDITIONAL_TAGS_ACTIONS_LABELS } from './conditional-tags-labels';
import { getConstantsOptions } from '../../utils';
import { getRestUrl } from '../../form/assets/state-init';
import manifest from '../manifest.json';

export const ConditionalTagsFormsOptions = (attributes) => {
	const { setAttributes } = attributes;

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsRulesForms = checkAttr('conditionalTagsRulesForms', attributes, manifest);
	const conditionalTagsPostId = checkAttr('conditionalTagsPostId', attributes, manifest);

	const [formFields, setFormFields] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${getRestUrl('formFields', true)}?id=${conditionalTagsPostId}` }).then((response) => {
			if (response.code === 200 && response.data) {
				setFormFields(response.data.fields);
			}
		});
	}, [conditionalTagsPostId]);

	const ConditionalTagsItem = ({ index }) => {
		if (!formFields) {
			return null;
		}

		const fieldValue = conditionalTagsRulesForms?.[index]?.[0];
		const optionsItem = formFields?.find((item) => item.value === fieldValue)?.subItems ?? [];

		return (
			<>
				<Select
					value={fieldValue}
					options={formFields}
					onChange={(value) => {
						conditionalTagsRulesForms[index][0] = value;
						setAttributes({
							[getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [...conditionalTagsRulesForms],
						});
					}}
					simpleValue
					noSearch
				/>

				{optionsItem?.length > 0 && (
					<Select
						value={conditionalTagsRulesForms?.[index]?.[2]}
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
							conditionalTagsRulesForms[index][2] = value;
							setAttributes({
								[getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [...conditionalTagsRulesForms],
							});
						}}
						simpleValue
						noSearch
					/>
				)}

				<OptionSelect
					value={conditionalTagsRulesForms?.[index]?.[1]}
					options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_LABELS)}
					onChange={(value) => {
						conditionalTagsRulesForms[index][1] = value;
						setAttributes({
							[getAttrKey('conditionalTagsAction', attributes, manifest)]: [...conditionalTagsRulesForms],
						});
					}}
				/>

				<Button
					icon={icons.trash}
					onClick={() => {
						conditionalTagsRulesForms.splice(index, 1);
						setAttributes({
							[getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [...conditionalTagsRulesForms],
						});
					}}
					label={__('Remove', 'eightshift-forms')}
				/>
			</>
		);
	};

	return (
		<ContainerPanel>
			<Toggle
				icon={icons.visibilityAlt}
				label={__('Field visibility overrides', 'eightshift-forms')}
				checked={conditionalTagsUse}
				onChange={(value) => {
					setAttributes({ [getAttrKey('conditionalTagsUse', attributes, manifest)]: value });

					if (!value) {
						setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: undefined });
					} else {
						setAttributes({ [getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [] });
					}
				}}
			/>

			<ContainerGroup hidden={!conditionalTagsUse}>
				<RichLabel
					icon={icons.conditionH}
					label={__('Rules', 'eightshift-forms')}
					// Translators: %d refers to the number of active rules
					subtitle={
						conditionalTagsRulesForms?.length > 0 &&
						sprintf(__('%d added', 'eightshift-forms'), conditionalTagsRulesForms.length)
					}
				/>
			</ContainerGroup>

			<Modal
				title={
					<RichLabel
						icon={icons.visibilityAlt}
						label={__('Field visibility overrides', 'eightshift-forms')}
					/>
				}
				triggerLabel={
					conditionalTagsRulesForms?.length > 0
						? __('Edit rules', 'eightshift-forms')
						: __('Add rule', 'eightshift-forms')
				}
				actions={
					<Button
						type='selected'
						slot='close'
					>
						{__('Close', 'eightshift-forms')}
					</Button>
				}
			>
				<Notice
					label={__(
						'It is important to remember that utilizing field visibility overrides may result in unforeseen consequences when used with conditional tags.',
						'eightshift-forms',
					)}
					type={'warning'}
				/>

				{conditionalTagsRulesForms.length > 0 && (
					<div>
						<span>{__('Field', 'eightshift-forms')}</span>
						<span>{__('Visibility', 'eightshift-forms')}</span>
					</div>
				)}

				<div>
					{conditionalTagsRulesForms?.map((_, index) => {
						return (
							<div key={index}>
								<ConditionalTagsItem index={index} />
							</div>
						);
					})}
				</div>

				<Button
					icon={icons.plusCircleFillAlt}
					onClick={() =>
						setAttributes({
							[getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [
								...conditionalTagsRulesForms,
								[formFields?.[0]?.value ?? '', 'show', ''],
							],
						})
					}
				>
					{__('Add visibility rule', 'eightshift-forms')}
				</Button>

				<RichLabel
					icon={icons.lightBulb}
					label={__(
						"If you can't find a field, make sure the form is saved, and all fields have a name set.",
						'eightshift-forms',
					)}
				/>
			</Modal>
		</ContainerPanel>
	);
};
