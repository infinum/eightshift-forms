/* global esFormsLocalization */

import { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ExternalLink } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { getAttrKey, checkAttr, props, fetchFromWpRest, ManageFileButton } from '@eightshift/frontend-libs-tailwind/scripts';
import { BaseControl, MultiSelect, AsyncSelect, Repeater, RepeaterItem, Button, ContainerPanel, InputField, Container, Tabs, TabList, Tab, TabPanel, ButtonGroup, ContainerGroup, OptionSelect, FilePickerShell, ToggleButton, ItemCollection, HStack, Checkbox } from '@eightshift/ui-components';
import { blockParts, codeVariable, file, form, locationSettings, moreH, trash, visible, branch, link, optionListAlt, plusCircle, locationAllow, fieldReadonly, location } from '@eightshift/ui-components/icons';
import { ConditionalTagsFormsOptions } from '../../../components/conditional-tags/components/conditional-tags-forms-options';
import { FormEditButton, LocationsButton, SettingsButton } from '../../../components/utils';
import { getRestUrl, getUtilsIcons } from '../../../components/form/assets/state-init';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';
import manifest from '../manifest.json';

const FilePicker = ({ onChange, fileUrl, fileId }) => {
	return (
		<FilePickerShell
			url={fileUrl}
			type='file'
			noUrlContent={
				<>
					<ManageFileButton onChange={onChange} />

					<ManageFileButton
						onChange={onChange}
						type='upload'
					/>
				</>
			}
		>
			<ManageFileButton
				type='replace'
				onChange={onChange}
				currentId={fileId}
				buttonProps={{
					className: 'es:grow',
				}}
			/>

			<Button
				onPress={() => onChange({ id: undefined, url: undefined })}
				className='es:grow'
			>
				{__('Remove', 'eightshift-frontend-libs-tailwind')}
			</Button>
		</FilePickerShell>
	);
};

export const FormsOptions = ({ attributes, setAttributes, preview }) => {
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

	const [geoFormFields, setGeoFormFields] = useState([]);

	useEffect(() => {
		apiFetch({
			path: getRestUrl('countriesGeolocation', true),
		}).then((response) => {
			if (response.code === 200) {
				setGeoFormFields(response?.data?.countries);
			}
		});
	}, []);

	let formsStyleOptions = [];
	let formsUseGeolocation = false;
	let formsUseCustomResultOutputFeature = false;
	let geolocationApi = '';

	if (typeof esFormsLocalization !== 'undefined') {
		// Custom block forms style options.
		if (typeof esFormsLocalization !== 'undefined') {
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
			<Tabs>
				<TabList>
					<Tab
						icon={form}
						label={__('Form', 'eightshift-forms')}
					/>

					<Tab
						icon={blockParts}
						label={__('Result outputs', 'eightshift-forms')}
						invisible={!formsUseCustomResultOutputFeature || !formsFormPostId}
					/>

					<Tab
						icon={location}
						label={__('Geolocation', 'eightshift-forms')}
						invisible={!formsUseGeolocation || !formsFormPostId}
					/>

					<Tab
						icon={fieldReadonly}
						label={__('Field visibility', 'eightshift-forms')}
						invisible={!formsFormPostId}
					/>

					<Tab
						icon={moreH}
						label={__('Advanced', 'eightshift-forms')}
						invisible={!formsFormPostId}
					/>
				</TabList>

				<TabPanel>
					<ContainerPanel>
						<AsyncSelect
							aria-label={__('Form to display', 'eightshift-forms')}
							value={Object.keys(formsFormPostIdRaw ?? {}).length ? formsFormPostIdRaw : { id: formsFormPostId }}
							fetchFunction={fetchFromWpRest(esFormsLocalization?.postTypes?.forms, {
								noCache: true,
								processLabel: ({ title: { rendered: label } }) => label,
								fields: 'id,title,integration_type',
								processMetadata: ({ title: { rendered: label }, integration_type: metadata, id }) => ({
									id,
									value: id,
									label,
									metadata,
								}),
							})}
							customValueDisplay={(item) => (
								<span className='esf:flex esf:items-center esf:gap-10'>
									<span
										dangerouslySetInnerHTML={{
											__html: getUtilsIcons(item?.metadata?.metadata || 'post'),
										}}
									/>
									{item?.label}
								</span>
							)}
							customMenuOption={(item) => (
								<span className='esf:flex esf:items-center esf:gap-10'>
									<span
										dangerouslySetInnerHTML={{
											__html: getUtilsIcons(item?.metadata?.metadata || 'post'),
										}}
									/>
									{item?.label}
								</span>
							)}
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

						<ButtonGroup hidden={!formsFormPostId}>
							<FormEditButton formId={formsFormPostId} />
							<SettingsButton formId={formsFormPostId} />
							<LocationsButton formId={formsFormPostId} />
						</ButtonGroup>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<Tabs type='chips'>
							<TabList>
								<Tab label={__('Simple (key/value)', 'eightshift-forms')} />
								<Tab label={__('Custom', 'eightshift-forms')} />
							</TabList>

							<TabPanel>
								<ContainerGroup>
									<ItemCollection
										icon={branch}
										label={__('Variations', 'eightshift-forms')}
										items={formsVariation}
										onChange={(value) => setAttributes({ [getAttrKey('formsVariation', attributes, manifest)]: value })}
										addDefaultItem={{
											title: '',
											slug: '',
										}}
										noExpandAllButton
									>
										{(item) => {
											const { title, slug, updateData, deleteItem } = item;

											return (
												<Container
													className='esf:group'
													lessSpaceStart
													lessSpaceEnd
												>
													<HStack noWrap>
														<InputField
															aria-label={__('Key', 'eightshift-forms')}
															placeholder={__('Key', 'eightshift-forms')}
															value={title}
															onChange={(value) => updateData({ title: value })}
															className='esf:max-w-96!'
															monospaceFont
														/>

														<span>:</span>

														<InputField
															aria-label={__('Value', 'eightshift-forms')}
															placeholder={__('Value', 'eightshift-forms')}
															value={slug}
															onChange={(value) => updateData({ slug: value })}
															monospaceFont
															className='esf:max-w-120!'
														/>

														<Button
															icon={trash}
															onClick={deleteItem}
															label={__('Remove', 'eightshift-forms')}
															size='small'
															type='dangerGhost'
															className='esf:ml-auto esf:not-group-hover:not-group-has-focus-visible:opacity-0'
														/>
													</HStack>
												</Container>
											);
										}}
									</ItemCollection>

									<Container
										lessSpaceStart
										lessSpaceEnd
									>
										<Button
											aria-label={__('Add variation', 'eightshift-forms')}
											icon={plusCircle}
											onClick={() =>
												setAttributes({
													[getAttrKey('formsVariation', attributes, manifest)]: [...formsVariation, { title: '', slug: '' }],
												})
											}
											className='esf:w-full'
										>
											{__('Variation', 'eightshift-forms')}
										</Button>
									</Container>
								</ContainerGroup>
							</TabPanel>

							<TabPanel>
								<ContainerGroup title={__('Custom variation', 'eightshift-forms')}>
									<Container>
										<InputField
											label={__('Title', 'eightshift-forms')}
											value={formsVariationData?.title}
											onChange={(value) => {
												const newArray = { ...formsVariationData };
												newArray.title = value;

												setAttributes({ [getAttrKey('formsVariationData', attributes, manifest)]: newArray });
											}}
											inline
										/>
									</Container>

									<Container>
										<InputField
											label={__('Subtitle', 'eightshift-forms')}
											value={formsVariationData?.subtitle}
											onChange={(value) => {
												const newArray = { ...formsVariationData };
												newArray.subtitle = value;

												setAttributes({ [getAttrKey('formsVariationData', attributes, manifest)]: newArray });
											}}
											inline
										/>
									</Container>
								</ContainerGroup>

								<Repeater
									noReordering
									label={__('Values', 'eightshift-forms')}
									items={formsVariationDataFiles}
									onChange={(value) =>
										setAttributes({
											[getAttrKey('formsVariationDataFiles', attributes, manifest)]: value,
										})
									}
								>
									{(item) => {
										const { title, label, itemIndex: index, updateData } = item;

										return (
											<RepeaterItem label={title || __('New value', 'eightshift-forms')}>
												<ContainerGroup label={__('Variation', 'eightshift-forms')}>
													<Container>
														<InputField
															label={__('Label', 'eightshift-forms')}
															value={label}
															onChange={(value) => updateData({ label: value })}
															inline
														/>
													</Container>

													<Container>
														<InputField
															label={__('Title', 'eightshift-forms')}
															value={title}
															onChange={(value) => updateData({ title: value })}
															inline
														/>
													</Container>
												</ContainerGroup>

												<ContainerGroup>
													<Container>
														<OptionSelect
															icon={optionListAlt}
															label={__('Type', 'eightshift-forms')}
															value={item.asFile ? 'file' : 'url'}
															options={[
																{ value: 'url', label: __('Link', 'eightshift-forms'), icon: link },
																{ value: 'file', label: __('File', 'eightshift-forms'), icon: file },
															]}
															onChange={(value) => {
																if (value === 'file') {
																	updateData({ url: undefined, asFile: true });
																} else {
																	updateData({ file: undefined, asFile: false });
																}
															}}
															inline
														/>
													</Container>

													<Container hidden={item.asFile}>
														<InputField
															icon={link}
															label={__('URL', 'eightshift-forms')}
															value={item.url}
															onChange={(value) => {
																const newArray = [...formsVariationDataFiles];
																newArray[index].url = value;

																setAttributes({
																	[getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray,
																});
															}}
															monospaceFont
															inline
														/>
													</Container>

													<Container hidden={!item.asFile}>
														<FilePicker
															onChange={({ id, url, title }) => {
																updateData({
																	file: {
																		id,
																		url,
																		title,
																	},
																});
															}}
															fileId={item?.file?.id}
															fileUrl={item?.file?.url?.substring(item?.file?.url?.lastIndexOf('/') + 1)}
														/>
													</Container>
												</ContainerGroup>

												<ContainerGroup label={__('Field', 'eightshift-forms')}>
													<Container>
														<InputField
															label={__('Name', 'eightshift-forms')}
															value={item.fieldName}
															onChange={(value) => {
																const newArray = [...formsVariationDataFiles];
																newArray[index].fieldName = value;

																setAttributes({
																	[getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray,
																});
															}}
															inline
														/>
													</Container>

													<Container>
														<InputField
															label={__('Value', 'eightshift-forms')}
															value={item.fieldValue}
															onChange={(value) => {
																const newArray = [...formsVariationDataFiles];
																newArray[index].fieldValue = value;

																setAttributes({
																	[getAttrKey('formsVariationDataFiles', attributes, manifest)]: newArray,
																});
															}}
															monospaceFont
															inline
														/>
													</Container>
												</ContainerGroup>
											</RepeaterItem>
										);
									}}
								</Repeater>
							</TabPanel>
						</Tabs>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<Container standalone>
							<MultiSelect
								icon={locationAllow}
								label={__('Show form in', 'eightshift-forms')}
								value={formsFormGeolocationAlternatives?.length > 0 ? [] : formsFormGeolocation}
								options={geoFormFields}
								onChange={(value) => setAttributes({ [getAttrKey('formsFormGeolocation', attributes, manifest)]: value })}
								disabled={formsFormGeolocationAlternatives?.length > 0}
								placeholder={formsFormGeolocationAlternatives?.length > 0 ? __('Overriden by advanced rules', 'eightshift-forms') : __('Select locations', 'eightshift-forms')}
								simpleValue
								noMinWidth
								searchable
							/>
						</Container>

						<ContainerGroup>
							<Container centered>
								<BaseControl
									icon={locationSettings}
									label={__('Advanced geolocation rules', 'eightshift-forms')}
									className='esf:w-full'
									inline
								>
									<HelpTooltip className='esf:flex esf:flex-col esf:gap-12'>
										<span>{__("Geolocation rules allow you to display alternate forms based on the user's location.", 'eightshift-forms')}</span>

										<span>{__('If no rules are added and the "Show form only if in countries" field is populated, the form will only be shown in these countries. Otherwise, the form is shown everywhere.', 'eightshift-forms')}</span>

										{geolocationApi && (
											<span>
												{__('You can find complete list of countries and regions on this', 'eightshift-forms')} <ExternalLink href={geolocationApi}>{__('link', 'eightshift-forms')}</ExternalLink>.
											</span>
										)}
									</HelpTooltip>
								</BaseControl>
							</Container>

							<ItemCollection
								items={formsFormGeolocationAlternatives}
								onChange={(value) =>
									setAttributes({
										[getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: value,
									})
								}
							>
								{(item) => {
									const { form, geoLocation, updateData, deleteItem } = item;

									return (
										<Container lessSpaceEnd>
											<div className='esf:grid esf:grid-cols-[auto_1fr_auto] esf:grid-rows-2 esf:gap-y-4 esf:gap-x-8 esf:items-center esf:group'>
												<span className='esf:col-1 esf:row-1'>{__('Show', 'eightshift-forms')}</span>

												<AsyncSelect
													className='esf:col-2 esf:row-1'
													value={Object.keys(form ?? {}).length ? form : { id: item?.formId }}
													fetchFunction={fetchFromWpRest(esFormsLocalization?.postTypes?.forms, {
														noCache: true,
														processLabel: ({ title: { rendered: label } }) => label,
														fields: 'id,title,integration_type',
														processMetadata: ({ title: { rendered: label }, integration_type: metadata, id }) => ({
															id,
															value: id,
															label,
															metadata,
														}),
													})}
													customValueDisplay={(item) => (
														<span className='esf:flex esf:items-center esf:gap-10'>
															<span
																dangerouslySetInnerHTML={{
																	__html: getUtilsIcons(item?.metadata?.metadata || 'post'),
																}}
															/>
															{item?.label}
														</span>
													)}
													customMenuOption={(item) => (
														<span className='esf:flex esf:items-center esf:gap-10'>
															<span
																dangerouslySetInnerHTML={{
																	__html: getUtilsIcons(item?.metadata?.metadata || 'post'),
																}}
															/>
															{item?.label}
														</span>
													)}
													onChange={(value) => {
														updateData({
															form: {
																id: value?.id,
																label: value?.metadata?.label,
																value: value?.metadata?.value,
																metadata: value?.metadata?.metadata,
															},
														});
													}}
												/>

												<span className='esf:col-1 esf:row-2'>{__('if in', 'eightshift-forms')}</span>

												<MultiSelect
													className='esf:col-2 esf:row-2'
													value={geoLocation}
													options={geoFormFields}
													onChange={(value) => updateData({ geoLocation: value })}
													simpleValue
													searchable
												/>

												<Button
													icon={trash}
													onClick={deleteItem}
													label={__('Remove', 'eightshift-forms')}
													className='esf:row-1 esf:col-3 esf:not-group-hover:not-group-focus-within:opacity-0'
													type='dangerGhost'
													size='small'
												/>
											</div>
										</Container>
									);
								}}
							</ItemCollection>

							<Container
								lessSpaceStart
								lessSpaceEnd
							>
								<Button
									aria-label={__('Add rule', 'eightshift-forms')}
									icon={plusCircle}
									onClick={() =>
										setAttributes({
											[getAttrKey('formsFormGeolocationAlternatives', attributes, manifest)]: [...formsFormGeolocationAlternatives, { formId: '', geoLocation: [] }],
										})
									}
									className='esf:w-full'
								>
									{__('Rule', 'eightshift-forms')}
								</Button>
							</Container>
						</ContainerGroup>

						{formsFormGeolocationAlternatives?.length > 0 && (
							<ToggleButton
								icon={visible}
								selected={isGeoPreview}
								onChange={(value) => setIsGeoPreview(value)}
							>
								{__('Preview rules', 'eightshift-forms')}
							</ToggleButton>
						)}
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<ConditionalTagsFormsOptions
							{...props('conditionalTags', attributes, {
								setAttributes,
								conditionalTagsPostId: formsFormPostId,
							})}
						/>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<Container standalone>
							<InputField
								icon={codeVariable}
								label={__('Additional type specifier', 'eightshift-forms')}
								value={formsFormDataTypeSelector}
								onChange={(value) => setAttributes({ [getAttrKey('formsFormDataTypeSelector', attributes, manifest)]: value })}
								monospaceFont
								inline
							/>
						</Container>

						<ContainerGroup
							hidden={formsStyleOptions?.length < 1}
							label={__('Style presets', 'eightshift-forms')}
						>
							{formsStyleOptions.map((option, index) => {
								return (
									<Container
										key={index}
										centered
									>
										<Checkbox
											label={option.label}
											checked={formsStyle.includes(option.value)}
											onChange={(value) => {
												const newValue = value ? [...formsStyle, option.value] : formsStyle.filter((v) => v !== option.value);
												setAttributes({ [getAttrKey('formsStyle', attributes, manifest)]: newValue });
											}}
										/>
									</Container>
								);
							})}
						</ContainerGroup>
					</ContainerPanel>
				</TabPanel>
			</Tabs>
		</>
	);
};
