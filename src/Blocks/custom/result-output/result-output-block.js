/* global esFormsLocalization */

import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ResultOutputEditor } from './components/result-output-editor';
import { ResultOutputOptions } from './components/result-output-options';
import { getFetchWpApi } from '@eightshift/frontend-libs/scripts';
import { outputFormSelectItemWithIcon } from '../../components/utils';

const dynamicItemSelectOptions = function(postType) {
	return getFetchWpApi(
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
};

export const ResultOutput = (props) => {
	return (
		<>
			<InspectorControls>
				<ResultOutputOptions
					{...props}
					formSelectOptions={dynamicItemSelectOptions(esFormsLocalization?.postTypes?.forms)}
					resultSelectOptions={dynamicItemSelectOptions(esFormsLocalization?.postTypes?.results)}
				/>
			</InspectorControls>
			<ResultOutputEditor
				{...props}
				formSelectOptions={dynamicItemSelectOptions(esFormsLocalization?.postTypes?.forms)}
				resultSelectOptions={dynamicItemSelectOptions(esFormsLocalization?.postTypes?.results)}
			/>
		</>
	);
};
