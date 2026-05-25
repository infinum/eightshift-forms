import { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { getAttrKey, checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	OptionSelect,
	Button,
	ContainerGroup,
	Container,
	Toggle,
	BaseControl,
	ItemCollection,
	HStack,
} from '@eightshift/ui-components';
import { optionListAlt, plusCircle, trash, visibilityAlt } from '@eightshift/ui-components/icons';
import { CONDITIONAL_TAGS_ACTIONS_SHORT_LABELS } from './conditional-tags-labels';
import { getConstantsOptions } from '../../utils';
import { getRestUrl } from '../../form/assets/state-init';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';
import manifest from '../manifest.json';
import { upperFirst } from '@eightshift/ui-components/utilities';

const ConditionalTagsItem = ({ item, formFields }) => {
	const { field, action, fieldOption, updateData, deleteItem } = item;

	const optionsItem = formFields?.find((item) => item.value === field)?.subItems ?? [];

	return (
		<Container className='esf:group'>
			<HStack noWrap>
				<OptionSelect
					value={action}
					options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_SHORT_LABELS).map((option) => ({
						...option,
						label: upperFirst(option.label),
					}))}
					onChange={(value) => updateData({ action: value })}
					type='menu'
				/>

				<OptionSelect
					aria-label={__('Field', 'eightshift-forms')}
					value={field}
					options={formFields}
					onChange={(value) => updateData({ field: value })}
					type='menu'
					disabled={!formFields}
					inline
				/>

				{optionsItem?.length > 0 && <span>&ndash;</span>}

				<OptionSelect
					hidden={optionsItem?.length < 1}
					aria-label={__('Sub-items', 'eightshift-forms')}
					value={fieldOption}
					options={optionsItem.map((item) => {
						if (item.value === '') {
							return {
								...item,
								label: __('All options', 'eightshift-forms'),
								separator: 'below',
							};
						}

						return item;
					})}
					onChange={(value) => updateData({ fieldOption: value })}
					type='menu'
				/>

				<Button
					icon={trash}
					onClick={deleteItem}
					label={__('Remove', 'eightshift-forms')}
					size='small'
					type='dangerGhost'
					className='esf:ml-auto esf:not-group-hover:not-group-has-focus-visible:opacity-0'
				/>
			</HStack>
		</Container>
	);
};

export const ConditionalTagsFormsOptions = (attributes) => {
	const { setAttributes } = attributes;
	const [formFields, setFormFields] = useState([]);

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsRulesForms = checkAttr('conditionalTagsRulesForms', attributes, manifest);
	const conditionalTagsPostId = checkAttr('conditionalTagsPostId', attributes, manifest);

	useEffect(() => {
		apiFetch({ path: `${getRestUrl('formFields', true)}?id=${conditionalTagsPostId}` }).then((response) => {
			if (response.code === 200 && response.data) {
				setFormFields(response.data.fields);
			}
		});
	}, [conditionalTagsPostId]);

	return (
		<>
			<Container
				standalone
				elevated
				accent
			>
				<Toggle
					icon={visibilityAlt}
					label={__('Field visibility overrides', 'eightshift-forms')}
					onChange={(value) => {
						if (value) {
							setAttributes({
								[getAttrKey('conditionalTagsUse', attributes, manifest)]: value,
								[getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [],
							});
						} else {
							setAttributes({
								[getAttrKey('conditionalTagsUse', attributes, manifest)]: value,
								[getAttrKey('conditionalTagsAction', attributes, manifest)]: undefined,
								[getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: undefined,
							});
						}
					}}
					checked={conditionalTagsUse}
				/>
			</Container>

			<ContainerGroup>
				<Container centered>
					<BaseControl
						icon={optionListAlt}
						label={__('Rules', 'eightshift-forms')}
						className='esf:w-full'
						inline
					>
						<HelpTooltip>
							{__(
								'It is important to remember that utilizing field visibility overrides may result in unforeseen consequences when used with conditional tags.',
								'eightshift-forms',
							)}

							<br />
							<br />

							{__(
								"If you can't find a field, make sure the form is saved, and all fields have a name set.",
								'eightshift-forms',
							)}
						</HelpTooltip>
					</BaseControl>
				</Container>

				<ItemCollection
					hidden={!formFields}
					items={conditionalTagsRulesForms.map(([field, action, fieldOption]) => ({
						field,
						action,
						fieldOption,
					}))}
					onChange={(items) => {
						setAttributes({
							[getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: items.map((item) => [
								item.field,
								item.action,
								item.fieldOption,
							]),
						});
					}}
				>
					{(item) => (
						<ConditionalTagsItem
							item={item}
							formFields={formFields}
						/>
					)}
				</ItemCollection>

				<Container
					lessSpaceStart
					lessSpaceEnd
				>
					<Button
						aria-label={__('Add rule', 'eightshift-forms')}
						icon={plusCircle}
						onClick={() =>
							setAttributes({
								[getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [
									...conditionalTagsRulesForms,
									[formFields?.[0]?.value ?? '', 'show', ''],
								],
							})
						}
						className='esf:w-full'
					>
						{__('Rule', 'eightshift-forms')}
					</Button>
				</Container>
			</ContainerGroup>
		</>
	);
};
