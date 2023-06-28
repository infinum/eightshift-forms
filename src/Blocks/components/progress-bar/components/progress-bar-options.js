import React from 'react';
import { __ } from '@wordpress/i18n';
import { icons, checkAttr, IconToggle, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ProgressBarOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const progressBarUse = checkAttr('progressBarUse', attributes, manifest);

	return (
		<IconToggle
			icon={icons.scrollbarH}
			label={__('Show progress bar', 'eightshift-forms')}
			checked={progressBarUse}
			onChange={(value) => {
				setAttributes({ [getAttrKey('progressBarUse', attributes, manifest)]: value });
			}}
		/>
	);
};
