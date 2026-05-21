/* global esFormsLocalization */

import { __, _n, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { isObject, upperFirst } from '@eightshift/ui-components/utilities';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	MultiSelect,
	InputField,
	Toggle,
	Spacer,
	Container,
	ContainerGroup,
	RichLabel,
	OptionSelect,
	Switch,
} from '@eightshift/ui-components';
import {
	a11yWarning,
	fieldAfterText,
	fieldLabel,
	fieldWidth,
	fieldHelp as fieldHelpIcon,
	hide,
	Icon,
	options,
	paletteColor,
	tag,
	arrowsDown,
	help,
} from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../../components/conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const FieldOptionsExternalBlocks = ({ attributes, setAttributes }) => {
	const [isNameChanged, setIsNameChanged] = useState(false);

	return (
		<>
			<Spacer
				border
				icon={options}
				text={__('General', 'eightshift-forms')}
			/>

			<NameField
				value={attributes?.fieldName}
				attribute='fieldName'
				setAttributes={setAttributes}
				type='custom field'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
				isOptional
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes)}
				setAttributes={setAttributes}
				conditionalTagsUse={attributes?.conditionalTagsUse}
				conditionalTagsRules={attributes?.conditionalTagsRules}
				conditionalTagsBlockName={attributes?.fieldName}
				conditionalTagsIsHidden={attributes?.conditionalTagsIsHidden}
			/>
		</>
	);
};

export const FieldOptions = (attributes) => {
	const {
		setAttributes,

		showFieldLabel = true,
		showFieldHideLabel = true,

		additionalControls,
		additionalControlsInner,
	} = attributes;

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);

	return (
		<>
			<ContainerGroup hidden={!showFieldLabel}>
				<Container>
					<InputField
						icon={tag}
						label={__('Field label', 'eightshift-forms')}
						actions={
							<Switch
								arial-label={__('Show field label', 'eightshift-forms')}
								hidden={!showFieldHideLabel}
								checked={!fieldHideLabel}
								onChange={(value) => setAttributes({ [getAttrKey('fieldHideLabel', attributes, manifest)]: !value })}
								size='medium'
							/>
						}
						type='multiline'
						value={fieldHideLabel ? null : fieldLabel}
						onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
						disabled={fieldHideLabel}
						rows={1}
					/>
				</Container>

				<Container
					hidden={!fieldHideLabel && fieldLabel?.length > 0}
					className='es-uic-theme-orange'
					elevated
					centered
					accent
				>
					<RichLabel
						label={
							fieldLabel === ''
								? __('Label should not be empty', 'eightshift-forms')
								: __('Fields should have labels for accessibility', 'eightshift-forms')
						}
						icon={a11yWarning}
					/>
				</Container>

				{typeof additionalControlsInner === 'function'
					? additionalControlsInner(showFieldLabel && !(fieldHideLabel || fieldLabel === ''))
					: additionalControlsInner}
			</ContainerGroup>

			<ContainerGroup hidden={!additionalControls}>
				{typeof additionalControls === 'function'
					? additionalControls(showFieldLabel && !(fieldHideLabel || fieldLabel === ''))
					: additionalControls}
			</ContainerGroup>
		</>
	);
};

export const FieldOptionsLayout = (attributes) => {
	const { blockName, setAttributes } = attributes;

	const fieldStyle = checkAttr('fieldStyle', attributes, manifest);

	let fieldStyleOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isObject(esFormsLocalization?.fieldBlockStyleOptions)) {
		fieldStyleOptions = esFormsLocalization.fieldBlockStyleOptions[blockName];
	}

	const responsiveData = getResponsiveLegacyData(
		manifest.responsiveAttributes.fieldWidth,
		attributes,
		manifest,
		setAttributes,
	);

	return (
		<>
			<ContainerGroup>
				{Object.entries(globalManifest.globalVariables.breakpoints)
					.toReversed()
					.map(([breakpoint], index) => (
						<Container
							key={breakpoint}
							elevated={index === 0}
						>
							<OptionSelect
								icon={index === 0 ? fieldWidth : <Icon icon={`screen${upperFirst(breakpoint)}`} />}
								label={index === 0 ? __('Field width', 'eightshift-forms') : upperFirst(breakpoint)}
								value={attributes[responsiveData.attribute[breakpoint]]}
								options={[
									index > 0 && {
										endIcon: arrowsDown,
										label: __('Inherit', 'eightshift-forms'),
										value: undefined,
										separator: 'below',
									},
									...Array.from(
										{ length: manifest.options.fieldWidth.max - manifest.options.fieldWidth.min + 1 },
										(_, i) => {
											const value = manifest.options.fieldWidth.min + i;

											return { label: sprintf(_n('%d column', '%d columns', value, 'eightshift-forms'), value), value };
										},
									).toReversed(),
								].filter(Boolean)}
								onChange={(value) => responsiveData.onChange(responsiveData.attribute[breakpoint], value)}
								type='menu'
								inline
							/>
						</Container>
					))}
			</ContainerGroup>

			<Container
				hidden={(fieldStyleOptions ?? [])?.length < 1}
				standalone
			>
				<MultiSelect
					icon={paletteColor}
					label={__('Style', 'eightshift-forms')}
					value={fieldStyle}
					options={fieldStyleOptions}
					onChange={(value) => setAttributes({ [getAttrKey('fieldStyle', attributes, manifest)]: value })}
					simpleValue
				/>
			</Container>
		</>
	);
};

export const FieldOptionsMore = (attributes) => {
	const { setAttributes } = attributes;

	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldSuffixContent = checkAttr('fieldSuffixContent', attributes, manifest);

	return (
		<>
			<ContainerGroup label={__('Additional labels', 'eightshift-forms')}>
				<Container>
					<InputField
						icon={fieldLabel}
						label={__('Above field', 'eightshift-forms')}
						subtitle={__('Below field label', 'eightshift-forms')}
						value={fieldBeforeContent}
						onChange={(value) => setAttributes({ [getAttrKey('fieldBeforeContent', attributes, manifest)]: value })}
						inline
					/>
				</Container>

				<Container>
					<InputField
						icon={fieldHelpIcon}
						label={__('Below field', 'eightshift-forms')}
						value={fieldSuffixContent}
						onChange={(value) => setAttributes({ [getAttrKey('fieldSuffixContent', attributes, manifest)]: value })}
						inline
					/>
				</Container>

				<Container>
					<InputField
						icon={fieldAfterText}
						label={__('Above help text', 'eightshift-forms')}
						value={fieldAfterContent}
						onChange={(value) => setAttributes({ [getAttrKey('fieldAfterContent', attributes, manifest)]: value })}
						inline
					/>
				</Container>

				<Container>
					<InputField
						icon={help}
						label={__('Help text', 'eightshift-forms')}
						value={fieldHelp}
						onChange={(value) => setAttributes({ [getAttrKey('fieldHelp', attributes, manifest)]: value })}
						inline
					/>
				</Container>
			</ContainerGroup>
		</>
	);
};

export const FieldOptionsVisibility = (attributes) => {
	const { setAttributes } = attributes;

	const fieldHidden = checkAttr('fieldHidden', attributes, manifest);
	const fieldDisabledOptions = checkAttr('fieldDisabledOptions', attributes, manifest);

	return (
		<Container>
			<Toggle
				icon={hide}
				label={__('Hidden', 'eightshift-forms')}
				checked={fieldHidden}
				onChange={(value) => setAttributes({ [getAttrKey('fieldHidden', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('fieldHidden', attributes, manifest), fieldDisabledOptions)}
			/>
		</Container>
	);
};

/**
 * Get the data for `ResponsiveLegacy` from Eightshift UI components.
 *
 * @param {Object} responsiveAttr - Responsive attribute data, usually from `manifest.responsiveAttributes`.
 * @param {Object} attributes - Component/block attributes.
 * @param {Object} manifest - Component/block manifest.
 * @param {function} setAttributes - The `setAttributes` function.
 *
 * @access public
 * @since 13.0.0
 *
 * @returns Object
 */
export const getResponsiveLegacyData = (responsiveAttr, attributes, manifest, setAttributes) => ({
	attribute: Object.fromEntries(
		Object.entries(responsiveAttr).map(([breakpoint, attrName]) => [
			breakpoint,
			getAttrKey(attrName, attributes, manifest),
		]),
	),
	value: attributes,
	onChange: (attributeName, value) => setAttributes({ [attributeName]: value }),
});
