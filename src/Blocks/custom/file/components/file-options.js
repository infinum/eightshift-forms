import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { FileOptions as FileOptionsComponent } from '../../../components/file/components/file-options';

export const FileOptions = ({ attributes, setAttributes }) => {
	return (
		<FileOptionsComponent
			{...props('file', attributes, {
				setAttributes,
			})}
		/>
	);
};
