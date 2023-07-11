import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { icons, checkAttr, getAttrKey, IconLabel, props, Section, IconToggle, Control, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const FileOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('file');

	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileAccept = checkAttr('fileAccept', attributes, manifest);
	const fileIsMultiple = checkAttr('fileIsMultiple', attributes, manifest);
	const fileIsRequired = checkAttr('fileIsRequired', attributes, manifest);
	const fileTracking = checkAttr('fileTracking', attributes, manifest);
	const fileMinSize = checkAttr('fileMinSize', attributes, manifest);
	const fileMaxSize = checkAttr('fileMaxSize', attributes, manifest);
	const fileCustomInfoText = checkAttr('fileCustomInfoText', attributes, manifest);
	const fileCustomInfoButtonText = checkAttr('fileCustomInfoButtonText', attributes, manifest);
	const fileDisabledOptions = checkAttr('fileDisabledOptions', attributes, manifest);
	const fileIsDisabled = checkAttr('fileIsDisabled', attributes, manifest);

	return (
		<>
			<PanelBody title={__('File', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={fileName} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={fileName}
						onChange={(value) => {
							setIsNameChanged(true);
							setAttributes({ [getAttrKey('fileName', attributes, manifest)]: value });
						}}
						disabled={isOptionDisabled(getAttrKey('fileName', attributes, manifest), fileDisabledOptions)}
						className='es-no-field-spacing'
					/>
					<NameChangeWarning isChanged={isNameChanged} />

					<IconToggle
						icon={icons.files}
						label={__('Allow multi-file upload', 'eightshift-forms')}
						checked={fileIsMultiple}
						onChange={(value) => setAttributes({ [getAttrKey('fileIsMultiple', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('fileIsMultiple', attributes, manifest), fileDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: fileDisabledOptions,
					})}
					additionalControls={<FieldOptionsAdvanced {...props('field', attributes)} />}
				/>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
					<IconToggle
						icon={icons.fieldRequired}
						label={__('Required', 'eightshift-forms')}
						checked={fileIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('fileIsRequired', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('fileIsRequired', attributes, manifest), fileDisabledOptions)}
					/>

					<TextControl
						label={<IconLabel icon={icons.fileType} label={__('Accepted file types', 'eightshift-forms')} />}
						value={fileAccept}
						help={__('Separate items with a comma.', 'eightshift-forms')}
						placeholder='e.g. .jpg,.png,.pdf'
						onChange={(value) => setAttributes({ [getAttrKey('fileAccept', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('fileAccept', attributes, manifest), fileDisabledOptions)}
					/>

					<Control icon={icons.fileSize} label={__('File size limits', 'eightshift-forms')} additionalLabelClasses='es-mb-0!' noBottomSpacing>
						<div className='es-fifty-fifty-h'>
							<TextControl
								label={__('Min (kB)', 'eightshift-forms')}
								value={fileMinSize}
								type='number'
								onChange={(value) => setAttributes({ [getAttrKey('fileMinSize', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileMinSize', attributes, manifest), fileDisabledOptions)}
								className='es-no-field-spacing'
							/>

							<TextControl
								label={__('Max (kB)', 'eightshift-forms')}
								value={fileMaxSize}
								type='number'
								onChange={(value) => setAttributes({ [getAttrKey('fileMaxSize', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileMaxSize', attributes, manifest), fileDisabledOptions)}
								className='es-no-field-spacing'
							/>
						</div>
					</Control>
				</Section>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={fileIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('fileIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('fileIsDisabled', attributes, manifest), fileDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.upload} label={__('Custom uploader', 'eightshift-forms')}>
					<TextControl
						value={fileCustomInfoText}
						label={<IconLabel icon={icons.infoCircle} label={__('Prompt text', 'eightshift-forms')} />}
						placeholder={__('Drag and drop files here', 'eightshift-forms')}
						onChange={(value) => setAttributes({
							[getAttrKey('fileCustomInfoText', attributes, manifest)]: value,
							[getAttrKey('fileCustomInfoTextUse', attributes, manifest)]: value?.length > 0,
						})}
						disabled={
							isOptionDisabled(getAttrKey('fileCustomInfoText', attributes, manifest), fileDisabledOptions)
							|| isOptionDisabled(getAttrKey('fileCustomInfoTextUse', attributes, manifest), fileDisabledOptions)
						}
					/>

					<TextControl
						label={<IconLabel icon={icons.buttonOutline} label={__('Upload button text', 'eightshift-forms')} />}
						value={fileCustomInfoButtonText}
						placeholder={__('Add files', 'eightshift-forms')}
						onChange={(value) => setAttributes({ [getAttrKey('fileCustomInfoButtonText', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('fileCustomInfoButtonText', attributes, manifest), fileDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={fileTracking}
						onChange={(value) => setAttributes({ [getAttrKey('fileTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('fileTracking', attributes, manifest), fileDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: fileName,
				})}
			/>
		</>
	);
};
