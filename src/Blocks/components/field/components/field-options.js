import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { TextControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	Section,
	AnimatedContentVisibility,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled } from './../../utils';

export const FieldOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

	const {
		setAttributes,

		showFieldLabel = true,

		additionalControls,
	} = attributes;

	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHidden = checkAttr('fieldHidden', attributes, manifest);
	const fieldDisabledOptions = checkAttr('fieldDisabledOptions', attributes, manifest);
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);

	return (
		<Section icon={icons.buttonOutline} label={__('Field', 'eightshift-forms')}>
			{showFieldLabel &&
				<>
					<IconToggle
						icon={icons.tag}
						label={__('Label', 'eightshift-forms')}
						checked={!fieldHideLabel}
						onChange={(value) => setAttributes({ [getAttrKey('fieldHideLabel', attributes, manifest)]: !value })}
						reducedBottomSpacing
					/>

					{!fieldHideLabel &&
						<TextControl
							value={fieldLabel}
							onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
							disabled={fieldHideLabel}
						/>
					}

					<AnimatedContentVisibility showIf={fieldHideLabel}>
						<IconLabel label={__('Might impact accessibility', 'eightshift-forms')} icon={icons.a11yWarning} additionalClasses='es-nested-color-yellow-500! es-line-h-1 es-color-cool-gray-500 es-mb-5' standalone />
					</AnimatedContentVisibility>
				</>
			}

			<TextControl
				label={<IconLabel icon={icons.help} label={__('Help text', 'eightshift-forms')} />}
				value={fieldHelp}
				onChange={(value) => setAttributes({ [getAttrKey('fieldHelp', attributes, manifest)]: value })}
			/>

			<IconToggle
				icon={icons.hide}
				label={__('Hide', 'eightshift-forms')}
				checked={fieldHidden}
				onChange={(value) => setAttributes({ [getAttrKey('fieldHidden', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('fieldHidden', attributes, manifest), fieldDisabledOptions)}
				noBottomSpacing={!additionalControls}
			/>

			{additionalControls}
		</Section>
	);
};
