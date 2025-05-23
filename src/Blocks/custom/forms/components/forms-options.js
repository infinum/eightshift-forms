/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { MediaPlaceholder } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { icons } from '@eightshift/ui-components/icons';
import { getAttrKey, checkAttr, props, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { ConditionalTagsFormsOptions } from '../../../components/conditional-tags/components/conditional-tags-forms-options';
import { FormEditButton, LocationsButton, SettingsButton, outputFormSelectItemWithIcon } from '../../../components/utils';
import { getRestUrl } from '../../../components/form/assets/state-init';
import { AsyncSelect, InputField, Toggle, BaseControl, MultiSelect, Repeater, RepeaterItem, Button, Modal, ContainerPanel } from '@eightshift/ui-components';

export const FormsOptions = ({ attributes, setAttributes, preview, formSelectOptions }) => {
	const manifest = select(STORE_NAME).getBlock('forms');

	const { isGeoPreview, setIsGeoPreview } = preview;

	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);
	const formsFormPostIdRaw = checkAttr('formsFormPostIdRaw', attributes, manifest);
	const formsStyle = checkAttr('formsStyle', attributes, manifest);
	const formsFormDataTypeSelector = checkAttr('formsFormDataTypeSelector', attributes, manifest);
	const formsFormGeolocation = checkAttr('formsFormGeolocation', attributes, manifest);
	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);
	const formsVariation = checkAttr('formsVariation', attributes, manifest);
	const formsVariationData = checkAttr('formsVariationData', attributes, manifest);
	const formsVariationDataFiles = checkAttr('formsVariationDataFiles', attributes, manifest);

	const [isGeoModalOpen, setIsGeoModalOpen] = useState(false);
	const [isResultOutputModalOpen, setIsResultOutputModalOpen] = useState(false);
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
	let formsUseCustomResultOutputFeature = false;
	let geolocationApi = '';

	if (typeof esFormsLocalization !== 'undefined') {
		// Custom block forms style options.
		if (Array.isArray(esFormsLocalization?.formsBlockStyleOptions)) {
			formsStyleOptions = esFormsLocalization.formsBlockStyleOptions;
		}

		// Use custom result output feature.
		if (esFormsLocalization?.formsUseCustomResultOutputFeature) {
			formsUseCustomResultOutputFeature = esFormsLocalization.formsUseCustomResultOutputFeature;
		}

		// Is geolocation active.
		if (esFormsLocalization?.use?.geolocation) {
			formsUseGeolocation = true;

			geolocationApi = getRestUrl('countriesGeolocation');
		}
	}

	return (
		<>
			<ContainerPanel title={__('Form', 'eightshift-forms')}>
				<AsyncSelect
					help={__("If you can't find a form, start typing its name while the dropdown is open.", 'eightshift-forms')}
					value={outputFormSelectItemWithIcon(Object.keys(formsFormPostIdRaw ?? {}).length ? formsFormPostIdRaw : { id: formsFormPostId })}
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

				{formsFormPostId && (
					<BaseControl>
						<div className='es-fifty-fifty-h es-gap-2!'>
							<FormEditButton formId={formsFormPostId} />
							<SettingsButton formId={formsFormPostId} />
							<LocationsButton formId={formsFormPostId} />
						</div>
					</BaseControl>
				)}

				<BaseControl
					icon={icons.tools}
					label={__('Advanced', 'eightshift-forms')}
				>
					<InputField
						icon={icons.codeVariable}
						label={__('Additional type specifier', 'eightshift-forms')}
						help={__('Additional data type selectors', 'eightshift-forms')}
						value={formsFormDataTypeSelector}
						onChange={(value) => setAttributes({ [getAttrKey('formsFormDataTypeSelector', attributes, manifest)]: value })}
					/>
				</BaseControl>

				{formsStyleOptions?.length > 0 && (
					<MultiSelect
						icon={icons.paletteColor}
						label={__('Form style preset', 'eightshift-forms')}
						value={formsStyle}
						options={formsStyleOptions}
						onChange={(value) => setAttributes({ [getAttrKey('formsStyle', attributes, manifest)]: value })}
						simpleValue
					/>
				)}
			</ContainerPanel>

			<ContainerPanel
				title={__('Results output', 'eightshift-forms')}
				initialOpen={true}
			>
				<Repeater
					noReordering
					icon={icons.paletteColor}
					label={__('Variation', 'eightshift-forms')}
					items={formsVariation}
					attributeName={getAttrKey('formsVariation', attributes, manifest)}
					setAttributes={setAttributes}
				>
					{formsVariation.map((item, index) => (
						<RepeaterItem
							key={index}
							icon={icons.paletteColor}
							title={item?.title}
						>
							<div className='es-fifty-fifty-h'>
								<InputField
									value={item.title}
									label={__('Key', 'eightshift-forms')}
									onChange={(value) => {
										const newArray = [...formsVariation];
										newArray[index].title = value;

										setAttributes({ [getAttrKey('formsVariation', attributes, manifest)]: newArray });
									}}
								/>
								<InputField
									value={item.slug}
									label={__('Value', 'eightshift-forms')}
									onChange={(value) => {
										const newArray = [...formsVariation];
										newArray[index].slug = value;

										setAttributes({ [getAttrKey('formsVariation', attributes, manifest)]: newArray });
									}}
								/>
							</div>
						</RepeaterItem>
					))}
				</Repeater>

				{formsUseCustomResultOutputFeature && (
					<>
						<Button
							variant='secondary'
							onClick={() => setIsResultOutputModalOpen(true)}
							className='es-rounded-1.5 es-font-weight-500'
						>
							{__('Edit custom result output', 'eightshift-forms')}
						</Button>

						{isResultOutputModalOpen && (
							<Modal
								size='large'
								overlayClassName='es-result-output-modal'
								className='es-modal-max-width-xxl es-rounded-3!'
								title={__('Results output', 'eightshift-forms')}
								onRequestClose={() => setIsResultOutputModalOpen(false)}
							>
								<InputField
									value={formsVariationData?.title}
									placeholder={__('Title', 'eightshift-forms')}
									onChange={(value) => {
										const newArray = { ...formsVariationData };
										newArray.title = value;

										setAttributes({ [getAttrKey('formsVariationData', attributes, manifest)]: newArray });
									}}
								/>

								<InputField
									value={formsVariationData?.subtitle}
									placeholder={__('Subtitle', 'eightshift-forms')}
									onChange={(value) => {
										const newArray = { ...formsVariationData };
										newArray.subtitle = value;

										setAttributes({ [getAttrKey('formsVariationData', attributes, manifest)]: newArray });
									}}
								/>

								<Repeater
									noReordering
									icon={icons.emptyCircle}
									label={__('Add a new item', 'eightshift-forms')}
									items={formsVariationDataFiles}
									attributeName={getAttrKey('formsVariationDataFiles', attributes, manifest)}
									setAttributes={setAttributes}
								>
									{formsVariationDataFiles.map((item, index) => (
										<RepeaterItem
											key={index}
											icon={icons.emptyCircle}
											title={item?.title}
										>
											<div className='es-border-t-cool-gray-300 es-mt-2 es-pt-2 es-fifty-fifty-h'>
												<InputField
													value={item.label}
													placeholder={__('Label', 'eightshift-forms')}
													onChange={(value) => {
														const newArray = [...formsVariationDataFiles];
														newArray[index].label = value;

														setAttributes({ [getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray });
													}}
												/>
												<InputField
													value={item.title}
													placeholder={__('Title', 'eightshift-forms')}
													onChange={(value) => {
														const newArray = [...formsVariationDataFiles];
														newArray[index].title = value;

														setAttributes({ [getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray });
													}}
												/>
											</div>

											<Toggle
												checked={item.asFile}
												label={__('Use this item as a file or as a link', 'eightshift-forms')}
												reducedBottomSpacing
												onChange={(value) => {
													const newArray = [...formsVariationDataFiles];
													newArray[index].asFile = value;

													if (value) {
														delete newArray[index].url;
													} else {
														delete newArray[index].file;
													}

													setAttributes({ [getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray });
												}}
											/>

											{!formsVariationDataFiles[index].asFile && (
												<InputField
													placeholder={__('Link URL', 'eightshift-forms')}
													value={item.url}
													onChange={(value) => {
														const newArray = [...formsVariationDataFiles];
														newArray[index].url = value;

														setAttributes({ [getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray });
													}}
												/>
											)}

											{formsVariationDataFiles[index].asFile && (
												<>
													{!formsVariationDataFiles[index].file && (
														<MediaPlaceholder
															icon={icons.image}
															onSelect={(value) => {
																const newArray = [...formsVariationDataFiles];
																newArray[index].file = {
																	id: value.id,
																	title: value.title,
																	url: value.url,
																};

																setAttributes({ [getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray });
															}}
														/>
													)}

													{formsVariationDataFiles[index]?.file && (
														<div className='es-border-t-cool-gray-300 es-mt-2 es-pt-2 es-h-between'>
															<div className='es-h-between'>
																{icons.file}
																{formsVariationDataFiles[index]?.file?.title}
															</div>
															<Button
																onClick={() => {
																	const newArray = [...formsVariationDataFiles];
																	delete newArray[index]?.file;
																	setAttributes({ [getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray });
																}}
																icon={icons.trash}
																className='es-button-icon-24 es-button-square-28 es-rounded-1 es-hover-color-red-500 es-nested-color-current es-transition-colors'
															/>
														</div>
													)}
												</>
											)}

											<div className='es-border-t-cool-gray-300 es-mt-2 es-pt-2 es-fifty-fifty-h'>
												<InputField
													placeholder={__('Field Name', 'eightshift-forms')}
													value={item.fieldName}
													onChange={(value) => {
														const newArray = [...formsVariationDataFiles];
														newArray[index].fieldName = value;

														setAttributes({ [getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray });
													}}
												/>
												<InputField
													value={item.fieldValue}
													placeholder={__('Field Value', 'eightshift-forms')}
													onChange={(value) => {
														const newArray = [...formsVariationDataFiles];
														newArray[index].fieldValue = value;

														setAttributes({ [getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray });
													}}
												/>
											</div>
										</RepeaterItem>
									))}
								</Repeater>
							</Modal>
						)}
					</>
				)}
			</ContainerPanel>

			{formsUseGeolocation && (
				<ContainerPanel
					title={__('Geolocation', 'eightshift-forms')}
					initialOpen={false}
				>
					<MultiSelect
						label={__('Show form only if in these countries:', 'eightshift-forms')}
						help={formsFormGeolocationAlternatives?.length < 1 && __("If you can't find a country, start typing its name while the dropdown is open.", 'eightshift-forms')}
						value={formsFormGeolocationAlternatives?.length > 0 ? [] : formsFormGeolocation}
						options={geoFormFields}
						onChange={(value) => setAttributes({ [getAttrKey('formsFormGeolocation', attributes, manifest)]: value })}
						cacheOptions={false}
						simpleValue
						disabled={formsFormGeolocationAlternatives?.length > 0}
						placeholder={formsFormGeolocationAlternatives?.length > 0 && __('Overriden by advanced rules', 'eightshift-forms')}
					/>

					<BaseControl
						icon={icons.locationSettings}
						label={__('Advanced rules', 'eightshift-forms')}
						// Translators: %d refers to the number of active rules
						subtitle={formsFormGeolocationAlternatives?.length > 0 && sprintf(__('%d added', 'eightshift-forms'), formsFormGeolocationAlternatives.length)}
						reducedBottomSpacing={formsFormGeolocationAlternatives?.length > 0}
						inline
					>
						<Button
							variant='tertiary'
							onClick={() => setIsGeoModalOpen(true)}
							className='es-rounded-1.5 es-w-9 es-h-center es-font-weight-500'
						>
							{formsFormGeolocationAlternatives?.length > 0 ? __('Edit', 'eightshift-forms') : __('Add', 'eightshift-forms')}
						</Button>
					</BaseControl>

					{formsFormGeolocationAlternatives?.length > 0 && (
						<Toggle
							icon={icons.visible}
							label={__('Rule preview', 'eightshift-forms')}
							checked={isGeoPreview}
							onChange={(value) => setIsGeoPreview(value)}
						/>
					)}

					{isGeoModalOpen && (
						<Modal
							overlayClassName='es-geolocation-modal'
							className='es-modal-max-width-xxl es-rounded-3!'
							icon={icons.locationSettings}
							title={__('Advanced rules', 'eightshift-forms')}
							onRequestClose={() => setIsGeoModalOpen(false)}
						>
							<p>{__("Geolocation rules allow you to display alternate forms based on the user's location.", 'eightshift-forms')}</p>
							<p>
								{__(
									'If no rules are added and the "Show form only if in countries" field is populated, the form will only be shown in these countries. Otherwise, the form is shown everywhere.',
									'eightshift-forms',
								)}
							</p>

							{geolocationApi && (
								<p>
									{__('You can find complete list of countries and regions on this', 'eightshift-forms')}{' '}
									<Button onClick={() => window.open(geolocationApi, '_blank')}>{__('link', 'eightshift-forms')}</Button>.
								</p>
							)}

							<br />

							{formsFormGeolocationAlternatives?.length > 0 && (
								<div className='es-h-spaced es-pb-2 es-mb-2 es-border-b-cool-gray-300'>
									<span className='es-w-64'>{__('Form to display', 'eightshift-forms')}</span>
									<span className='es-w-80'>{__('Countries to show the form in', 'eightshift-forms')}</span>
								</div>
							)}

							{formsFormGeolocationAlternatives?.map((_, index) => {
								return (
									<div
										className='es-h-spaced es-mb-2'
										key={index}
									>
										<AsyncSelect
											value={outputFormSelectItemWithIcon(
												Object.keys(formsFormGeolocationAlternatives?.[index]?.form ?? {}).length
													? formsFormGeolocationAlternatives?.[index]?.form
													: { id: formsFormGeolocationAlternatives?.[index]?.formId },
											)}
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
								onClick={() =>
									setAttributes({ [getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: [...formsFormGeolocationAlternatives, { formId: '', geoLocation: [] }] })
								}
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
				</ContainerPanel>
			)}

			<ConditionalTagsFormsOptions
				{...props('conditionalTags', attributes, {
					setAttributes,
					conditionalTagsPostId: formsFormPostId,
				})}
			/>
		</>
	);
};
