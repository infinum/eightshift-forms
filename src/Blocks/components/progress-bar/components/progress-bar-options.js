import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { TextControl } from '@wordpress/components';
import { icons, checkAttr, IconToggle, getAttrKey, STORE_NAME } from '@eightshift/frontend-libs/scripts';

export const ProgressBarOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('progress-bar');

	const {
		setAttributes,
	} = attributes;

	const progressBarUse = checkAttr('progressBarUse', attributes, manifest);
	const progressBarHideLabels = checkAttr('progressBarHideLabels', attributes, manifest);
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

			<IconToggle
				icon={icons.tag}
				label={__('Hide progress bar labels', 'eightshift-forms')}
				checked={progressBarHideLabels}
				onChange={(value) => {
					setAttributes({ [getAttrKey('progressBarHideLabels', attributes, manifest)]: value });
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
