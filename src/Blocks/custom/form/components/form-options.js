import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from "@wordpress/data";
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons, props } from '@eightshift/frontend-libs/scripts';
import { FormOptions as FormOptionsComponent } from '../../../components/form/components/form-options';
import globalManifest from '../../../manifest.json';

export const FormOptions = ({ attributes, setAttributes }) => {
	const {
		settingsPageUrl,
	} = globalManifest;

	const formId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<PanelBody title={__('Form', 'eightshift-forms')}>
			<FormOptionsComponent
				{...props('form', attributes, {
					setAttributes,
				})}
			/>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On Mailer settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${settingsPageUrl}&formId=${formId}&type=mailer`}
					isSecondary
				>
					{__('Open Mailer Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
