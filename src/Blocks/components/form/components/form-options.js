import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, SelectControl, ToggleControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	getOption,
	LinkEditComponent
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const formName = checkAttr('formName', attributes, manifest);
	const formAction = checkAttr('formAction', attributes, manifest);
	const formMethod = checkAttr('formMethod', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);
	const formSuccessRedirect = checkAttr('formSuccessRedirect', attributes, manifest);
	const formTrackingEventName = checkAttr('formTrackingEventName', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
				value={formName}
				onChange={(value) => setAttributes({ [getAttrKey('formName', attributes, manifest)]: value })}
			/>

			<SelectControl
				label={<IconLabel icon={icons.id} label={__('Method', 'eightshift-forms')} />}
				value={formMethod}
				options={getOption('formMethod', attributes, manifest)}
				onChange={(value) => setAttributes({ [getAttrKey('formMethod', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
			/>

			{showAdvanced &&
				<>
					<LinkEditComponent
						url={formSuccessRedirect}
						setAttributes={setAttributes}
						showNewTabOption={false}
						title={__('Redirect', 'eightshift-forms')}
						urlAttrName={getAttrKey('formSuccessRedirect', attributes, manifest)}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Tracking Event Name', 'eightshift-forms')} />}
						value={formTrackingEventName}
						onChange={(value) => setAttributes({ [getAttrKey('formTrackingEventName', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Action', 'eightshift-forms')} />}
						value={formAction}
						onChange={(value) => setAttributes({ [getAttrKey('formAction', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Id', 'eightshift-forms')} />}
						value={formId}
						onChange={(value) => setAttributes({ [getAttrKey('formId', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
