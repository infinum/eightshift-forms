import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { FileEditor as FileEditorComponent } from '../../../components/file/components/file-editor';

export const FileEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<FileEditorComponent
			{...props('file', attributes, {
				setAttributes: setAttributes,
				blockClass,
			})}
		/>
	);
}
