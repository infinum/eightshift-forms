import React from 'react';
import { useState } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { FormsEditor } from './components/forms-editor';
import { FormsOptions } from './components/forms-options';

export const Forms = (props) => {
	const [isGeoPreview, setIsGeoPreview] = useState(false);

	return (
		<>
			<InspectorControls>
				<FormsOptions
					{...props}
					preview={{
						isGeoPreview: isGeoPreview,
						setIsGeoPreview: setIsGeoPreview
					}}
				/>
			</InspectorControls>
			<FormsEditor
				{...props}
				preview={{
					isGeoPreview: isGeoPreview,
					setIsGeoPreview: setIsGeoPreview
				}}
			/>
		</>
	);
};
