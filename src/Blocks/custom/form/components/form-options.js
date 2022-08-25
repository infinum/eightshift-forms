/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, select } from "@wordpress/data";
import { PanelBody, Button } from '@wordpress/components';
import { icons, props, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FormOptions as FormOptionsComponent } from '../../../components/form/components/form-options';

export const FormOptions = ({ attributes, setAttributes }) => {
	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	const formId = useSelect((select) => select('core/editor').getCurrentPostId());
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	return (
		<PanelBody title={__('Form', 'eightshift-forms')}>
			<Button
				isPrimary
				icon={icons.options}
				href={`${wpAdminUrl}${settingsPageUrl}&formId=${formId}&type=mailer`}
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
