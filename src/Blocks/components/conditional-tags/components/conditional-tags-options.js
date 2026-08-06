import { useState, useEffect } from 'react';
import { __, _n, sprintf } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { getAttrKey, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { RichLabel, Notice, Button, InputField, Toggle, Container, ContainerGroup, Spacer, Modal, HStack, BaseControl, OptionSelect } from '@eightshift/ui-components';
import { Spinner, conditionalVisibility, hide, lightBulb, play, plusCircle, trash, treeAlt, visible } from '@eightshift/ui-components/icons';
import { getConstantsOptions } from '../../utils';
import { CONDITIONAL_TAGS_ACTIONS_LABELS, CONDITIONAL_TAGS_ACTIONS_INVERSE_LABELS, CONDITIONAL_TAGS_OPERATORS_LABELS } from './conditional-tags-labels';
import { getRestUrl } from '../../form/assets/state-init';
import { truncateMiddle, upperFirst } from '@eightshift/ui-components/utilities';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const ConditionalTagsOptions = (attributes) => {
	const { setAttributes } = attributes;

	const postId = select('core/editor').getCurrentPostId();

	const [formFields, setFormFields] = useState(null);

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

		const hasRule = conditionalTagsRules?.[1]?.length > 0;

		return (
			<>
				<Container
					className='esf:w-fit'
					standalone
					centered
					compact
				>
					<RichLabel
						icon={conditionalTagsRules[0] === 'hide' ? hide : visible}
						label={sprintf(__('Field is %s', 'eightshift-forms'), CONDITIONAL_TAGS_ACTIONS_LABELS[conditionalTagsRules[0]])}
					/>
				</Container>

				<Spacer />

				<RichLabel label={sprintf(__('%s "%s" if', 'eightshift-forms'), CONDITIONAL_TAGS_ACTIONS_INVERSE_LABELS[conditionalTagsRules[0]], conditionalTagsBlockName)} />

				{conditionalTagsRules?.[1]?.map((_, index) => (
					<ContainerGroup label={conditionalTagsRules?.[1]?.length > 1 && index > 0 && __('or when', 'eightshift-forms')}>
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
					</ContainerGroup>
				))}

				<Button
					icon={plusCircle}
					onClick={() => {
						conditionalTagsRules[1].push([[formFields?.[0]?.value ?? '', globalManifest.comparator.IS, '']]);
						setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
					}}
					size={hasRule ? 'default' : 'large'}
				>
					{hasRule ? __('OR', 'eightshift-forms') : __('Rule', 'eightshift-forms')}
				</Button>
			</>
		);
	};

	const ConditionalTagsItem = ({ parent, index, total }) => {
		const operatorValue = conditionalTagsRules?.[1]?.[parent]?.[index]?.[1] ?? globalManifest.comparator.IS;
		const fieldValue = conditionalTagsRules?.[1]?.[parent]?.[index]?.[0];

		// Internal state due to rerendering issue.
		const [inputCheck, setInputCheck] = useState(conditionalTagsRules?.[1]?.[parent]?.[index]?.[2] ?? '');

		if (!formFields) {
			return null;
		}

		const formFieldOptions = formFields?.find((item) => item.value === conditionalTagsRules[1][parent][index][0])?.subItems ?? [];
		const showRuleValuePicker = formFieldOptions?.length > 0 && (operatorValue === globalManifest.comparator.IS || operatorValue === globalManifest.comparator.ISN);

		return (
			<>
				<Container
					lessSpaceStart
					lessSpaceEnd
				>
					<HStack noWrap>
						<OptionSelect
							aria-label={__('Field', 'eightshift-forms')}
							value={fieldValue}
							options={formFields
								.filter((item) => {
									// Remove current field from selection.
									if (item.value !== conditionalTagsBlockName) {
										return item;
									}

									return null;
								})
								.map((item) => {
									return { ...item, label: truncateMiddle(item?.label, 20), subtitle: upperFirst(item?.type) };
								})}
							onChange={(value) => {
								conditionalTagsRules[1][parent][index][0] = value;
								conditionalTagsRules[1][parent][index][2] = '';
								setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
							}}
							type='menu'
						/>

						<OptionSelect
							aria-label={__('Comparator', 'eightshift-forms')}
							value={operatorValue}
							options={getConstantsOptions(CONDITIONAL_TAGS_OPERATORS_LABELS)}
							onChange={(value) => {
								conditionalTagsRules[1][parent][index][1] = value;
								setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
							}}
							type='menu'
						/>

						<OptionSelect
							aria-label={__('Value', 'eightshift-forms')}
							hidden={!showRuleValuePicker}
							value={conditionalTagsRules?.[1]?.[parent]?.[index]?.[2]}
							options={formFieldOptions}
							onChange={(value) => {
								conditionalTagsRules[1][parent][index][2] = value;
								setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
							}}
							type='menu'
						/>

						<InputField
							hidden={showRuleValuePicker}
							value={inputCheck}
							onBlur={() => setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] })}
							onChange={(value) => {
								conditionalTagsRules[1][parent][index][2] = value;
								setInputCheck(value);
							}}
							size='medium'
						/>

						<Button
							className='esf:ml-auto'
							icon={trash}
							onClick={() => {
								conditionalTagsRules[1][parent].splice(index, 1);

								if (conditionalTagsRules[1][parent].length === 0) {
									conditionalTagsRules[1].splice(parent, 1);
								}
								setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
							}}
							type='dangerGhost'
							aria-label={__('Remove', 'eightshift-forms')}
							tooltip
						/>
					</HStack>
				</Container>

				<Container
					hidden={total !== index + 1}
					className='esf:justify-end'
					lessSpaceEnd
					centered
				>
					<Button
						icon={plusCircle}
						onClick={() => {
							conditionalTagsRules[1][parent][index + 1] = [formFields?.[0]?.value ?? '', globalManifest.comparator.IS, ''];
							setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
						}}
					>
						{__('AND', 'eightshift-forms')}
					</Button>
				</Container>
			</>
		);
	};

	const rulesCount = conditionalTagsRules?.[1]?.length && conditionalTagsRules?.[1]?.flat()?.length;

	if (!formFields) {
		return (
			<Container
				standalone
				centered
			>
				<BaseControl
					icon={conditionalVisibility}
					label={__('Conditional visibility', 'eightshift-forms')}
					inline
					className='esf:w-full'
				>
					<Spinner />
				</BaseControl>
			</Container>
		);
	}

	if (formFields?.length < 1) {
		return (
			<Container
				standalone
				centered
			>
				<RichLabel
					icon={conditionalVisibility}
					label={__('Conditional visibility unavailable', 'eightshift-forms')}
					subtitle={__('Field(s) may be missing a name', 'eightshift-forms')}
				/>
			</Container>
		);
	}

	return (
		<>
			<ContainerGroup>
				<Container>
					<Toggle
						icon={conditionalVisibility}
						label={__('Conditional visibility', 'eightshift-forms')}
						checked={conditionalTagsUse}
						onChange={(value) => {
							setAttributes({ [conditionalTagsUseKey]: value });
							setAttributes({
								[conditionalTagsRulesKey]: !value ? undefined : [globalManifest.comparatorActions.HIDE, []],
							});
						}}
					/>
				</Container>

				<Container hidden={!conditionalTagsUse}>
					<OptionSelect
						icon={play}
						label={__('Starting state', 'eightshift-forms')}
						value={conditionalTagsRules?.[0]}
						options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_LABELS).map((item) => {
							return { ...item, label: upperFirst(item?.label) };
						})}
						onChange={(value) => {
							conditionalTagsRules[0] = value;
							setAttributes({ [conditionalTagsRulesKey]: [...conditionalTagsRules] });
						}}
						inline
					/>
				</Container>

				<Container hidden={!conditionalTagsUse}>
					{conditionalTagsIsHidden && (
						<>
							<Notice
								label={__('Field is hidden.', 'eightshift-forms')}
								subtitle={__('This might introduce issues if used with conditional tags.', 'eightshift-forms')}
								type='warning'
							/>
							<Spacer />
						</>
					)}

					<BaseControl
						icon={treeAlt}
						label={__('Rules', 'eightshift-forms')}
						// Translators: %d refers to the number of active rules
						subtitle={rulesCount > 0 && sprintf(_n('%d added', '%d added', rulesCount, 'eightshift-forms'), rulesCount)}
						inline
					>
						<Modal
							className='esf:max-w-lg!'
							title={__('Conditional visibility', 'eightshift-forms')}
							triggerLabel={__('Manage', 'eightshift-forms')}
							actions={
								<>
									<RichLabel
										label={__("Can't find a field?", 'eightshift-forms')}
										subtitle={__('Make sure the form is saved, and all fields have a name set.', 'eightshift-forms')}
										icon={lightBulb}
										type='warning'
									/>

									<Button
										type='selected'
										slot='close'
										className='esf:ml-auto'
									>
										{__('Close', 'eightshift-forms')}
									</Button>
								</>
							}
							noCloseButton
							noClickToDismiss
						>
							<ConditionalTagsType />
						</Modal>
					</BaseControl>
				</Container>
			</ContainerGroup>
		</>
	);
};
