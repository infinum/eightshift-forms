/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { isArray } from 'lodash';
import { select } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { MediaPlaceholder } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button, Modal, ExternalLink } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import {
	IconLabel,
	icons,
	getAttrKey,
	checkAttr,
	getFetchWpApi,
	unescapeHTML,
	STORE_NAME,
	props,
	AsyncSelect,
	MultiSelect,
	Select,
	IconToggle,
	Control,
	Section,
	truncateMiddle,
	Collapsable,
} from '@eightshift/frontend-libs/scripts';
import { ConditionalTagsFormsOptions } from '../../../components/conditional-tags/components/conditional-tags-forms-options';
import { getSettingsJsonOptions } from '../../../components/utils';
import manifest from '../manifest.json';

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
	const formsFormPostIdRaw = checkAttr('formsFormPostIdRaw', attributes, manifest);
	const formsStyle = checkAttr('formsStyle', attributes, manifest);
	const formsFormDataTypeSelector = checkAttr('formsFormDataTypeSelector', attributes, manifest);
	const formsFormGeolocation = checkAttr('formsFormGeolocation', attributes, manifest);
	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);
	const formsDownloads = checkAttr('formsDownloads', attributes, manifest);
	const formsSuccessRedirectVariation = checkAttr('formsSuccessRedirectVariation', attributes, manifest);
	const formsSuccessRedirectVariationUrl = checkAttr('formsSuccessRedirectVariationUrl', attributes, manifest);

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

		geolocationApi = `${esFormsLocalization.restPrefix}/${esFormsLocalization.restRoutes.countriesGeolocation}`;
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



	return (
		<>
			<PanelBody title={__('Form', 'eightshift-forms')}>
				<AsyncSelect
					help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
					value={formsFormPostIdRaw ?? (formsFormPostId ? { label: 'Selected item', id: parseInt(formsFormPostId ?? -1) } : null)}
					loadOptions={formSelectOptions}
					onChange={(value) => setAttributes({
						[getAttrKey('formsFormPostIdRaw', attributes, manifest)]: value,
						[getAttrKey('formsFormPostId', attributes, manifest)]: `${value?.value}`,
					})}
				/>

				{formsFormPostId &&
					<Control>
						<div className='es-fifty-fifty-h es-gap-2!'>
							<Button
								icon={icons.edit}
								href={`${wpAdminUrl}${editFormUrl}&post=${formsFormPostId}`}
								className='es-rounded-1.5 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
							>
								{__('Edit fields', 'eightshift-forms')}
							</Button>

							<Button
								icon={icons.options}
								href={`${wpAdminUrl}${settingsPageUrl}&formId=${formsFormPostId}`}
								className='es-rounded-1.5 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
							>
								{__('Settings', 'eightshift-forms')}
							</Button>
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

				<Section icon={icons.file} label={__('"Thank you" page', 'eightshift-forms')} noBottomSpacing>
					<Select
						icon={icons.paletteColor}
						label={__('Variant', 'eightshift-forms')}
						value={formsSuccessRedirectVariation}
						options={getSettingsJsonOptions(successRedirectVariations)}
						onChange={(value) => {
							setAttributes({ [getAttrKey('formsSuccessRedirectVariation', attributes, manifest)]: value });
						}}
						additionalSelectClasses='es-w-36'
						simpleValue
						inlineLabel
						clearable
					/>

					<TextControl
						label={<IconLabel icon={icons.anchor} label={__('Url', 'eightshift-forms')} />}
						help={__('Additional url and file downloads that will be passed to the "Thank you" page.', 'eightshift-forms')}
						value={formsSuccessRedirectVariationUrl}
						onChange={(value) => setAttributes({ [getAttrKey('formsSuccessRedirectVariationUrl', attributes, manifest)]: value })}
					/>

					<Collapsable
						icon={icons.fileDownload}
						label={__('File downloads', 'eightshift-forms')}
						subtitle={formsDownloads?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), formsDownloads?.length)}
						noBottomSpacing
					>
						<Control reducedBottomSpacing={formsDownloads?.length > 0} noBottomSpacing={formsDownloads?.length < 1}>
							<MediaPlaceholder
								icon={icons.image}
								multiple
								onSelect={(value) => {
									const items = value.map((item) => {
										const mimeType = item?.mime_type ?? item?.mime ?? '';

										return {
											title: item?.filename ?? item?.slug ?? 'UNKNOWN',
											id: item.id,
											isImage: mimeType?.startsWith('image/'),
										};
									});
									setAttributes({ [getAttrKey('formsDownloads', attributes, manifest)]: [...formsDownloads, ...items] });
								}}
							/>
						</Control>

						{formsDownloads.map((item, index) => {
							return (
								<Control
									key={index}
									icon={item?.isImage ? icons.image : icons.file}
									label={truncateMiddle(item.title, 28)}
									inlineLabel
									reducedBottomSpacing={index < formsDownloads.length - 1}
									noBottomSpacing={index === formsDownloads.length - 1}
								>
									<Button
										onClick={() => {
											delete formsDownloads[index];
											const item = formsDownloads.filter((_, i) => i !== index);
											setAttributes({ [getAttrKey('formsDownloads', attributes, manifest)]: item });
										}}
										icon={icons.trash}
										className='es-button-icon-24 es-button-square-28 es-rounded-1 es-hover-color-red-500 es-nested-color-current es-transition-colors'
									/>
								</Control>
							);
						})}
					</Collapsable>

				</Section>



				{formsStyleOptions?.length > 0 &&
					<Select
						icon={icons.paletteColor}
						label={__('Form style preset', 'eightshift-forms')}
						value={formsStyle}
						options={formsStyleOptions}
						onChange={(value) => setAttributes({ [getAttrKey('formsStyle', attributes, manifest)]: value })}
						simpleValue
						isSearchable={false}
						isClearable={false}
					/>
				}
			</PanelBody>

			<ConditionalTagsFormsOptions
				{...props('conditionalTags', attributes, {
					setAttributes,
					conditionalTagsPostId: formsFormPostId,
				})}
			/>

			{formsUseGeolocation &&
				<PanelBody title={__('Geolocation', 'eightshift-forms')} initialOpen={false}>
					<MultiSelect
						label={__('Show form only if in these countries:', 'eightshift-forms')}
						help={formsFormGeolocationAlternatives?.length < 1 && __('If you can\'t find a country, start typing its name while the dropdown is open.', 'eightshift-forms')}
						value={formsFormGeolocationAlternatives?.length > 0 ? [] : formsFormGeolocation}
						options={formFields}
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
							onClick={() => setIsModalOpen(true)}
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

					{isModalOpen && (
						<Modal
							overlayClassName='es-geolocation-modal'
							className='es-modal-max-width-xxl es-rounded-3!'
							title={<IconLabel icon={icons.locationSettings} label={__('Advanced rules', 'eightshift-forms')} standalone />}
							onRequestClose={() => setIsModalOpen(false)}
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
									<div className='es-h-spaced' key={index}>
										<AsyncSelect
											value={formsFormGeolocationAlternatives?.[index]?.form}
											loadOptions={formSelectOptions}
											onChange={(value) => {
												const newData = [...formsFormGeolocationAlternatives];
												newData[index].form = value;
												newData[index].formId = value.value.toString();
												setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: newData });
											}}
											additionalSelectClasses='es-w-64'
											noBottomSpacing
										/>

										<MultiSelect
											value={formsFormGeolocationAlternatives?.[index]?.geoLocation}
											options={formFields}
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
									onClick={() => setIsModalOpen(false)}
									className='es-rounded-1.5!'
								>
									{__('Close', 'eightshift-forms')}
								</Button>
							</div>
						</Modal>
					)}
				</PanelBody>
			}
		</>
	);
};
