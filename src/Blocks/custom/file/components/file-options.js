import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { FileOptions as FileOptionsComponent } from '../../../components/file/components/file-options';

export const FileOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('File', 'eightshift-forms')}>
			<FileOptionsComponent
				{...props('file', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</PanelBody>
	);
};
