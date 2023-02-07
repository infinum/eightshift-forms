/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { SelectControl, PanelBody, Button, Modal } from '@wordpress/components';
import {
	icons,
	getAttrKey,
	checkAttr,
	IconToggle,
	InlineNotification,
	InlineNotificationType
} from '@eightshift/frontend-libs/scripts';
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

	const ConditionalTagsItem = ({index}) => {
		const fieldValue = conditionalTagsRules?.[index]?.[0];

		const optionsItem = formFields.find((item) => item.value === fieldValue)?.options ?? [];

		return (
			<div className="es-conditional-tags-modal__grid" data-count={optionsItem.length ? '3': '2'}>
				<SelectControl
					value={fieldValue}
					options={formFields}
					onChange={(value) => {
						conditionalTagsRules[index][0] = value;
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
					}}
				/>

				{optionsItem &&
					<SelectControl
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
							conditionalTagsRules[index][2] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
						}}
					/>
				}

				<SelectControl
					value={conditionalTagsRules?.[index]?.[1]}
					options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS_INTERNAL)}
					onChange={(value) => {
						conditionalTagsRules[index][1] = value;
						setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: [...conditionalTagsRules] });
					}}
				/>
			</div>
		);
	};

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
								{__('With this options you can control what fields/options are visible or hidden on your form depending on the usage location.', 'eightshift-forms')}
							</p>
							<InlineNotification
								text={__('If some fields are missing please make sure all your field names are set and you have updated/saved form in the top right corner.', 'eightshift-forms')}
								type={InlineNotificationType.INFO}
							/>

						<div className="es-conditional-tags-modal__header es-conditional-tags-modal__grid" data-count={'3'}>
								<span>{__('Field', 'eightshift-forms')}</span>
								<span>{__('Inner fields', 'eightshift-forms')}</span>
								<span>{__('Visibility', 'eightshift-forms')}</span>
							</div>

							{conditionalTagsRules?.map((_, index) => {
								const itemExists = formFields.filter((item) => {
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
									setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules, [formFields?.[0]?.value ?? '', 'show', ''] ]});
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
