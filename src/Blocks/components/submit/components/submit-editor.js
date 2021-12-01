import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const SubmitEditor = (attributes) => {
	const {
		blockFullName,
		clientId,
	} = attributes;

	delete attributes.prefix;
	delete attributes.setAttributes;
	delete attributes.clientId;

	return (
		<ServerSideRender
			block={blockFullName}
			attributes={{
				...attributes,
				submitUniqueId: clientId,
			}}
		/>
	);
};
