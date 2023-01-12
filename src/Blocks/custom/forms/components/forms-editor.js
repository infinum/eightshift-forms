import React from 'react';
import {
	ServerSideRender,
	checkAttr,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { getFilteredAttributes } from '../../../components/utils';

export const FormsEditor = ({ attributes, preview }) => {
	const {
		blockFullName
	} = attributes;

	const {
		attributesSsr,
	} = manifest;

	const {
		isGeoPreview,
	} = preview;

	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);

	return (
		<>
			<ServerSideRender
				block={blockFullName}
				attributes={
					getFilteredAttributes(
						attributes,
						attributesSsr,
						{
							formsServerSideRender: true
						}
					)
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
									getFilteredAttributes(
										attributes,
										[
											...attributesSsr,
											'formsFormGeolocation',
											'formsFormGeolocationAlternatives',
										],
										{
											formsFormPostId: item.formId,
											formsServerSideRender: true
										}
									)
								}
							/>
						);
					})}
				</>
			}
		</>
	);
};
