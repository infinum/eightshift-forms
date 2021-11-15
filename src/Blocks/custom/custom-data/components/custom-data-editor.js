import React, { useMemo, useEffect } from 'react';
import { useSelect } from "@wordpress/data";
import { getAttrKey, getUnique } from '@eightshift/frontend-libs/scripts';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const CustomDataEditor = ({ attributes, setAttributes, }) => {
	const unique = useMemo(() => getUnique(), []);

	const {
		blockClass,
		blockFullName
	} = attributes;

	const formPostId = useSelect((select) => select('core/editor').getCurrentPostId());

	// Populate ID manually and make it generic.
	useEffect(() => {
		setAttributes({ [getAttrKey('customDataId', attributes, manifest)]: unique });
	}, []); // eslint-disable-line

	return (
		<div className={blockClass}>
			<ServerSideRender
				block={blockFullName}
				attributes={
					{
						...attributes,
						customDataServerSideRender: true,
						customDataFormPostId: formPostId.toString(),
					}
				}
			/>
		</div>
	);
}
