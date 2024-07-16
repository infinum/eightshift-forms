/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { isArray } from 'lodash';
import { __, sprintf } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { PanelBody, TextControl, Button, Modal, ExternalLink } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import {
	IconLabel,
	icons,
	getAttrKey,
	checkAttr,
	props,
	AsyncSelect,
	MultiSelect,
	IconToggle,
	Control,
	Section,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { ConditionalTagsFormsOptions } from '../../../components/conditional-tags/components/conditional-tags-forms-options';
import {
	FormEditButton,
	LocationsButton,
	SettingsButton,
	outputFormSelectItemWithIcon,
} from '../../../components/utils';
import { getRestUrl } from '../../../components/form/assets/state-init';

export const FormsOptions = ({
	attributes,
	setAttributes,
	preview,
	formSelectOptions
}) => {
	const manifest = select(STORE_NAME).getBlock('forms');

	const {
		isGeoPreview,
		setIsGeoPreview,
	} = preview;

	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);
	const formsFormPostIdRaw = checkAttr('formsFormPostIdRaw', attributes, manifest);
	const formsStyle = checkAttr('formsStyle', attributes, manifest);
	const formsFormDataTypeSelector = checkAttr('formsFormDataTypeSelector', attributes, manifest);
	const formsFormGeolocation = checkAttr('formsFormGeolocation', attributes, manifest);
	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);
	const formsSuccessRedirectVariation = checkAttr('formsSuccessRedirectVariation', attributes, manifest);

	const [isGeoModalOpen, setIsGeoModalOpen] = useState(false);
	const [geoFormFields, setGeoFormFields] = useState([]);

	useEffect(() => {
		apiFetch({
			path: getRestUrl('countriesGeolocation', true),
		}).then((response) => {
			if (response.code === 200) {
				setGeoFormFields(response.data);
			}
		});
	}, []);

	let formsStyleOptions = [];
	let formsUseGeolocation = false;
	let geolocationApi = '';

	// Custom block forms style options.
	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.formsBlockStyleOptions)) {
		formsStyleOptions = esFormsLocalization.formsBlockStyleOptions;
	}

	// Is geolocation active.
	if (typeof esFormsLocalization !== 'undefined' && esFormsLocalization?.use?.geolocation) {
		formsUseGeolocation = true;

		geolocationApi = getRestUrl('countriesGeolocation');
	}

	return (
		<>
			<PanelBody title={__('Form', 'eightshift-forms')}>
				<AsyncSelect
					help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
					value={outputFormSelectItemWithIcon(Object.keys(formsFormPostIdRaw ?? {}).length ? formsFormPostIdRaw : {id: formsFormPostId})}
					loadOptions={formSelectOptions}
					onChange={(value) => {
						setAttributes({
							[getAttrKey('formsFormPostIdRaw', attributes, manifest)]: {
								id: value?.id,
								label: value?.metadata?.label,
								value: value?.metadata?.value,
								metadata: value?.metadata?.metadata,
							},
							[getAttrKey('formsFormPostId', attributes, manifest)]: `${value?.value.toString()}`,
						});
					}}
				/>

				{formsFormPostId &&
					<Control>
						<div className='es-fifty-fifty-h es-gap-2!'>
							<FormEditButton formId={formsFormPostId} />
							<SettingsButton formId={formsFormPostId} />
							<LocationsButton formId={formsFormPostId} />
						</div>
					</Control>
				}


				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<TextControl
						label={<IconLabel icon={icons.codeVariable} label={__('Additional type specifier', 'eightshift-forms')} />}
						help={__('Additional data type selectors', 'eightshift-forms')}
						value={formsFormDataTypeSelector}
						onChange={(value) => setAttributes({ [getAttrKey('formsFormDataTypeSelector', attributes, manifest)]: value })}
					/>
				</Section>

				{formsStyleOptions?.length > 0 &&
					<MultiSelect
						icon={icons.paletteColor}
						label={__('Form style preset', 'eightshift-forms')}
						value={formsStyle}
						options={formsStyleOptions}
						onChange={(value) => setAttributes({ [getAttrKey('formsStyle', attributes, manifest)]: value })}
						simpleValue
					/>
				}
			</PanelBody>

			<PanelBody title={__('Results output', 'eightshift-forms')} initialOpen={false}>
				<TextControl
					label={<IconLabel icon={icons.anchor} label={__('Success redirect variation', 'eightshift-forms')} />}
					help={__('Override form settings success redirect variation value', 'eightshift-forms')}
					value={formsSuccessRedirectVariation}
					onChange={(value) => setAttributes({ [getAttrKey('formsSuccessRedirectVariation', attributes, manifest)]: value })}
				/>
			</PanelBody>

			{formsUseGeolocation &&
				<PanelBody title={__('Geolocation', 'eightshift-forms')} initialOpen={false}>
					<MultiSelect
						label={__('Show form only if in these countries:', 'eightshift-forms')}
						help={formsFormGeolocationAlternatives?.length < 1 && __('If you can\'t find a country, start typing its name while the dropdown is open.', 'eightshift-forms')}
						value={formsFormGeolocationAlternatives?.length > 0 ? [] : formsFormGeolocation}
						options={geoFormFields}
						onChange={(value) => setAttributes({ [getAttrKey('formsFormGeolocation', attributes, manifest)]: value })}
						cacheOptions={false}
						simpleValue
						disabled={formsFormGeolocationAlternatives?.length > 0}
						placeholder={formsFormGeolocationAlternatives?.length > 0 && __('Overriden by advanced rules', 'eightshift-forms')}
					/>

					<Control
						icon={icons.locationSettings}
						label={__('Advanced rules', 'eightshift-forms')}
						// Translators: %d refers to the number of active rules
						subtitle={formsFormGeolocationAlternatives?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), formsFormGeolocationAlternatives.length)}
						noBottomSpacing={formsFormGeolocationAlternatives?.length < 1}
						reducedBottomSpacing={formsFormGeolocationAlternatives?.length > 0}
						inlineLabel
					>
						<Button
							variant='tertiary'
							onClick={() => setIsGeoModalOpen(true)}
							className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
						>
							{formsFormGeolocationAlternatives?.length > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
						</Button>
					</Control>

					{formsFormGeolocationAlternatives?.length > 0 &&
						<IconToggle
							icon={icons.visible}
							label={__('Rule preview', 'eightshift-forms')}
							checked={isGeoPreview}
							onChange={(value) => setIsGeoPreview(value)}
							noBottomSpacing
						/>
					}

					{isGeoModalOpen && (
						<Modal
							overlayClassName='es-geolocation-modal'
							className='es-modal-max-width-xxl es-rounded-3!'
							title={<IconLabel icon={icons.locationSettings} label={__('Advanced rules', 'eightshift-forms')} standalone />}
							onRequestClose={() => setIsGeoModalOpen(false)}
						>
							<p>{__('Geolocation rules allow you to display alternate forms based on the user\'s location.', 'eightshift-forms')}</p>
							<p>{__('If no rules are added and the "Show form only if in countries" field is populated, the form will only be shown in these countries. Otherwise, the form is shown everywhere.', 'eightshift-forms')}</p>

							{geolocationApi &&
								<p>{__('You can find complete list of countries and regions on this', 'eightshift-forms')} <ExternalLink href={geolocationApi}>{__('link', 'eightshift-forms')}</ExternalLink>.</p>
							}

							<br />

							{formsFormGeolocationAlternatives?.length > 0 &&
								<div className='es-h-spaced es-pb-2 es-mb-2 es-border-b-cool-gray-300'>
									<span className='es-w-64'>{__('Form to display', 'eightshift-forms')}</span>
									<span className='es-w-80'>{__('Countries to show the form in', 'eightshift-forms')}</span>
								</div>
							}

							{formsFormGeolocationAlternatives?.map((_, index) => {
								return (
									<div className='es-h-spaced es-mb-2' key={index}>
										<AsyncSelect
											value={
												outputFormSelectItemWithIcon(
													Object.keys(formsFormGeolocationAlternatives?.[index]?.form ?? {}).length ?
													formsFormGeolocationAlternatives?.[index]?.form :
													{id: formsFormGeolocationAlternatives?.[index]?.formId}
												)
											}
											loadOptions={formSelectOptions}
											onChange={(value) => {
												const newData = [...formsFormGeolocationAlternatives];
												newData[index].form = {
													id: value?.id,
													label: value?.metadata?.label,
													value: value?.metadata?.value,
													metadata: value?.metadata?.metadata,
												};
												newData[index].formId = value.value.toString();
												setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: newData });
											}}
											additionalSelectClasses='es-w-64'
											noBottomSpacing
										/>

										<MultiSelect
											value={formsFormGeolocationAlternatives?.[index]?.geoLocation}
											options={geoFormFields}
											onChange={(value) => {
												const newData = [...formsFormGeolocationAlternatives];
												newData[index].geoLocation = value;
												setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: newData });
											}}
											additionalSelectClasses='es-w-80'
											noBottomSpacing
											simpleValue
										/>

										<Button
											icon={icons.trash}
											onClick={() => {
												formsFormGeolocationAlternatives.splice(index, 1);
												setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: [...formsFormGeolocationAlternatives] });
											}}
											label={__('Remove', 'eightshift-forms')}
											className='es-ml-auto es-rounded-1!'
										/>
									</div>
								);
							})}

							<Button
								icon={icons.plusCircleFillAlt}
								className='es-rounded-1 es-mt-4'
								onClick={() => setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: [...formsFormGeolocationAlternatives, { formId: '', geoLocation: [] }] })}
							>
								{__('Add rule', 'eightshift-forms')}
							</Button>

							<div className='es-mt-8 -es-mx-8 es-px-8 es-pt-8 es-border-t-cool-gray-100 es-h-end es-gap-8!'>
								<Button
									variant='primary'
									onClick={() => setIsGeoModalOpen(false)}
									className='es-rounded-1.5!'
								>
									{__('Close', 'eightshift-forms')}
								</Button>
							</div>
						</Modal>
					)}
				</PanelBody>
			}

			<ConditionalTagsFormsOptions
				{...props('conditionalTags', attributes, {
					setAttributes,
					conditionalTagsPostId: formsFormPostId,
				})}
			/>
		</>
	);
};
