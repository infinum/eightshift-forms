import React from 'react';
import {
	ServerSideRender,
	checkAttr,
	props,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { ConditionalTagsEditor } from '../../../components/conditional-tags/components/conditional-tags-editor';
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

			<ConditionalTagsEditor
				{...props('conditionalTags', attributes)}
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
