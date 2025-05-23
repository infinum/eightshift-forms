import React from 'react';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { FileEditor as FileEditorComponent } from '../../../components/file/components/file-editor';

export const FileEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<FileEditorComponent
			{...props('file', attributes, {
				setAttributes,
				clientId,
			})}
		/>
	);
};
