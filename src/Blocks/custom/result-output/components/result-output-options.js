/* global esFormsLocalization */

import { __ } from '@wordpress/i18n';
import { checkAttr, fetchFromWpRest, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { AsyncSelect, ContainerPanel, Toggle } from '@eightshift/ui-components';
import { visibilityAlt } from '@eightshift/ui-components/icons';
import { getUtilsIcons } from '../../../components/form/assets/state-init';
import manifest from '../manifest.json';

export const ResultOutputOptions = ({ attributes, setAttributes }) => {
	const resultOutputFormPostId = checkAttr('resultOutputFormPostId', attributes, manifest);
	const resultOutputFormPostIdRaw = checkAttr('resultOutputFormPostIdRaw', attributes, manifest);
	const resultOutputPostId = checkAttr('resultOutputPostId', attributes, manifest);
	const resultOutputPostIdRaw = checkAttr('resultOutputPostIdRaw', attributes, manifest);
	const resultOutputHide = checkAttr('resultOutputHide', attributes, manifest);

	return (
		<ContainerPanel>
			<AsyncSelect
				label={__('Result Output', 'eightshift-forms')}
				value={Object.keys(resultOutputPostIdRaw ?? {}).length ? resultOutputPostIdRaw : { id: resultOutputPostId }}
				fetchFunction={fetchFromWpRest(esFormsLocalization?.postTypes?.results, {
					noCache: true,
					processLabel: ({ title: { rendered: label } }) => label,
					fields: 'id,title,integration_type',
					processMetadata: ({ title: { rendered: label }, integration_type: metadata, id }) => ({
						id,
						value: id,
						label,
						metadata,
					}),
				})}
				onChange={(value) => {
					setAttributes({
						[getAttrKey('resultOutputPostIdRaw', attributes, manifest)]: {
							id: value?.id,
							label: value?.metadata?.label,
							value: value?.metadata?.value,
							metadata: value?.metadata?.metadata,
						},
						[getAttrKey('resultOutputPostId', attributes, manifest)]: `${value?.value.toString()}`,
					});
				}}
			/>

			<AsyncSelect
				label={__('Connected Form', 'eightshift-forms')}
				value={Object.keys(resultOutputFormPostIdRaw ?? {}).length ? resultOutputFormPostIdRaw : { id: resultOutputFormPostId }}
				fetchFunction={fetchFromWpRest(esFormsLocalization?.postTypes?.forms, {
					noCache: true,
					processLabel: ({ title: { rendered: label } }) => label,
					fields: 'id,title,integration_type',
					processMetadata: ({ title: { rendered: label }, integration_type: metadata, id }) => ({
						id,
						value: id,
						label,
						metadata,
					}),
				})}
				customValueDisplay={(item) => (
					<span className='esf:flex esf:items-center esf:gap-10'>
						<span
							dangerouslySetInnerHTML={{
								__html: getUtilsIcons(item?.metadata?.metadata || 'post'),
							}}
						/>
						{item?.label}
					</span>
				)}
				customMenuOption={(item) => (
					<span className='esf:flex esf:items-center esf:gap-10'>
						<span
							dangerouslySetInnerHTML={{
								__html: getUtilsIcons(item?.metadata?.metadata || 'post'),
							}}
						/>
						{item?.label}
					</span>
				)}
				onChange={(value) => {
					setAttributes({
						[getAttrKey('resultOutputFormPostIdRaw', attributes, manifest)]: {
							id: value?.id,
							label: value?.metadata?.label,
							value: value?.metadata?.value,
							metadata: value?.metadata?.metadata,
						},
						[getAttrKey('resultOutputFormPostId', attributes, manifest)]: `${value?.value.toString()}`,
					});
				}}
			/>

			<Toggle
				icon={visibilityAlt}
				label={__('Hide by default', 'eightshift-forms')}
				help={__('Hide result output block by default. It will be shown only on form success.', 'eightshift-forms')}
				checked={resultOutputHide}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputHide', attributes, manifest)]: value })}
			/>
		</ContainerPanel>
	);
};
