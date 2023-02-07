import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody, Button, BaseControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	FancyDivider,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, MissingName } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const FileOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileAccept = checkAttr('fileAccept', attributes, manifest);
	const fileIsMultiple = checkAttr('fileIsMultiple', attributes, manifest);
	const fileIsRequired = checkAttr('fileIsRequired', attributes, manifest);
	const fileTracking = checkAttr('fileTracking', attributes, manifest);
	const fileMinSize = checkAttr('fileMinSize', attributes, manifest);
	const fileMaxSize = checkAttr('fileMaxSize', attributes, manifest);
	const fileCustomInfoText = checkAttr('fileCustomInfoText', attributes, manifest);
	const fileCustomInfoTextUse = checkAttr('fileCustomInfoTextUse', attributes, manifest);
	const fileCustomInfoButtonText = checkAttr('fileCustomInfoButtonText', attributes, manifest);
	const fileDisabledOptions = checkAttr('fileDisabledOptions', attributes, manifest);

	return (
		<>
			<PanelBody title={__('File', 'eightshift-forms')}>
				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: fileDisabledOptions,
					})}
				/>

				<Button
					icon={icons.files}
					isPressed={fileIsMultiple}
					onClick={() => setAttributes({ [getAttrKey('fileIsMultiple', attributes, manifest)]: !fileIsMultiple })}
					disabled={isOptionDisabled(getAttrKey('fileIsMultiple', attributes, manifest), fileDisabledOptions)}
				>
					{__('Allow uploading multiple files', 'eightshift-forms')}
				</Button>

				<FancyDivider label={__('Validation', 'eightshift-forms')} />

				<div className='es-h-spaced es-has-wp-field-b-space'>
					<Button
						icon={icons.fieldRequired}
						isPressed={fileIsRequired}
						onClick={() => setAttributes({ [getAttrKey('fileIsRequired', attributes, manifest)]: !fileIsRequired })}
						disabled={isOptionDisabled(getAttrKey('fileIsRequired', attributes, manifest), fileDisabledOptions)}
					>
						{__('Required', 'eightshift-forms')}
					</Button>
				</div>

				<TextControl
					label={<IconLabel icon={icons.fileType} label={__('Accepted file types', 'eightshift-forms')} />}
					value={fileAccept}
					help={__('Separate items with a comma.', 'eightshift-forms')}
					placeholder='e.g. .jpg,.png,.pdf'
					onChange={(value) => setAttributes({ [getAttrKey('fileAccept', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileAccept', attributes, manifest), fileDisabledOptions)}
				/>

				<BaseControl label={<IconLabel icon={icons.fileSizeMin} label={__('Allowed file size', 'eightshift-forms')} />}>
					<div className='es-fifty-fifty-h'>
						<TextControl
							label={__('Minimum (kB)', 'eightshift-forms')}
							value={fileMinSize}
							type={'number'}
							onChange={(value) => setAttributes({ [getAttrKey('fileMinSize', attributes, manifest)]: value })}
							className='es-no-field-spacing'
							disabled={isOptionDisabled(getAttrKey('fileMinSize', attributes, manifest), fileDisabledOptions)}
						/>

						<TextControl
							label={__('Maximum (kB)', 'eightshift-forms')}
							value={fileMaxSize}
							type={'number'}
							onChange={(value) => setAttributes({ [getAttrKey('fileMaxSize', attributes, manifest)]: value })}
							className='es-no-field-spacing'
							disabled={isOptionDisabled(getAttrKey('fileMaxSize', attributes, manifest), fileDisabledOptions)}
						/>
					</div>
				</BaseControl>

				<FancyDivider label={__('Custom uploader', 'eightshift-forms')} />

				<BaseControl
					className={fileCustomInfoTextUse ? '' : 'es-no-field-spacing'}
					label={
						<div className='es-flex-between'>
							<IconLabel icon={icons.textSize} label={__('Prompt text', 'eightshift-forms')} />

							<Button
								icon={icons.visible}
								isPressed={fileCustomInfoTextUse}
								onClick={() => setAttributes({ [getAttrKey('fileCustomInfoTextUse', attributes, manifest)]: !fileCustomInfoTextUse })}
								disabled={isOptionDisabled(getAttrKey('fileCustomInfoTextUse', attributes, manifest), fileDisabledOptions)}
							/>
						</div>
					}
				>
					{fileCustomInfoTextUse &&
						<TextControl
							value={fileCustomInfoText}
							placeholder={__('Drag and drop files here', 'eightshift-forms')}
							onChange={(value) => setAttributes({ [getAttrKey('fileCustomInfoText', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('fileCustomInfoText', attributes, manifest), fileDisabledOptions)}
						/>
					}
				</BaseControl>

				<TextControl
					label={<IconLabel icon={icons.buttonOutline} label={__('Upload button text', 'eightshift-forms')} />}
					value={fileCustomInfoButtonText}
					placeholder={__('Add files', 'eightshift-forms')}
					onChange={(value) => setAttributes({ [getAttrKey('fileCustomInfoButtonText', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileCustomInfoButtonText', attributes, manifest), fileDisabledOptions)}
				/>

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
					help={__('Should be unique! Used to identify the field within form submission data.', 'eightshift-forms')}
					value={fileName}
					onChange={(value) => setAttributes({ [getAttrKey('fileName', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileName', attributes, manifest), fileDisabledOptions)}
				/>
				<MissingName value={fileName} />

				<FancyDivider label={__('Tracking', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={fileTracking}
					onChange={(value) => setAttributes({ [getAttrKey('fileTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileTracking', attributes, manifest), fileDisabledOptions)}
				/>
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: fileName,
				})}
			/>
		</>
	);
};
