/* global esFormsBlocksLocalization */


import React from 'react';
import { css } from '@emotion/react';
import { useState } from '@wordpress/element';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, TextControl, Button, BaseControl, Modal } from '@wordpress/components';
import {
	CustomSelect,
	IconLabel,
	icons,
	getAttrKey,
	checkAttr,
	getFetchWpApi,
	unescapeHTML,
	IconToggle,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const FormsOptions = ({ attributes, setAttributes, preview }) => {
	const {
		editFormUrl,
		settingsPageUrl
	} = globalManifest;

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
	
	let formsStyleOptions = [];
	let formsUseGeolocation = false;

	// Custom block forms style options.
	if (typeof esFormsBlocksLocalization !== 'undefined' && isArray(esFormsBlocksLocalization?.formsBlockStyleOptions)) {
		formsStyleOptions = esFormsBlocksLocalization.formsBlockStyleOptions;
	}

	// Is geolocation active.
	if (typeof esFormsBlocksLocalization !== 'undefined' && esFormsBlocksLocalization?.useGeolocation) {
		formsUseGeolocation = true;
	}

	const formSelectOptions = getFetchWpApi(
		postType,
		{
			processLabel: ({title: {rendered: renderedTitle}}) => unescapeHTML(renderedTitle)
		}
	);

	const geoLocationOptions = getFetchWpApi(
		'geolocation-countries',
		{
			processLabel: ({label}) => label,
			processId: ({value}) => value,
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
				<div css={css`
					margin-top: 20px;
					padding-top: 20px;
					border-top: 1px solid var(--global-colors-es-ebb);
				`}>
					<div css={css`
						display: flex;
						align-items: center;
						justify-content: flex-end;
					`}>
						<Button
							onClick={removeItem}
							isSecondary
							icon={icons.trash}
							iconSize={12}
							isSmall={true}
							css={css`
								margin-bottom: 10px;
							`}
						>
							{__('Remove', 'eightshift-form')}
						</Button>
					</div>

					<div css={css`
						display: grid;
						grid-gap: 20px;
						grid-template-columns: repeat(2, 1fr);
					`}>
						<CustomSelect
							label={<IconLabel icon={icons.dropdown} label={__('Select form', 'eightshift-forms')} />}
							help={__('Select form from the list that is going to be shown to the user in specific geolocation.', 'eightshift-forms')}
							value={data.formId}
							loadOptions={formSelectOptions}
							onChange={(value) => handleChange(value.toString(), index, 'formId')}
							isClearable={false}
							reFetchOnSearch={true}
							multiple={false}
							simpleValue
						/>

						<CustomSelect
							label={<IconLabel icon={icons.dropdown} label={__('Select geolocation usage', 'eightshift-forms')} />}
							help={__('Select geolocation code where this form will be shown to the users.', 'eightshift-forms')}
							value={data.geoLocation}
							loadOptions={geoLocationOptions}
							onChange={(value) => handleChange(value, index, 'geoLocation')}
							multiple={true}
						/>
					</div>
				</div>
			</>
		);
	};

	const addItem = () => {
		geoRepeater.push({
			formId: '',
			geoLocation: [],
		});

		setGeoRepeater([...geoRepeater]);
	};

	return (
		<PanelBody title={__('Forms', 'eightshift-forms')}>
			<CustomSelect
				label={<IconLabel icon={icons.dropdown} label={__('Select form', 'eightshift-forms')} />}
				help={__('Select form from the list that is going to be shown to the user. Hint: If you can\'t find your form try typing its name.', 'eightshift-forms')}
				value={parseInt(formsFormPostId)}
				loadOptions={formSelectOptions}
				onChange={(value) => setAttributes({[getAttrKey('formsFormPostId', attributes, manifest)]: value.toString()})}
				isClearable={false}
				reFetchOnSearch={true}
				multiple={false}
				simpleValue
			/>

			{formsFormPostId &&
				<>
					<BaseControl>
						<Button
							href={`${editFormUrl}&post=${formsFormPostId}`}
							isSecondary
							icon={'edit'}
						>

							{__('Edit details', 'eightshift-forms')}
						</Button>
						<Button
							href={`${settingsPageUrl}&formId=${formsFormPostId}`}
							isSecondary
							icon={'admin-settings'}
						>
							{__('Edit settings', 'eightshift-forms')}
						</Button>
					</BaseControl>
				</>
			}

			<TextControl
				label={<IconLabel icon={icons.code} label={__('Type selector', 'eightshift-forms')} />}
				help={__('Set additional data type selector for the form.', 'eightshift-forms')}
				value={formsFormDataTypeSelector}
				onChange={(value) => setAttributes({ [getAttrKey('formsFormDataTypeSelector', attributes, manifest)]: value })}
			/>

			{formsStyleOptions &&
				<SelectControl
					label={<IconLabel icon={icons.paletteColor} label={__('Style', 'eightshift-forms')} />}
					help={__('Set what style type is your form.', 'eightshift-forms')}
					value={formsStyle}
					options={formsStyleOptions}
					onChange={(value) => setAttributes({ [getAttrKey('formsStyle', attributes, manifest)]: value })}
				/>
			}

			{formsUseGeolocation &&
				<>
					<hr />

					<CustomSelect
						label={<IconLabel icon={icons.dropdown} label={__('Select geolocation usage', 'eightshift-forms')} />}
						help={__('Select geolocation code where this form will be shown to the users. Hint: If you can\'t find your location is not found try typing its name.', 'eightshift-forms')}
						value={formsFormGeolocation}
						loadOptions={geoLocationOptions}
						onChange={(value) => setAttributes({[getAttrKey('formsFormGeolocation', attributes, manifest)]: value})}
						multiple={true}
					/>

					<Button
						icon={icons.filter}
						onClick={() => setIsModalOpen(true)} 
						isSecondary
						iconSize={24}
					>
						{__('Open GeoLocation Settings', 'eightshift-form')}
					</Button>

					{geoRepeater &&
						<>
							<br />
							<br />
							<IconToggle
								icon={icons.visible}
								label={__('Preview all geolocation forms', 'eightshift-forms')}
								help={__('This toggle is only used to preview all other forms set in the geolocation modal.', 'eightshift-forms')}
								checked={isGeoPreview}
								onChange={() => setIsGeoPreview(!isGeoPreview)}
							/>
						</>
					}

					<br />

					<Button
						icon={icons.visible}
						onClick={() => {
							window.open('/wp-json/eightshift-forms/v1/geolocation-countries');
						}}
						isTertiary
						iconSize={24}
					>
						{__('Preview locations data', 'eightshift-form')}
					</Button>

					{isModalOpen && (
						<Modal
							title={__('GeoLocation Settings', 'eightshift-form')}
							shouldCloseOnClickOutside={false}
							shouldCloseOnEsc={false}
							isDismissible={false}
							onRequestClose={() => {
								setIsModalOpen(false);
							}}
						>
							<p>
								{__('Add new geolocation conditions to this form by adding a new item and selecting what form will be used in what country. If you configure your form geolocation here and don\'t populate the default locations in the sidebar options the default form will be used.', 'eightshift-form')}
							</p>
							<Button
								onClick={addItem}
								icon={icons.add}
								iconSize={24}
								isPrimary
							>
								{__('Add new geo location item', 'eightshift-form')}
							</Button>

							{geoRepeater.map((item, index) => {
								return(
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
								<div css={css`
									display: flex;
									align-items: center;
									justify-content: space-between;
									margin-top: 20px;
									padding-top: 20px;
									border-top: 1px solid var(--global-colors-es-ebb);
								`}
								>
									<Button
										onClick={() => {
											setIsModalOpen(false);
										}}
										icon={icons.errorCircle}
										iconSize={24}
										isSecondary
									>
										{__('Cancel', 'eightshift-form')}
									</Button>
									<Button
										onClick={() => {
											setIsModalOpen(false);
											setAttributes({formsFormGeolocationAlternatives: geoRepeater});
										}}
										icon={icons.check}
										iconSize={24}
										isPrimary
									>
										{__('Save changes', 'eightshift-form')}
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
