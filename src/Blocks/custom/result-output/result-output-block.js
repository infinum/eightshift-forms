/* global esFormsLocalization */

import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ResultOutputEditor } from './components/result-output-editor';
import { ResultOutputOptions } from './components/result-output-options';
import { fetchFromWpRest } from '@eightshift/frontend-libs-tailwind/scripts';
import { outputFormSelectItemWithIcon } from '../../components/utils';

const dynamicItemSelectOptions = function (postType) {
	return fetchFromWpRest(postType, {
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
	});
};

export const ResultOutput = (props) => {
	const formSelectOptions = dynamicItemSelectOptions(esFormsLocalization?.postTypes?.forms);
	const resultSelectOptions = dynamicItemSelectOptions(esFormsLocalization?.postTypes?.results);

	return (
		<>
			<InspectorControls>
				<ResultOutputOptions
					{...props}
					formSelectOptions={formSelectOptions}
					resultSelectOptions={resultSelectOptions}
				/>
			</InspectorControls>
			<ResultOutputEditor
				{...props}
				formSelectOptions={formSelectOptions}
				resultSelectOptions={resultSelectOptions}
			/>
		</>
	);
};
