import React from 'react';
import { useSelect } from "@wordpress/data";
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const CustomDataEditor = ({ attributes, clientId }) => {
	const {
		blockClass,
		blockFullName
	} = attributes;

	const formPostId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<div className={blockClass}>
			<ServerSideRender
				block={blockFullName}
				attributes={
					{
						...attributes,
						customDataUniqueId: clientId,
						customDataServerSideRender: true,
						customDataFormPostId: formPostId.toString(),
					}
				}
			/>
		</div>
	);
};
