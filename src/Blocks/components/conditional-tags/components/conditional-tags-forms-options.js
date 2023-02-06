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
import { select } from "@wordpress/data";
import { CONDITIONAL_TAGS_ACTIONS_INTERNAL } from './conditional-tags-utils';
import { getConstantsOptions } from '../../utils';
import manifest from '../manifest.json';

export const ConditionalTagsFormsOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsAction = checkAttr('conditionalTagsAction', attributes, manifest);
	const conditionalTagsRules = checkAttr('conditionalTagsRules', attributes, manifest);
	const conditionalTagsPostId = checkAttr('conditionalTagsPostId', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [isNewRuleAdded, setIsNewRuleAdded] = useState(false);
	const [formFields, setFormFields] = useState([]);

	useEffect(() => {
		apiFetch({ path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.formFields}/?id=${conditionalTagsPostId}` }).then((response) => {

			if (response.code === 200) {
				setFormFields(getConstantsOptions(response.data, true));
			}
		});
	}, [conditionalTagsPostId, isModalOpen]);

	const ConditionalTagsItem = ({index}) => {
		return (
			<div className='es-fifty-fifty-auto-h es-has-wp-field-t-space'>
				<SelectControl
					value={conditionalTagsRules?.[index]?.[0]}
					options={formFields}
					onChange={(value) => {
						conditionalTagsRules[index][0] = value;
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
					}}
				/>

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
							<InlineNotification
								text={__('If some fields are missing please make sure all your field names are set and you have updated/saved form in the top right corner.', 'eightshift-forms')}
								type={InlineNotificationType.INFO}
							/>

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
									<div key={index}>
										<ConditionalTagsItem index={index} />
										<Button
											isLarge
											icon={icons.trash}
											onClick={() => {
												conditionalTagsRules.splice(index, 1);
												setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules] });
											}}
											label={__('Remove', 'eightshift-forms')}
											style={{ marginTop: '0.2rem' }}
										/>
									</div>
								);
							})}

							<Button
								isSecondary
								icon={icons.add}
								onClick={() => {
									setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules, [] ]})
									setIsNewRuleAdded(true);
								}}
							>
								{__('Add rule', 'eightshift-forms')}
							</Button>

							<div className='es-h-end es-has-wp-field-t-space'>
								<Button onClick={() => {
									setIsModalOpen(false);
									setIsNewRuleAdded(false);
								}}>
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
