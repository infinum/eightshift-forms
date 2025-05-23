import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { icons } from '@eightshift/ui-components/icons';
import { InputField, BaseControl, Toggle, ContainerPanel } from '@eightshift/ui-components';

export const FileOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('file');

	const { setAttributes } = attributes;

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
		<ContainerPanel title={__('File', 'eightshift-forms')}>
			<BaseControl
				icon={icons.options}
				label={__('General', 'eightshift-forms')}
			>
				<NameField
					value={fileName}
					attribute={getAttrKey('fileName', attributes, manifest)}
					disabledOptions={fileDisabledOptions}
					setAttributes={setAttributes}
					type='file'
					isChanged={isNameChanged}
					setIsChanged={setIsNameChanged}
				/>
			</BaseControl>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: fileDisabledOptions,
				})}
			/>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: fileDisabledOptions,
				})}
			/>

			<BaseControl
				icon={icons.checks}
				label={__('Validation', 'eightshift-forms')}
			>
				<Toggle
					icon={icons.fieldRequired}
					label={__('Required', 'eightshift-forms')}
					checked={fileIsRequired}
					onChange={(value) => setAttributes({ [getAttrKey('fileIsRequired', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileIsRequired', attributes, manifest), fileDisabledOptions)}
				/>

				<InputField
					icon={icons.fileType}
					label={__('Accepted file types', 'eightshift-forms')}
					value={fileAccept}
					help={__('Separate items with a comma.', 'eightshift-forms')}
					placeholder={__('e.g. .jpg,.png,.pdf', 'eightshift-forms')}
					onChange={(value) => setAttributes({ [getAttrKey('fileAccept', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileAccept', attributes, manifest), fileDisabledOptions)}
				/>

				<BaseControl
					icon={icons.fileSize}
					label={__('File size limits', 'eightshift-forms')}
				>
					<div className='es-fifty-fifty-h'>
						<InputField
							label={__('Min (KB)', 'eightshift-forms')}
							help={__('1MB = 1000 KB', 'eightshift-forms')}
							value={fileMinSize}
							type='number'
							onChange={(value) => setAttributes({ [getAttrKey('fileMinSize', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('fileMinSize', attributes, manifest), fileDisabledOptions)}
							className='es-no-field-spacing'
						/>

						<InputField
							label={__('Max (KB)', 'eightshift-forms')}
							value={fileMaxSize}
							type='number'
							onChange={(value) => setAttributes({ [getAttrKey('fileMaxSize', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('fileMaxSize', attributes, manifest), fileDisabledOptions)}
							className='es-no-field-spacing'
						/>
					</div>
				</BaseControl>
			</BaseControl>

			<BaseControl
				icon={icons.tools}
				label={__('Advanced', 'eightshift-forms')}
			>
				<FieldOptionsVisibility
					{...props('field', attributes, {
						fieldDisabledOptions: fileDisabledOptions,
					})}
				/>

				<Toggle
					icon={icons.files}
					label={__('Allow multi-file upload', 'eightshift-forms')}
					checked={fileIsMultiple}
					onChange={(value) => setAttributes({ [getAttrKey('fileIsMultiple', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileIsMultiple', attributes, manifest), fileDisabledOptions)}
				/>

				<Toggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={fileIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('fileIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileIsDisabled', attributes, manifest), fileDisabledOptions)}
				/>
			</BaseControl>

			<BaseControl
				icon={icons.upload}
				label={__('Custom uploader', 'eightshift-forms')}
				collapsable
			>
				<InputField
					value={fileCustomInfoText}
					icon={icons.infoCircle}
					label={__('Prompt text', 'eightshift-forms')}
					placeholder={__('Drag and drop files here', 'eightshift-forms')}
					onChange={(value) =>
						setAttributes({
							[getAttrKey('fileCustomInfoText', attributes, manifest)]: value,
							[getAttrKey('fileCustomInfoTextUse', attributes, manifest)]: value?.length > 0,
						})
					}
					disabled={
						isOptionDisabled(getAttrKey('fileCustomInfoText', attributes, manifest), fileDisabledOptions) ||
						isOptionDisabled(getAttrKey('fileCustomInfoTextUse', attributes, manifest), fileDisabledOptions)
					}
				/>

				<InputField
					icon={icons.buttonOutline}
					label={__('Upload button text', 'eightshift-forms')}
					value={fileCustomInfoButtonText}
					placeholder={__('Add files', 'eightshift-forms')}
					onChange={(value) => setAttributes({ [getAttrKey('fileCustomInfoButtonText', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileCustomInfoButtonText', attributes, manifest), fileDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</BaseControl>

			<BaseControl
				icon={icons.alignHorizontalVertical}
				label={__('Tracking', 'eightshift-forms')}
				collapsable
			>
				<InputField
					icon={icons.googleTagManager}
					label={__('GTM tracking code', 'eightshift-forms')}
					value={fileTracking}
					onChange={(value) => setAttributes({ [getAttrKey('fileTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('fileTracking', attributes, manifest), fileDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</BaseControl>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: fileDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: fileName,
					conditionalTagsIsHidden: checkAttr('fileFieldHidden', attributes, manifest),
				})}
			/>
		</ContainerPanel>
	);
};
