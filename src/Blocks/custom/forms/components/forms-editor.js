import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, props, getAttrKey, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { __, sprintf } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';
import { ConditionalTagsEditor } from '../../../components/conditional-tags/components/conditional-tags-editor';
import { FormEditButton, outputFormSelectItemWithIcon } from '../../../components/utils';
import { icons } from '@eightshift/ui-components/icons';
import { AsyncSelect } from '@eightshift/ui-components';

export const FormsEditor = ({ attributes, setAttributes, preview, formSelectOptions }) => {
	const manifest = select(STORE_NAME).getBlock('forms');

	const { isGeoPreview } = preview;

	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);
	const formsFormPostIdRaw = checkAttr('formsFormPostIdRaw', attributes, manifest);
	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);

	if (formsFormPostId?.length < 1) {
		return (
			<Placeholder
				icon={icons.form}
				label={__('Eightshift Forms', 'eightshift-forms')}
			>
				<AsyncSelect
					label={__('To get started, select a form:', 'eightshift-forms')}
					help={__("If you can't find a form, start typing its name while the dropdown is open.", 'eightshift-forms')}
					value={outputFormSelectItemWithIcon(Object.keys(formsFormPostIdRaw).length ? formsFormPostIdRaw : { id: formsFormPostId })}
					loadOptions={formSelectOptions}
					onChange={(value) => {
						setAttributes({
							[getAttrKey('formsFormPostIdRaw', attributes, manifest)]: {
								id: value?.id,
								label: value?.metadata?.label,
								value: value?.metadata?.value,
								metadata: value?.metadata?.metadata,
							},
							[getAttrKey('formsFormPostId', attributes, manifest)]: `${value?.value}`,
						});
					}}
				/>
			</Placeholder>
		);
	}

	return (
		<>
			{isGeoPreview && <div>{__('Original form', 'eightshift-forms')}</div>}

			<Placeholder
				icon={icons.form}
				label={__('Eightshift Forms', 'eightshift-forms')}
				isColumnLayout={true}
			>
				{sprintf(__('Form "%s" with type "%s" will be displayed here.', 'eightshift-forms'), formsFormPostIdRaw?.label, formsFormPostIdRaw?.metadata)}
				<br />
				<FormEditButton formId={formsFormPostIdRaw?.id} />
			</Placeholder>

			<ConditionalTagsEditor
				{...props('conditionalTags', attributes)}
				isFormPicker
			/>

			{isGeoPreview && (
				<>
					<div>{__('Geolocation alternatives', 'eightshift-forms')}</div>
					{formsFormGeolocationAlternatives.map((item, index) => {
						return (
							<Placeholder
								key={index}
								icon={icons.form}
								label={__('Eightshift Forms', 'eightshift-forms')}
							>
								{sprintf(__('Form "%s" with type "%s" will be displayed here.', 'eightshift-forms'), item?.form?.label, item?.form?.metadata)}
								<br />
								{sprintf(__('Geolocation used: "%s"', 'eightshift-forms'), item.geoLocation.join(', '))}
								<br />
								<FormEditButton formId={item?.form?.id} />
							</Placeholder>
						);
					})}
				</>
			)}
		</>
	);
};
