import React from 'react';
import { useSelect } from "@wordpress/data";
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const GoodbitsEditor = ({ attributes }) => {
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
						goodbitsServerSideRender: true,
						goodbitsFormPostId: formPostId.toString(),
					}
				}
			/>
		</div>
	);
};
