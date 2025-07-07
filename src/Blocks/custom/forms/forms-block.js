/* global esFormsLocalization */

import React from 'react';
import { useState } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { FormsEditor } from './components/forms-editor';
import { FormsOptions } from './components/forms-options';
import { fetchFromWpRest } from '@eightshift/frontend-libs-tailwind/scripts';
import { outputFormSelectItemWithIcon } from '../../components/utils';

export const Forms = (props) => {
	const [isGeoPreview, setIsGeoPreview] = useState(false);

	const formSelectOptions = fetchFromWpRest(esFormsLocalization?.postTypes?.forms, {
		noCache: true,
		fields: 'id,title,integration_type',
		processLabel: ({ title: { rendered: label }, integration_type: metadata, id }) => {
			return outputFormSelectItemWithIcon({
				label,
				id,
				metadata,
			})?.label;
		},
		processMetadata: ({ title: { rendered: label }, integration_type: metadata, id }) => ({
			id,
			value: id,
			label,
			metadata,
		}),
	});

	return (
		<>
			<InspectorControls>
				<FormsOptions
					{...props}
					preview={{
						isGeoPreview: isGeoPreview,
						setIsGeoPreview: setIsGeoPreview,
					}}
					formSelectOptions={formSelectOptions}
				/>
			</InspectorControls>
			<FormsEditor
				{...props}
				preview={{
					isGeoPreview: isGeoPreview,
					setIsGeoPreview: setIsGeoPreview,
				}}
				formSelectOptions={formSelectOptions}
			/>
		</>
	);
};
