import React, { useMemo } from 'react';
import { useSelect } from "@wordpress/data";
import { getAttrKey, getUnique } from '@eightshift/frontend-libs/scripts';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const CustomDataEditor = ({ attributes, setAttributes, clientId }) => {
	const unique = useMemo(() => getUnique(), []);

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
