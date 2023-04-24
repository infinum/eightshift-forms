/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { PanelBody, Button, Modal } from '@wordpress/components';
import { icons, getAttrKey, checkAttr, IconToggle, Select, Control, Section, IconLabel, OptionSelector } from '@eightshift/frontend-libs/scripts';
import { CONDITIONAL_TAGS_ACTIONS_INTERNAL } from './conditional-tags-utils';
import { getConstantsOptions } from '../../utils';
import manifest from '../manifest.json';

export const ConditionalTagsFormsOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsRules = checkAttr('conditionalTagsRules', attributes, manifest);
	const conditionalTagsPostId = checkAttr('conditionalTagsPostId', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [isNewRuleAdded, setIsNewRuleAdded] = useState(false);
	const [formFields, setFormFields] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.formFields}/?id=${conditionalTagsPostId}` }).then((response) => {

			if (response.code === 200) {
				setFormFields(response.data);
			}
		});
	}, [conditionalTagsPostId, isModalOpen]);

	const ConditionalTagsItem = ({ index }) => {
		const fieldValue = conditionalTagsRules?.[index]?.[0];

		const optionsItem = fieldValue?.subItems ?? [];

		return (
			<>
				<Select
					value={fieldValue}
					options={formFields}
					onChange={(value) => {
						const newData = [...conditionalTagsRules];
						newData[index][0] = value;
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: newData });
					}}
					additionalSelectClasses='es-w-40'
					noBottomSpacing
				/>

				{optionsItem?.length > 0 &&
					<Select
						value={conditionalTagsRules?.[index]?.[2]}
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
							const newData = [...conditionalTagsRules];
							newData[index][2] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: newData });
						}}
						additionalSelectClasses='es-w-40'
						noBottomSpacing
					/>
				}

				{hasSubFields && optionsItem?.length < 1 && <span className='es-w-40'>&nbsp;</span>}

				<OptionSelector
					value={conditionalTagsRules?.[index]?.[1]}
					options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_INTERNAL)}
					onChange={(value) => {
						const newData = [...conditionalTagsRules];
						newData[index][1] = value;
						setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: newData });
					}}
					additionalContainerClass='es-w-40'
					additionalButtonClass='es-h-7.5'
					noBottomSpacing
				/>
			</>
		);
	};

	const hasSubFields = conditionalTagsRules.map(([fieldData]) => fieldData).some(({ subItems }) => subItems?.length > 0);

	return (
		<PanelBody>
			<IconToggle
				icon={icons.visibilityAlt}
				label={__('Field visibility overrides', 'eightshift-frontend-libs')}
				checked={conditionalTagsUse}
				onChange={(value) => {
					setAttributes({ [getAttrKey('conditionalTagsUse', attributes, manifest)]: value });

					if (!value) {
						setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: undefined });
					} else {
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [] });
					}
				}}
				noBottomSpacing={!conditionalTagsUse}
				additionalClasses='es-font-weight-500'
			/>

			<Section showIf={conditionalTagsUse}>
				<Control
					icon={icons.conditionH}
					label={__('Rules', 'eightshift-forms')}
					// Translators: %d refers to the number of active rules
					subtitle={conditionalTagsRules?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), conditionalTagsRules.length)}
					noBottomSpacing
					inlineLabel
				>
					<Button
						variant='tertiary'
						onClick={() => setIsModalOpen(true)}
						className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
					>
						{conditionalTagsRules?.length > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
					</Button>
				</Control>
			</Section>

			{conditionalTagsUse && isModalOpen &&
				<Modal
					overlayClassName='es-conditional-tags-modal es-geolocation-modal'
					className='es-modal-max-width-xxl es-rounded-3!'
					title={<IconLabel icon={icons.visibilityAlt} label={__('Field visibility overrides', 'eightshift-forms')} standalone />}
					onRequestClose={() => {
						setIsModalOpen(false);
						setIsNewRuleAdded(false);
					}}
				>
					<div className='es-h-spaced es-pb-2 es-mb-2 es-border-b-cool-gray-300'>
						<span className='es-w-40'>{__('Field', 'eightshift-forms')}</span>
						{hasSubFields && <span className='es-w-40'>{__('Inner fields', 'eightshift-forms')}</span>}
						<span className='es-w-40'>{__('Visibility', 'eightshift-forms')}</span>
					</div>

					<div className='es-v-spaced'>
						{conditionalTagsRules?.map((_, index) => {
							const itemExists = formFields.filter((item) => {
								return conditionalTagsRules?.[index]?.[0] === item?.value && item?.value !== '';
							});

							if (itemExists.length < 0 && !isNewRuleAdded) {
								const newData = [...conditionalTagsRules];
								newData.splice(index, 1);
								setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: newData });
								return null;
							}

							return (
								<div key={index} className='es-h-spaced'>
									<ConditionalTagsItem index={index} />

									<Button
										icon={icons.trash}
										onClick={() => {
											const newData = [...conditionalTagsRules];
											newData.splice(index, 1);
											setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: newData });
										}}
										label={__('Remove', 'eightshift-forms')}
										className='es-ml-auto es-rounded-1!'
									/>
								</div>
							);
						})}
					</div>

					<Button
						icon={icons.plusCircleFillAlt}
						onClick={() => {
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules, [formFields?.[0]?.value ?? '', 'show', '']] });
							setIsNewRuleAdded(true);
						}}
						className='es-rounded-1 es-mt-4'
					>
						{__('Add rule', 'eightshift-forms')}
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
		</PanelBody>
	);
};
