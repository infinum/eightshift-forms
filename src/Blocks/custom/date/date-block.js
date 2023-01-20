import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { DateEditor } from './components/date-editor';
import { DateOptions } from './components/date-options';

export const Date = (props) => {
	return (
		<>
			<InspectorControls>
				<DateOptions {...props} />
			</InspectorControls>
			<DateEditor {...props} />
		</>
	);
};
