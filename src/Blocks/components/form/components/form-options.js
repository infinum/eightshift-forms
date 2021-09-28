import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	ComponentUseToggle,
	LinkEditComponent
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const formName = checkAttr('formName', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);
	const formSuccessRedirect = checkAttr('formSuccessRedirect', attributes, manifest);
	const formTrackingEventName = checkAttr('formTrackingEventName', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
				help={__('Set unique field name. If not set field will have an generic name.', 'eightshift-forms')}
				value={formName}
				onChange={(value) => setAttributes({ [getAttrKey('formName', attributes, manifest)]: value })}
			/>

			<ComponentUseToggle
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
				showUseToggle={true}
				showLabel={true}
			/>
			{showAdvanced &&
				<>
					<LinkEditComponent
						url={formSuccessRedirect}
						setAttributes={setAttributes}
						showNewTabOption={false}
						help={__('If set form will redirect to this url once submitted with success.', 'eightshift-forms')}
						title={__('Redirect', 'eightshift-forms')}
						urlAttrName={getAttrKey('formSuccessRedirect', attributes, manifest)}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Tracking Event Name', 'eightshift-forms')} />}
						help={__('Provide GTM tracking event name.', 'eightshift-forms')}
						value={formTrackingEventName}
						onChange={(value) => setAttributes({ [getAttrKey('formTrackingEventName', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Id', 'eightshift-forms')} />}
						help={__('Provide forms unique ID.', 'eightshift-forms')}
						value={formId}
						onChange={(value) => setAttributes({ [getAttrKey('formId', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
