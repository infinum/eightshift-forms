import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from "@wordpress/data";
import { PanelBody, Button } from '@wordpress/components';
import { icons, props } from '@eightshift/frontend-libs/scripts';
import { FormOptions as FormOptionsComponent } from '../../../components/form/components/form-options';
import globalManifest from '../../../manifest.json';

export const FormOptions = ({ attributes, setAttributes }) => {
	const {
		settingsPageUrl,
	} = globalManifest;

	const formId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<PanelBody title={__('Form', 'eightshift-forms')}>
			<Button
				isPrimary
				icon={icons.options}
				href={`${settingsPageUrl}&formId=${formId}&type=mailer`}
				style={{ height: '3rem', paddingLeft: '0.5rem', paddingRight: '0.5rem', }}
			>
				<span>
					<span>{__('Form settings', 'eightshift-forms')}</span>
					<br />
					<small>{__('Configure the form and integrations', 'eightshift-forms')}</small>
				</span>
			</Button>


			<hr />

			<FormOptionsComponent
				{...props('form', attributes, {
					setAttributes,
				})}
			/>
		</PanelBody>
	);
};
