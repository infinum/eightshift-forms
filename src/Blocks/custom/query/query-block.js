import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { QueryEditor } from './components/query-editor';
import { QueryOptions } from './components/query-options';

export const Query = (props) => {
	return (
		<>
			<InspectorControls>
				<QueryOptions {...props} />
			</InspectorControls>
			<QueryEditor {...props} />
		</>
	);
};
