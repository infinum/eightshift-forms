/* global esFormsLocalization */

import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { TextControl, SelectControl, PanelBody, Button, Modal } from '@wordpress/components';
import {
	icons,
	getAttrKey,
	checkAttr,
	IconToggle,
	InlineNotification,
	InlineNotificationType,
} from '@eightshift/frontend-libs/scripts';
import { CONDITIONAL_TAGS_OPERATORS_INTERNAL, CONDITIONAL_TAGS_ACTIONS_INTERNAL, CONDITIONAL_TAGS_LOGIC_INTERNAL } from './conditional-tags-utils';
import { getConstantsOptions } from '../../utils';
import manifest from '../manifest.json';

export const ConditionalTagsOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const postId = select('core/editor').getCurrentPostId();

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsAction = checkAttr('conditionalTagsAction', attributes, manifest);
	const conditionalTagsLogic = checkAttr('conditionalTagsLogic', attributes, manifest);
	const conditionalTagsRules = checkAttr('conditionalTagsRules', attributes, manifest);
	const conditionalTagsParentName = checkAttr('conditionalTagsParentName', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [isNewRuleAdded, setIsNewRuleAdded] = useState(false);
	const [formFields, setFormFields] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.formFields}/?id=${postId}` }).then((response) => {

			if (response.code === 200 && response.data) {
				setFormFields(response.data);
			}
		});
	}, [isModalOpen, postId]);

	const ConditionalTagsItem = ({index}) => {

		const operatorValue = conditionalTagsRules?.[index]?.[1] ?? 'is';
		const fieldValue = conditionalTagsRules?.[index]?.[0];

		// Internal state due to rerendering issue.
		const [inputCheck, setInputCheck] = useState(conditionalTagsRules?.[index]?.[2]);

		const options = formFields?.find((item) => item.value === conditionalTagsRules[index][0])?.options ?? [];
		const optionsItem = formFields?.find((item) => item.value === conditionalTagsParentName)?.options ?? [];

		if (formFields.length <= 0) {
			return (<>{__('Missing field names!','eightshift-forms')}</>);
		}

		return (
			<div className='es-conditional-tags-modal__item-inner es-conditional-tags-modal__grid' data-count={optionsItem.length ? '4' : '3'}>
				{formFields && 
					<>
						<SelectControl
							value={fieldValue}
							options={formFields}
							onChange={(value) => {
								conditionalTagsRules[index][0] = value;
								setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
							}}
						/>

						<SelectControl
							value={operatorValue}
							options={getConstantsOptions(CONDITIONAL_TAGS_OPERATORS_INTERNAL)}
							onChange={(value) => {
								conditionalTagsRules[index][1] = value;
								setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
							}}
						/>

						{(options.length && (operatorValue === 'is' || operatorValue === 'isn')) ?
							<SelectControl
								value={conditionalTagsRules?.[index]?.[2]}
								options={options}
								onChange={(value) => {
									conditionalTagsRules[index][2] = value;
									setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
								}}
							/> :
							<TextControl
								value={inputCheck}
								onBlur={() => setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] })}
								onChange={(value) => {
									conditionalTagsRules[index][2] = value;
									setInputCheck(value);
								}}
							/>
						}

						<SelectControl
							value={conditionalTagsRules?.[index]?.[3]}
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
								conditionalTagsRules[index][3] = value;
								setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
							}}
						/>
					</>
				}
			</div>
		);
	};

	const optionsItem = formFields?.find((item) => item.value === conditionalTagsParentName)?.options ?? [];

	return (
		<PanelBody title={__('Conditional tags', 'eightshift-forms')} initialOpen={false}>
			<IconToggle
				icon={icons.width}
				label={__('Use conditional tags', 'eightshift-frontend-libs')}
				checked={conditionalTagsUse}
				onChange={(value) => {
					setAttributes({ [getAttrKey('conditionalTagsUse', attributes, manifest)]: value });

					if (!value) {
						setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('conditionalTagsLogic', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: undefined });
					} else {
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [] });
					}
				}}
			/>

			{conditionalTagsUse &&
				<>
					<Button
						isSecondary
						icon={icons.locationSettings}
						onClick={() => {
							setIsModalOpen(true);
						}}
					>
						{__('Configure', 'eightshift-forms')}
					</Button>

					{isModalOpen &&
						<Modal
							overlayClassName='es-conditional-tags-modal es-geolocation-modal'
							className='es-modal-max-width-l'
							title={__('Conditional tags', 'eightshift-forms')}
							onRequestClose={() => {
								setIsModalOpen(false);
								setIsNewRuleAdded(false);
							}}
						>
							<p className='es-conditional-tags-modal__desc'>
								{__('With this options you can control what fields/options are visible or hidden on your form.', 'eightshift-forms')}
							</p>

							<InlineNotification
								text={__('If some fields are missing please make sure all your field names are set and you have updated/saved form in the top right corner.', 'eightshift-forms')}
								type={InlineNotificationType.INFO}
							/>

							<div className="es-conditional-tags-modal__header-intro">
								<SelectControl
									value={conditionalTagsAction}
									options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_INTERNAL)}
									onChange={(value) => setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: value })}
								/>
								<span className='es-conditional-tags-modal__header-intro-text'>{__('this field if', 'eightshift-forms')}</span>
								<SelectControl
									value={conditionalTagsLogic}
									options={getConstantsOptions(CONDITIONAL_TAGS_LOGIC_INTERNAL)}
									onChange={(value) => setAttributes({ [getAttrKey('conditionalTagsLogic', attributes, manifest)]: value })}
								/>
								<span className='es-conditional-tags-modal__header-intro-text'>{__('of the following match:', 'eightshift-forms')}</span>
							</div>

							<div className="es-conditional-tags-modal__header es-conditional-tags-modal__grid" data-count={optionsItem.length ? '4' : '3'}>
								<span>{__('Field', 'eightshift-forms')}</span>
								<span>{__('Condition', 'eightshift-forms')}</span>
								<span>{__('Value', 'eightshift-forms')}</span>
								{optionsItem.length === 3 &&
									<span>{__('Inner fields', 'eightshift-forms')}</span>
								}
							</div>

							{conditionalTagsRules?.map((_, index) => {
								const itemExists = formFields?.filter((item) => {
									return conditionalTagsRules?.[index]?.[0] === item?.value && item?.value !== '';
								});

								if (!itemExists.length && !isNewRuleAdded) {
									conditionalTagsRules.splice(index, 1);
									setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
									return;
								}

								return (
									<div key={index} className="es-conditional-tags-modal__item">
										<ConditionalTagsItem index={index} />
										<Button
											className="es-conditional-tags-modal__item-remove"
											icon={icons.trash}
											onClick={() => {
												conditionalTagsRules.splice(index, 1);
												setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
											}}
											label={__('Remove', 'eightshift-forms')}
										/>
									</div>
								);
							})}

							<Button
								isSecondary
								icon={icons.add}
								onClick={() => {
									setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules, [formFields?.[0]?.value ?? '', 'is', '', '']]});
									setIsNewRuleAdded(true);
								}}
							>
								{__('Add rule', 'eightshift-forms')}
							</Button>

							<div className='es-h-end es-has-wp-field-t-space'>
								<Button
									isPrimary
									onClick={() => {
										setIsModalOpen(false);
										setIsNewRuleAdded(false);
									}}
								>
									{__('Close', 'eightshift-forms')}
								</Button>
							</div>
						</Modal>
					}
				</>
			}
		</PanelBody>
	);
};
