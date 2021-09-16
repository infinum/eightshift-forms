import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const FormsEditor = ({ attributes }) => {

	const {
		blockClass,
		blockFullName
	} = attributes;

	return (
		<div className={blockClass}>
			<ServerSideRender
				block={blockFullName}
				attributes={attributes}
			/>
		</div>
	);
}
