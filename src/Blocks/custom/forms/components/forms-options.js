/* global esFormsLocalization */

import React, { useState } from 'react';
import { isArray } from 'lodash';
import { select } from "@wordpress/data";
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, Button, Modal, ExternalLink } from '@wordpress/components';
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
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormsOptions = ({ attributes, setAttributes, preview }) => {
	const {
		editFormUrl,
		settingsPageUrl
	} = select(STORE_NAME).getSettings();

	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const {
		postType,
	} = manifest;

	const {
		isGeoPreview,
		setIsGeoPreview,
	} = preview;

	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);
	const formsFormGeolocation = checkAttr('formsFormGeolocation', attributes, manifest);
	const formsStyle = checkAttr('formsStyle', attributes, manifest);
	const formsFormDataTypeSelector = checkAttr('formsFormDataTypeSelector', attributes, manifest);
	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);

	const [isModalOpen, setIsModalOpen] = useState(false);
	const [geoRepeater, setGeoRepeater] = useState(formsFormGeolocationAlternatives);
	const [prevGeoRepeater, setPrevGeoRepeater] = useState([]);

	let formsStyleOptions = [];
	let formsUseGeolocation = false;
	let geolocationApi = '';

	// Custom block forms style options.
	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.formsBlockStyleOptions)) {
		formsStyleOptions = esFormsLocalization.formsBlockStyleOptions;
	}

	// Is geolocation active.
	if (typeof esFormsLocalization !== 'undefined' && esFormsLocalization?.useGeolocation) {
		formsUseGeolocation = true;

		if (esFormsLocalization?.geolocationApi) {
			geolocationApi = esFormsLocalization.geolocationApi;
		}
	}

	const formSelectOptions = getFetchWpApi(
		postType,
		{
			processLabel: ({ title: { rendered: renderedTitle } }) => unescapeHTML(renderedTitle)
		}
	);

	const geoLocationOptions = getFetchWpApi(
		'geolocation-countries',
		{
			processLabel: ({ label }) => label,
			processId: ({ value }) => value,
			routePrefix: 'eightshift-forms/v1',
			fields: 'label, value',
			perPage: 500,
		}
	);

	const GeoLocationModalItem = ({
		index,
		data,
		removeItem,
		handleChange,
	}) => {
		return (
			<>
				<div className='es-fifty-fifty-auto-h es-has-wp-field-t-space'>
					<CustomSelect
						value={data.formId}
						loadOptions={formSelectOptions}
						onChange={(value) => handleChange(value.toString(), index, 'formId')}
						isClearable={false}
						reFetchOnSearch={true}
						multiple={false}
						simpleValue
					/>

					<CustomSelect
						value={data.geoLocation}
						loadOptions={geoLocationOptions}
						onChange={(value) => handleChange(value, index, 'geoLocation')}
						multiple={true}
					/>

					<Button
						isLarge
						icon={icons.trash}
						onClick={removeItem}
						label={__('Remove', 'eightshift-forms')}
						style={{ marginTop: '0.2rem' }}
					/>
				</div>
			</>
		);
	};

	const addItem = () => {
		setGeoRepeater([...geoRepeater, {
			formId: '',
			geoLocation: [],
		}]);
	};

	return (
		<PanelBody title={__('Eightshift Forms', 'eightshift-forms')}>
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

			{formsUseGeolocation &&
				<>
					<FancyDivider label={__('Geolocation', 'eightshift-forms')} />

					<CustomSelect
						label={<IconLabel icon={icons.locationAllow} label={__('Show form only if in countries', 'eightshift-forms')} />}
						help={
							geoRepeater?.length > 0
								? __('Overriden by geolocation rules.', 'eightshift-forms')
								: (
									<>
										<p>{__('If you can\'t find a country, start typing its name while the dropdown is open.', 'eightshift-forms')}</p>
									</>
								)
						}
						value={formsFormGeolocation}
						loadOptions={geoLocationOptions}
						onChange={(value) => setAttributes({ [getAttrKey('formsFormGeolocation', attributes, manifest)]: value })}
						multiple={true}
						disabled={geoRepeater?.length > 0}
					/>

					<Button
						isSecondary
						icon={icons.locationSettings}
						onClick={() => {
							setPrevGeoRepeater([...geoRepeater]);
							setIsModalOpen(true);
						}}
					>
						{__('Geolocation rules', 'eightshift-forms')}
					</Button>

					{geoRepeater?.length > 0 &&
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
							shouldCloseOnClickOutside={false}
							shouldCloseOnEsc={false}
							isDismissible={false}
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
								onClick={addItem}
							>
								{__('Add rule', 'eightshift-forms')}
							</Button>

							{geoRepeater?.length > 0 &&
								<div className='es-fifty-fifty-auto-h es-has-wp-field-t-space'>
									<IconLabel icon={<BlockIcon iconName='esf-form' />} label={__('Form to display', 'eightshift-forms')} standalone />
									<IconLabel icon={icons.locationAllow} label={__('Countries to show the form in', 'eightshift-forms')} standalone />
									<div style={{ width: '2.25rem' }}>&nbsp;</div>
								</div>
							}

							{geoRepeater.map((_, index) => {
								return (
									<GeoLocationModalItem
										key={index}
										index={index}
										data={geoRepeater[index]}
										handleChange={(value, index, key) => {
											geoRepeater[index][key] = value;
											setGeoRepeater([...geoRepeater]);
										}}
										removeItem={(index) => {
											geoRepeater.splice(index, 1);
											setGeoRepeater([...geoRepeater]);
										}}
									/>
								);
							})}

							{geoRepeater &&
								<div className='es-h-end es-has-wp-field-t-space'>
									<Button onClick={() => {
										setGeoRepeater(prevGeoRepeater);
										setAttributes({ formsFormGeolocationAlternatives: prevGeoRepeater });
										setIsModalOpen(false);
									}}>
										{__('Cancel', 'eightshift-forms')}
									</Button>

									<Button
										isPrimary
										onClick={() => {
											setIsModalOpen(false);
											setAttributes({ formsFormGeolocationAlternatives: geoRepeater });
										}}
									>
										{__('Save', 'eightshift-forms')}
									</Button>
								</div>
							}
						</Modal>
					)}
				</>
			}
		</PanelBody>
	);
};
