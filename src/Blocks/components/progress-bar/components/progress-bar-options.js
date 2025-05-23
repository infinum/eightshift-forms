import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { checkAttr, Toggle, getAttrKey, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { icons } from '@eightshift/ui-components/icons';
import { InputField } from '@eightshift/ui-components';

export const ProgressBarOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('progress-bar');

	const { setAttributes } = attributes;

	const progressBarUse = checkAttr('progressBarUse', attributes, manifest);
	const progressBarHideLabels = checkAttr('progressBarHideLabels', attributes, manifest);
	const progressBarMultiflowUse = checkAttr('progressBarMultiflowUse', attributes, manifest);
	const progressBarMultiflowInitCount = checkAttr('progressBarMultiflowInitCount', attributes, manifest);

	return (
		<>
			<Toggle
				icon={icons.scrollbarH}
				label={__('Show progress bar', 'eightshift-forms')}
				checked={progressBarUse}
				onChange={(value) => {
					setAttributes({ [getAttrKey('progressBarUse', attributes, manifest)]: value });
				}}
			/>

			<Toggle
				icon={icons.tag}
				label={__('Hide progress bar labels', 'eightshift-forms')}
				help={__('This will hide the labels on the progress bar.', 'eightshift-forms')}
				checked={progressBarHideLabels}
				onChange={(value) => {
					setAttributes({ [getAttrKey('progressBarHideLabels', attributes, manifest)]: value });
				}}
			/>

			{progressBarMultiflowUse && (
				<InputField
					type={'number'}
					label={__('Progress bar initial steps number', 'eightshift-forms')}
					value={progressBarMultiflowInitCount}
					onChange={(value) => setAttributes({ [getAttrKey('progressBarMultiflowInitCount', attributes, manifest)]: value })}
				/>
			)}
		</>
	);
};
