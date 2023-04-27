import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { JiraEditor } from './components/jira-editor';
import { JiraOptions } from './components/jira-options';

export const Jira = (props) => {
	return (
		<>
			<InspectorControls>
				<JiraOptions {...props} />
			</InspectorControls>
			<JiraEditor {...props} />
		</>
	);
};
