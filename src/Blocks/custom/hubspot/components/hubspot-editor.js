import React from 'react';
import { useSelect } from "@wordpress/data";
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const HubspotEditor = ({ attributes }) => {
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
						hubspotServerSideRender: true,
						hubspotFormPostId: formPostId.toString(),
					}
				}
			/>
		</div>
	);
}
