import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { icons } from '@eightshift/ui-components/icons';
import { InputField, Toggle, BaseControl, ContainerPanel } from '@eightshift/ui-components';

export const DynamicOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('dynamic');

	const { setAttributes, title = __('Dynamic', 'eightshift-forms') } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const dynamicName = checkAttr('dynamicName', attributes, manifest);
	const dynamicType = checkAttr('dynamicType', attributes, manifest);
	const dynamicIsDeactivated = checkAttr('dynamicIsDeactivated', attributes, manifest);
	const dynamicIsRequired = checkAttr('dynamicIsRequired', attributes, manifest);
	const dynamicTracking = checkAttr('dynamicTracking', attributes, manifest);
	const dynamicDisabledOptions = checkAttr('dynamicDisabledOptions', attributes, manifest);
	const dynamicIsMultiple = checkAttr('dynamicIsMultiple', attributes, manifest);

	return (
		<ContainerPanel title={title}>
			<Toggle
				icon={icons.cursorDisabled}
				label={__('Deactivated', 'eightshift-forms')}
				help={__('All dynamic fields are deactivated by default.', 'eightshift-forms')}
				checked={dynamicIsDeactivated}
				onChange={(value) => setAttributes({ [getAttrKey('dynamicIsDeactivated', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('dynamicIsDeactivated', attributes, manifest), dynamicDisabledOptions)}
			/>

			{!dynamicIsDeactivated && (
				<>
					<BaseControl
						icon={icons.options}
						label={__('General', 'eightshift-forms')}
					>
						<NameField
							value={dynamicName}
							attribute={getAttrKey('dynamicName', attributes, manifest)}
							disabledOptions={dynamicDisabledOptions}
							setAttributes={setAttributes}
							type='dynamic'
							isChanged={isNameChanged}
							setIsChanged={setIsNameChanged}
						/>
					</BaseControl>

					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: dynamicDisabledOptions,
						})}
					/>

					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: dynamicDisabledOptions,
						})}
					/>

					<BaseControl
						icon={icons.tools}
						label={__('Advanced', 'eightshift-forms')}
					>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: dynamicDisabledOptions,
							})}
						/>

						{dynamicType === 'select' && (
							<Toggle
								icon={icons.files}
								label={__('Allow multi selection', 'eightshift-forms')}
								checked={dynamicIsMultiple}
								onChange={(value) => {
									setAttributes({ [getAttrKey('dynamicIsMultiple', attributes, manifest)]: value });
								}}
								disabled={isOptionDisabled(getAttrKey('dynamicIsMultiple', attributes, manifest), dynamicDisabledOptions)}
							/>
						)}
					</BaseControl>

					<BaseControl
						icon={icons.checks}
						label={__('Validation', 'eightshift-forms')}
					>
						<Toggle
							icon={icons.required}
							label={__('Required', 'eightshift-forms')}
							checked={dynamicIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('dynamicIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('dynamicIsRequired', attributes, manifest), dynamicDisabledOptions)}
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
							value={dynamicTracking}
							onChange={(value) => setAttributes({ [getAttrKey('dynamicTracking', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('dynamicTracking', attributes, manifest), dynamicDisabledOptions)}
							className='es-no-field-spacing'
						/>
					</BaseControl>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: dynamicDisabledOptions,
						})}
					/>

					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: dynamicName,
							conditionalTagsIsHidden: checkAttr('dynamicFieldHidden', attributes, manifest),
						})}
					/>
				</>
			)}
		</ContainerPanel>
	);
};
