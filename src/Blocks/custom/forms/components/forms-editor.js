import React from 'react';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { AsyncSelect } from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import { __, sprintf } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';
import { FormEditButton, outputFormSelectItemWithIcon } from '../../../components/utils';
import manifest from '../manifest.json';

export const FormsEditor = ({ attributes, setAttributes, preview, formSelectOptions }) => {
	const { isGeoPreview } = preview;

	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);
	const formsFormPostIdRaw = checkAttr('formsFormPostIdRaw', attributes, manifest);
	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);

	if (formsFormPostId?.length < 1) {
		return (
			<Placeholder
				icon={icons.form}
				label={<span>{__('Eightshift Forms', 'eightshift-forms')}</span>}
			>
				<AsyncSelect
					label={<span>{__('To get started, select a form:', 'eightshift-forms')}</span>}
					help={__("If you can't find a form, start typing its name while the dropdown is open.", 'eightshift-forms')}
					value={outputFormSelectItemWithIcon(
						Object.keys(formsFormPostIdRaw).length ? formsFormPostIdRaw : { id: formsFormPostId },
					)}
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
				label={<span>{__('Eightshift Forms', 'eightshift-forms')}</span>}
				isColumnLayout={true}
			>
				{sprintf(
					__('Form "%s" with type "%s" will be displayed here.', 'eightshift-forms'),
					formsFormPostIdRaw?.label,
					formsFormPostIdRaw?.metadata,
				)}
				<br />
				<FormEditButton formId={formsFormPostIdRaw?.id} />
			</Placeholder>

			{isGeoPreview && (
				<>
					<div>{__('Geolocation alternatives', 'eightshift-forms')}</div>
					{formsFormGeolocationAlternatives.map((item, index) => {
						return (
							<Placeholder
								key={index}
								icon={icons.form}
								label={<span>{__('Eightshift Forms', 'eightshift-forms')}</span>}
							>
								{sprintf(
									__('Form "%s" with type "%s" will be displayed here.', 'eightshift-forms'),
									item?.form?.label,
									item?.form?.metadata,
								)}
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
