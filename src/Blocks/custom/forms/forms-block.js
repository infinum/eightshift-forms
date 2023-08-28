import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';
import { FormsEditor } from './components/forms-editor';
import { FormsOptions } from './components/forms-options';
import {
	getFetchWpApi,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import {
	outputFormSelectItemWithIcon,
} from '../../components/utils';

export const Forms = (props) => {
	const manifest = select(STORE_NAME).getBlock('forms');
	const {
		postType,
	} = manifest;

	const [isGeoPreview, setIsGeoPreview] = useState(false);

	const formSelectOptions = getFetchWpApi(
		postType,
		{
			noCache: true,
			processLabel: ({ title: { rendered: label }, integration_type: metadata, id }) => {
				return outputFormSelectItemWithIcon({
					label,
					id,
					metadata,
				})?.label;
			},
			fields: 'id,title,integration_type',
			processMetadata: ({ title: { rendered: label }, integration_type: metadata, id }) => ({
				id,
				value: id,
				label,
				metadata,
			}),
		}
	);

	return (
		<>
			<InspectorControls>
				<FormsOptions
					{...props}
					preview={{
						isGeoPreview: isGeoPreview,
						setIsGeoPreview: setIsGeoPreview
					}}
					formSelectOptions={formSelectOptions}
				/>
			</InspectorControls>
			<FormsEditor
				{...props}
				preview={{
					isGeoPreview: isGeoPreview,
					setIsGeoPreview: setIsGeoPreview
				}}
				formSelectOptions={formSelectOptions}
			/>
		</>
	);
};
