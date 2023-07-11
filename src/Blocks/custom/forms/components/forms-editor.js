import React from 'react';
import { select } from '@wordpress/data';
import { ServerSideRender, checkAttr, props, icons, AsyncSelect, getFetchWpApi, unescapeHTML, getAttrKey, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';
import { ConditionalTagsEditor } from '../../../components/conditional-tags/components/conditional-tags-editor';
import { getFilteredAttributes } from '../../../components/utils';

export const FormsEditor = ({ attributes, setAttributes, preview }) => {
	const manifest = select(STORE_NAME).getBlock('forms');

	const {
		blockFullName
	} = attributes;

	const {
		attributesSsr,
		postType,
	} = manifest;

	const {
		isGeoPreview,
	} = preview;


	const formSelectOptions = getFetchWpApi(
		postType,
		{
			processLabel: ({ title: { rendered: renderedTitle } }) => unescapeHTML(renderedTitle)
		}
	);

	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);
	const formsFormPostIdRaw = checkAttr('formsFormPostIdRaw', attributes, manifest);
	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);

	if (formsFormPostId?.length < 1) {
		return (
			<Placeholder
				icon={icons.form}
				label={<span className='es-font-weight-400'>{__('Eightshift Forms', 'productive')}</span>}
				className='es-max-w-80 es-rounded-3! es-mx-auto! es-font-weight-400 es-color-cool-gray-500! es-nested-color-current!'
			>
				<AsyncSelect
					label={<span className='es-mb-0! es-mx-0! es-mt-1! es-text-3.5 es-font-weight-500'>To get started, select a form:</span>}
					help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
					value={formsFormPostIdRaw ?? (formsFormPostId ? { label: 'Selected item', id: parseInt(formsFormPostId ?? -1) } : null)}
					loadOptions={formSelectOptions}
					onChange={(value) => setAttributes({
						[getAttrKey('formsFormPostIdRaw', attributes, manifest)]: value,
						[getAttrKey('formsFormPostId', attributes, manifest)]: `${value?.value}`,
					})}
					noBottomSpacing
				/>
			</Placeholder>
		);
	}

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
				isFormPicker
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
