import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { JiraEditor } from './components/jira-editor';
import { JiraOptions } from './components/jira-options';

export const Jira = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={JiraOptions}
			editor={JiraEditor}
		/>
	);
};
