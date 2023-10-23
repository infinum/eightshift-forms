import React from 'react';
import manifest from '../manifest.json';
import readme from './readme.mdx';

export default {
	title: `Components/${manifest.title}`,
	parameters: {
		docs: { 
			page: readme
		}
	},
};

export const block = () => (
	<div />
);
