import React from 'react';
import {
	ServerSideRender,
	checkAttr,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormsEditor = ({ attributes, preview }) => {
	const {
		blockFullName
	} = attributes;

	const {
		isGeoPreview,
	} = preview;

	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);

	return (
		<>
			<ServerSideRender
				block={blockFullName}
				attributes={
					{
						...attributes,
						formsServerSideRender: true,
					}
				}
			/>

			{isGeoPreview &&
				<>
					{formsFormGeolocationAlternatives.map((item, index) => {
						return (
							<ServerSideRender
								key={index}
								block={blockFullName}
								attributes={
									{
										...attributes,
										formsFormPostId: item.formId,
										formsServerSideRender: true,
									}
								}
							/>
						);
					})}
				</>
			}
		</>
	);
};
