import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const SubmitEditor = (attributes) => {
	const {
		blockFullName,
	} = attributes;

	delete attributes.prefix;
	delete attributes.setAttributes;

	return (
		<ServerSideRender
			block={blockFullName}
			attributes={attributes}
		/>
	);
};
