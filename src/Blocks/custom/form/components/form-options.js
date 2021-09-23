import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from "@wordpress/data";
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { props, IconLabel, icons } from '@eightshift/frontend-libs/scripts';
import { FormOptions as FormOptionsComponent } from '../../../components/form/components/form-options';
import globalManifest from './../../../manifest.json';

export const FormOptions = ({ attributes, setAttributes }) => {
	const {
		settingsPageUrl,
	} = globalManifest;

	const formId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<PanelBody title={__('Form', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On settings page you can setup email settings, integrations and much more.', 'eightshift-forms')}
			>
				<Button
					label={__('Open Form Settings Page', 'eightshift-forms')}
					href={`${settingsPageUrl}&formId=${formId}&setting=general`}
					isSecondary
				>
					{__('Open Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>

			<hr />

			<FormOptionsComponent
				{...props('form', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</PanelBody>
	);
};
