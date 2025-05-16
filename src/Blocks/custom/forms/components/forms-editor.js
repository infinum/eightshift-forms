import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, props, getAttrKey, STORE_NAME } from '@eightshift/frontend-libs/scripts';
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
				label={<span className='es-font-weight-400'>{__('Eightshift Forms', 'eightshift-forms')}</span>}
				className='es-max-w-80 es-rounded-3! es-mx-auto! es-font-weight-400 es-color-cool-gray-500! es-nested-color-current!'
			>
				<AsyncSelect
					label={<span className='es-mb-0! es-mx-0! es-mt-1! es-text-3.5 es-font-weight-500'>To get started, select a form:</span>}
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
			{isGeoPreview && <div className='es-text-7 es-mb-3 es-text-align-center es-font-weight-700'>{__('Original form', 'eightshift-forms')}</div>}

			<Placeholder
				icon={icons.form}
				label={<span className='es-font-weight-400'>{__('Eightshift Forms', 'eightshift-forms')}</span>}
				className='es-rounded-3! es-mx-auto! es-font-weight-400 es-color-cool-gray-500! es-nested-color-current!'
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
					<div className='es-mt-5 es-text-7 es-text-align-center es-font-weight-700'>{__('Geolocation alternatives', 'eightshift-forms')}</div>
					{formsFormGeolocationAlternatives.map((item, index) => {
						return (
							<Placeholder
								key={index}
								icon={icons.form}
								label={<span className='es-font-weight-400'>{__('Eightshift Forms', 'eightshift-forms')}</span>}
								className='es-rounded-3! es-mt-5! es-mx-auto! es-font-weight-400 es-color-cool-gray-500! es-nested-color-current!'
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
