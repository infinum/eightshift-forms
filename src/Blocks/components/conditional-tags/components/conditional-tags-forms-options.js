import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { PanelBody, Button, Modal } from '@wordpress/components';
import { icons, getAttrKey, checkAttr, IconToggle, Select, Control, Section, IconLabel, OptionSelector } from '@eightshift/frontend-libs/scripts';
import { CONDITIONAL_TAGS_ACTIONS_LABELS } from './conditional-tags-labels';
import { getConstantsOptions } from '../../utils';
import manifest from '../manifest.json';
import { ROUTES, getRestUrl } from '../../form/assets/state';

export const ConditionalTagsFormsOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsRulesForms = checkAttr('conditionalTagsRulesForms', attributes, manifest);
	const conditionalTagsPostId = checkAttr('conditionalTagsPostId', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [formFields, setFormFields] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${getRestUrl(ROUTES.FORM_FIELDS, true)}?id=${conditionalTagsPostId}` }).then((response) => {
			if (response.code === 200 && response.data) {
				setFormFields(response.data.fields);
			}
		});
	}, [conditionalTagsPostId, isModalOpen]);

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
						setAttributes({ [getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [...conditionalTagsRulesForms] });
					}}
					noBottomSpacing
					simpleValue
					noSearch
					additionalSelectClasses='es-w-40'
				/>

				{optionsItem?.length > 0 &&
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
							setAttributes({ [getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [...conditionalTagsRulesForms] });
						}}
						noBottomSpacing
						simpleValue
						noSearch
						additionalSelectClasses='es-w-40'
					/>
				}

				<OptionSelector
					value={conditionalTagsRulesForms?.[index]?.[1]}
					options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_LABELS)}
					onChange={(value) => {
						conditionalTagsRulesForms[index][1] = value;
						setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: [...conditionalTagsRulesForms] });
					}}
					additionalContainerClass='es-w-40'
					additionalButtonClass='es-h-7.5'
					noBottomSpacing
				/>

				<Button
					icon={icons.trash}
					onClick={() => {
						conditionalTagsRulesForms.splice(index, 1);
						setAttributes({ [getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [...conditionalTagsRulesForms] });
					}}
					label={__('Remove', 'eightshift-forms')}
					className='es-ml-auto es-rounded-1!'
				/>
			</>
		);
	};

	return (
		<PanelBody>
			<IconToggle
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
				noBottomSpacing={!conditionalTagsUse}
				additionalClasses='es-font-weight-500'
			/>

			<Section showIf={conditionalTagsUse} noBottomSpacing>
				<Control
					icon={icons.conditionH}
					label={__('Rules', 'eightshift-forms')}
					// Translators: %d refers to the number of active rules
					subtitle={conditionalTagsRulesForms?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), conditionalTagsRulesForms.length)}
					noBottomSpacing
					inlineLabel
				>
					<Button
						variant='tertiary'
						onClick={() => setIsModalOpen(true)}
						className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
					>
						{conditionalTagsRulesForms?.length > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
					</Button>
				</Control>
			</Section>

			{conditionalTagsUse && isModalOpen &&
				<Modal
					overlayClassName='es-conditional-tags-modal es-geolocation-modal'
					className='es-modal-max-width-xxl es-rounded-3!'
					title={<IconLabel icon={icons.visibilityAlt} label={__('Field visibility overrides', 'eightshift-forms')} standalone />}
					onRequestClose={() => setIsModalOpen(false)}
				>
					<div className='es-mb-10'>{__('It is important to remember that utilizing field visibility overrides may result in unforeseen consequences when used with conditional tags.', 'eightshift-forms')}</div>

					{conditionalTagsRulesForms.length > 0 &&
						<div className='es-h-spaced es-pb-2 es-mb-2 es-border-b-cool-gray-300'>
							<span className='es-w-40'>{__('Field', 'eightshift-forms')}</span>
							<span className='es-w-40'>{__('Visibility', 'eightshift-forms')}</span>
						</div>
					}

					<div className='es-v-spaced'>
						{conditionalTagsRulesForms?.map((_, index) => {
							return (
								<div key={index} className='es-h-spaced'>
									<ConditionalTagsItem index={index} />
								</div>
							);
						})}
					</div>

					<Button
						icon={icons.plusCircleFillAlt}
						onClick={() => setAttributes({ [getAttrKey('conditionalTagsRulesForms', attributes, manifest)]: [...conditionalTagsRulesForms, [formFields?.[0]?.value ?? '', 'show', '']] })}
						className='es-rounded-1 es-mt-4'
					>
						{__('Add visibility rule', 'eightshift-forms')}
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
							onClick={() => setIsModalOpen(false)}
							className='es-rounded-1.5!'
						>
							{__('Save', 'eightshift-forms')}
						</Button>
					</div>
				</Modal>
			}
		</PanelBody>
	);
};
