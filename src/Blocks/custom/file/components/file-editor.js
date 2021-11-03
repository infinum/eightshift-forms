import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { FileEditor as FileEditorComponent } from '../../../components/file/components/file-editor';

export const FileEditor = ({ attributes, setAttributes, clientId }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<FileEditorComponent
			{...props('file', attributes, {
				setAttributes,
				blockClass,
				clientId,
			})}
		/>
	);
}
