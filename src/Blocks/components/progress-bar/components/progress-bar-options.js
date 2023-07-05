import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { icons, checkAttr, IconToggle, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ProgressBarOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const progressBarUse = checkAttr('progressBarUse', attributes, manifest);
	const progressBarMultiflowUse = checkAttr('progressBarMultiflowUse', attributes, manifest);
	const progressBarMultiflowInitCount = checkAttr('progressBarMultiflowInitCount', attributes, manifest);

	return (
		<>
			<IconToggle
				icon={icons.scrollbarH}
				label={__('Show progress bar', 'eightshift-forms')}
				checked={progressBarUse}
				onChange={(value) => {
					setAttributes({ [getAttrKey('progressBarUse', attributes, manifest)]: value });
				}}
			/>

			{progressBarMultiflowUse &&
				<TextControl
					type={'number'}
					label={__('Progress bar initial steps number', 'eightshift-forms')}
					value={progressBarMultiflowInitCount}
					onChange={(value) => setAttributes({ [getAttrKey('progressBarMultiflowInitCount', attributes, manifest)]: value })}
				/>
			}
		</>
	);
};
