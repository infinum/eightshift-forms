/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { isArray } from 'lodash';
import { select } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { MediaPlaceholder } from '@wordpress/block-editor';
import { PanelBody, BaseControl, TextControl, Button, Modal, ExternalLink } from '@wordpress/components';
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
	props,
} from '@eightshift/frontend-libs/scripts';
import { ConditionalTagsFormsOptions } from '../../../components/conditional-tags/components/conditional-tags-forms-options';
import manifest from '../manifest.json';
import { getSettingsJsonOptions } from '../../../components/utils';

export const FormsOptions = ({ attributes, setAttributes, preview }) => {
	const {
		editFormUrl,
		settingsPageUrl
	} = select(STORE_NAME).getSettings();

	const {
		wpAdminUrl,
		settings: {
			successRedirectVariations,
		}
	} = esFormsLocalization;

	const {
		postType,
	} = manifest;

	const {
		isGeoPreview,
		setIsGeoPreview,
	} = preview;

	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);
	const formsStyle = checkAttr('formsStyle', attributes, manifest);
	const formsFormDataTypeSelector = checkAttr('formsFormDataTypeSelector', attributes, manifest);
	const formsFormGeolocation = checkAttr('formsFormGeolocation', attributes, manifest);
	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);
	const formsDownloads = checkAttr('formsDownloads', attributes, manifest);
	const formsSuccessRedirectVariation = checkAttr('formsSuccessRedirectVariation', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [formFields, setFormFields] = useState([]);

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

		geolocationApi = `${esFormsLocalization.restPrefix}${esFormsLocalization.restRoutes.countriesGeolocation}`;
	}

	const formSelectOptions = getFetchWpApi(
		postType,
		{
			processLabel: ({ title: { rendered: renderedTitle } }) => unescapeHTML(renderedTitle)
		}
	);

	useEffect(() => {
		apiFetch({ path: `${esFormsLocalization.restPrefixProject}/${esFormsLocalization.restRoutes.countriesGeolocation}` }).then((response) => {
			if (response.code === 200) {
				setFormFields(response.data);
			}
		});
	}, []);

	const GeoLocationModalItem = ({
		index,
	}) => {
		return (
			<>
				<div className='es-fifty-fifty-auto-h es-has-wp-field-t-space'>
					<CustomSelect
						value={formsFormGeolocationAlternatives?.[index]?.formId}
						loadOptions={formSelectOptions}
						onChange={(value) => {
							formsFormGeolocationAlternatives[index].formId = value.toString();
							setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: formsFormGeolocationAlternatives });
						}}
						isClearable={false}
						reFetchOnSearch={true}
						multiple={false}
						simpleValue
					/>

					<CustomSelect
						value={formsFormGeolocationAlternatives?.[index]?.geoLocation}
						options={formFields}
						onChange={(value) => {
							formsFormGeolocationAlternatives[index].geoLocation = value;
							setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: formsFormGeolocationAlternatives });
						}}
						cacheOptions={false}
						multiple={true}
						simpleValue
					/>
				</div>
			</>
		);
	};

	return (
		<>
			<PanelBody title={__('Settings', 'eightshift-forms')}>
				<CustomSelect
					label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Form to display', 'eightshift-forms')} />}
					help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
					value={parseInt(formsFormPostId)}
					loadOptions={formSelectOptions}
					onChange={(value) => setAttributes({ [getAttrKey('formsFormPostId', attributes, manifest)]: value.toString() })}
					isClearable={false}
					reFetchOnSearch={true}
					multiple={false}
					simpleValue
				/>

				{formsFormPostId &&
					<div className='es-v-spaced es-has-wp-field-b-space'>
						<Button
							icon={icons.edit}
							href={`${wpAdminUrl}${editFormUrl}&post=${formsFormPostId}`}
						>
							{__('Edit fields', 'eightshift-forms')}
						</Button>

						<Button
							icon={icons.options}
							href={`${wpAdminUrl}${settingsPageUrl}&formId=${formsFormPostId}`}
						>
							{__('Form settings', 'eightshift-forms')}
						</Button>
					</div>
				}

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.code} label={__('Type selector', 'eightshift-forms')} />}
					help={__('Additional data type selectors', 'eightshift-forms')}
					value={formsFormDataTypeSelector}
					onChange={(value) => setAttributes({ [getAttrKey('formsFormDataTypeSelector', attributes, manifest)]: value })}
				/>

				<FancyDivider label={__('Thank you page', 'eightshift-forms')} />

				<BaseControl label={<IconLabel icon={icons.design} label={__('Download', 'eightshift-forms')} />}>
					<CustomSelect
						value={formsSuccessRedirectVariation}
						options={getSettingsJsonOptions(successRedirectVariations)}
						onChange={(value) => {
							setAttributes({ [getAttrKey('formsSuccessRedirectVariation', attributes, manifest)]: value });
						}}
						cacheOptions={false}
						multiple={false}
						simpleValue
					/>

					<MediaPlaceholder
						icon={icons.image}
						multiple = {true}
						onSelect={(value) => {
							const items = value.map((item) => {
								return {
									title: item.filename,
									id: item.id,
								};
							});
							setAttributes({ [getAttrKey('formsDownloads', attributes, manifest)]: [...formsDownloads, ...items] });
						}}
					/>

					{formsDownloads.map((item, index) => {
						return (
							<div key={index} className="es-forms-options-download">
								<Button
										onClick={() => {
											delete formsDownloads[index];
											const item = formsDownloads.filter((_, i) => i !== index);
											setAttributes({ [getAttrKey('formsDownloads', attributes, manifest)]: item });
										}}
										icon={icons.trash}
										className='es-button-icon-24 es-slight-button-border-cool-gray-300 es-rounded-1.0 es-nested-color-red-500'
									></Button>
									{item.id}<br/>{item.title}
							</div>
						);
					})}
				</BaseControl>

				{formsStyleOptions?.length > 0 &&
					<CustomSelect
						label={<IconLabel icon={icons.paletteColor} label={__('Form style preset', 'eightshift-forms')} />}
						value={formsStyle}
						options={formsStyleOptions}
						onChange={(value) => setAttributes({ [getAttrKey('formsStyle', attributes, manifest)]: value })}
						simpleValue
						isSearchable={false}
						isClearable={false}
					/>
				}
			</PanelBody>

			{formsUseGeolocation &&
				<PanelBody title={__('Geolocation', 'eightshift-forms')} initialOpen={false}>
					<CustomSelect
						label={<IconLabel icon={icons.locationAllow} label={__('Show form only if in countries', 'eightshift-forms')} />}
						help={
							formsFormGeolocationAlternatives?.length > 0
								? __('Overriden by geolocation rules.', 'eightshift-forms')
								: (
									<>
										<p>{__('If you can\'t find a country, start typing its name while the dropdown is open.', 'eightshift-forms')}</p>
									</>
								)
						}
						value={formsFormGeolocation}
						options={formFields}
						onChange={(value) => setAttributes({[getAttrKey('formsFormGeolocation', attributes, manifest)]: value})}
						cacheOptions={false}
						multiple={true}
						simpleValue
						disabled={formsFormGeolocationAlternatives?.length > 0}
					/>

					<Button
						isSecondary
						icon={icons.locationSettings}
						onClick={() => setIsModalOpen(true)}
					>
						{__('Geolocation rules', 'eightshift-forms')}
					</Button>

					{formsFormGeolocationAlternatives?.length > 0 &&
						<div className='es-has-wp-field-t-space'>
							<Button
								icon={icons.visible}
								isPressed={isGeoPreview}
								onClick={() => setIsGeoPreview(!isGeoPreview)}
							>
								{__('Preview geolocation rules', 'eightshift-forms')}
							</Button>
						</div>
					}

					{isModalOpen && (
						<Modal
							overlayClassName='es-geolocation-modal'
							className='es-modal-max-width-l'
							title={__('Geolocation rules', 'eightshift-forms')}
							onRequestClose={() => setIsModalOpen(false)}
						>
							<p>{__('Geolocation rules allow you to display alternate forms based on the user\'s location.', 'eightshift-forms')}</p>
							<p>{__('If no rules are added and the "Show form only if in countries" field is populated, the form will only be shown in these countries. Otherwise, the form is shown everywhere.', 'eightshift-forms')}</p>
							{geolocationApi && 
								<p>{__('You can find complete list of countries and regions on this', 'eightshift-forms')} <ExternalLink href={geolocationApi}>{__('link', 'eightshift-forms')}</ExternalLink>.</p>
							}

							<br />

							<Button
								isSecondary
								icon={icons.add}
								onClick={() => setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: [...formsFormGeolocationAlternatives, {formId: '', geoLocation: []} ]})}
							>
								{__('Add rule', 'eightshift-forms')}
							</Button>

							{formsFormGeolocationAlternatives?.length > 0 &&
								<div className='es-fifty-fifty-auto-h es-has-wp-field-t-space'>
									<IconLabel icon={<BlockIcon iconName='esf-form' />} label={__('Form to display', 'eightshift-forms')} standalone />
									<IconLabel icon={icons.locationAllow} label={__('Countries to show the form in', 'eightshift-forms')} standalone />
									<div style={{ width: '2.25rem' }}>&nbsp;</div>
								</div>
							}

							{formsFormGeolocationAlternatives?.map((_, index) => {
								return (
									<div key={index}>
										<GeoLocationModalItem index={index} />
										<Button
											isLarge
											icon={icons.trash}
											onClick={() => {
												formsFormGeolocationAlternatives.splice(index, 1);
												setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: [...formsFormGeolocationAlternatives] });
											}}
											label={__('Remove', 'eightshift-forms')}
											style={{ marginTop: '0.2rem' }}
										/>
									</div>
								);
							})}

							<div className='es-h-end es-has-wp-field-t-space'>
								<Button onClick={() => setIsModalOpen(false)}>
									{__('Cancel', 'eightshift-forms')}
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
