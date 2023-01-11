import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import { Fragment } from "@wordpress/element";
import { TextControl, SelectControl, PanelBody, Button, BaseControl, Modal } from '@wordpress/components';
import {
	CustomSelect,
	IconLabel,
	icons,
	getAttrKey,
	checkAttr,
	getFetchWpApi,
	unescapeHTML,
	BlockIcon,
	FancyDivider,
	STORE_NAME,
	camelize,
	IconToggle,
	CollapsableComponentUseToggle,
} from '@eightshift/frontend-libs/scripts';
import { CONDITIONAL_TAGS_OPERATORS, CONDITIONAL_TAGS_ACTIONS, CONDITIONAL_TAGS_LOGIC } from './../../form/assets/utilities';
import { getFormFields } from '../../utils';
import manifest from '../manifest.json';

export const ConditionalTagsOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const conditionalTagsUse = checkAttr('conditionalTagsUse', attributes, manifest);
	const conditionalTagsAction = checkAttr('conditionalTagsAction', attributes, manifest);
	const conditionalTagsLogic = checkAttr('conditionalTagsLogic', attributes, manifest);
	const conditionalTagsRules = checkAttr('conditionalTagsRules', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);

	const getConstantsOptions = (options, useEmpty = false) => {
		const items = Object.values(options).map((item) => ({'value': item, 'label': item}));
		const empty = {
			value: '',
			label: '',
		};
		
		return useEmpty ? [empty, ...items] : items;
	}

	const ConditionalTagsItem = ({index}) => {
		return (
			<>
				<div className='es-fifty-fifty-auto-h es-has-wp-field-t-space'>
					<SelectControl
						value={conditionalTagsRules?.[index]?.[0]}
						options={getFormFields()}
						onChange={(value) => {
							conditionalTagsRules[index][0] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: conditionalTagsRules });
						}}
					/>

					<SelectControl
						value={conditionalTagsRules?.[index]?.[1]}
						options={getConstantsOptions(CONDITIONAL_TAGS_OPERATORS, true)}
						onChange={(value) => {
							conditionalTagsRules[index][1] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: conditionalTagsRules });
						}}
					/>

					<TextControl
						value={conditionalTagsRules?.[index]?.[2]}
						onChange={(value) => {
							conditionalTagsRules[index][2] = value;
							setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: conditionalTagsRules });
						}}
					/>
				</div>
			</>
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
						setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: undefined })
						setAttributes({ [getAttrKey('conditionalTagsLogic', attributes, manifest)]: undefined })
						setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: undefined })
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
							onRequestClose={() => setIsModalOpen(false)}
						>
							<SelectControl
								value={conditionalTagsAction}
								options={getConstantsOptions(CONDITIONAL_TAGS_ACTIONS)}
								onChange={(value) => setAttributes({ [getAttrKey('conditionalTagsAction', attributes, manifest)]: value })}
							/>
		
							<SelectControl
								value={conditionalTagsLogic}
								options={getConstantsOptions(CONDITIONAL_TAGS_LOGIC)}
								onChange={(value) => setAttributes({ [getAttrKey('conditionalTagsLogic', attributes, manifest)]: value })}
							/>
		
							<Button
								isSecondary
								icon={icons.add}
								onClick={() => setAttributes({ [getAttrKey('conditionalTagsRules', attributes, manifest)]: [...conditionalTagsRules, [] ]})}
							>
								{__('Add rule', 'eightshift-forms')}
							</Button>
		
							{conditionalTagsRules.map((_, index) => {
								return (
									<Fragment key={index}>
										<ConditionalTagsItem
											index={index}
										/>
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
									</Fragment>
								);
							})}

							<div className='es-h-end es-has-wp-field-t-space'>
								<Button onClick={() => {
									setIsModalOpen(false);
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
}
