import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { SelectControl, TextControl, TextareaControl, BaseControl, ToggleControl } from '@wordpress/components';
import { checkAttr, getAttrKey, getOption } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormBuckarooOptions = ({
	attributes,
	setAttributes,
	blockClass,
	redirectUrl,
	redirectUrlError,
	redirectUrlReject,
}) => {

	const formBuckarooService = checkAttr('formBuckarooService', attributes, manifest);
	const formBuckarooPaymentDescription = checkAttr('formBuckarooPaymentDescription', attributes, manifest);
	const formBuckarooEmandateDescription = checkAttr('formBuckarooEmandateDescription', attributes, manifest);
	const formBuckarooSequenceType = checkAttr('formBuckarooSequenceType', attributes, manifest);
	const formBuckarooIsSequenceTypeOnFrontend = checkAttr('formBuckarooIsSequenceTypeOnFrontend', attributes, manifest);

	const MAX_CHARS_IN_EMANDATE_DESCRIPTION_FIELD = 70;

	const fieldsForService = {
		ideal: [
			{
				name: 'donation-amount',
				required: true,
			},
			{
				name: 'issuer',
			},
		],
		emandate: [
			{
				name: 'issuer',
			},
		],
	};

	return (
		<>
			<SelectControl
				label={__('Service', 'eightshift-forms')}
				help={__('Please select which Buckaroo formBuckarooService you wish to use', 'eightshift-forms')}
				value={formBuckarooService}
				options={getOption('formBuckarooService', attributes, manifest)}
				onChange={(value) => setAttributes({ [getAttrKey('formBuckarooService', attributes, manifest)]: value })}
			/>
			{fieldsForService[formBuckarooService] &&
				<BaseControl>
					<div className={`${blockClass}__fields-for-formBuckarooService`}>
						<h3>{__('When using this formBuckarooService, you should add fields with the following names: ', 'eightshift-forms')}</h3>
						<ul className={`${blockClass}__fields-for-formBuckarooService-list`}>
							{fieldsForService[formBuckarooService].map((serviceField, key) => {
								return (
									<li key={key}>{!serviceField.required ? <i>{__('(Optional)', 'eightshift-forms')}</i> : ''} {serviceField.name}</li>
								);
							})}
						</ul>
					</div>
				</BaseControl>
			}
			{formBuckarooService === 'emandate' &&
				<ToggleControl
					label={__('Allow user to set Recurring / One time in form?', 'eightshift-forms')}
					help={__('If enabled you need to allow the user to select the recurring / one-time on frontend. You need to add a pre-defined field for this OR a field with name "sequence-type" to the form.', 'eightshift-forms')}
					checked={formBuckarooIsSequenceTypeOnFrontend}
					onChange={(value) => setAttributes({ [getAttrKey('formBuckarooIsSequenceTypeOnFrontend', attributes, manifest)]: value })}
				/>
			}
			{!formBuckarooIsSequenceTypeOnFrontend && formBuckarooService === 'emandate' &&
				<SelectControl
					label={__('Recurring / One off?', 'eightshift-forms')}
					help={__('Set if this form will create a recurring or one-off emandate.', 'eightshift-forms')}
					value={formBuckarooSequenceType}
					options={getOption('formBuckarooSequenceType', attributes, manifest)}
					onChange={(value) => setAttributes({ [getAttrKey('formBuckarooSequenceType', attributes, manifest)]: value })}
				/>
			}

			<TextareaControl
				label={__('Payment description', 'eightshift-forms')}
				help={__('A description of for this transaction', 'eightshift-forms')}
				value={formBuckarooPaymentDescription}
				onChange={(value) => setAttributes({ [getAttrKey('formBuckarooPaymentDescription', attributes, manifest)]: value })}
			/>

			{formBuckarooService === 'emandate' &&
				<TextareaControl
					label={__('Emandate reason', 'eightshift-forms')}
					value={formBuckarooEmandateDescription}
					help={__('A description of the (purpose) of the emandate. This will be shown in the emandate information of the customers\' bank account. Max 70 characters.', 'eightshift-forms')}
					onChange={(newValue) => {
						if (newValue.length > 0) {
							setAttributes({ [getAttrKey('formBuckarooEmandateDescription', attributes, manifest)]: newValue.substring(0, MAX_CHARS_IN_EMANDATE_DESCRIPTION_FIELD) })
						}
					}}
				/>
			}

			<TextControl
				label={__('Redirect url (on success)', 'eightshift-forms')}
				value={redirectUrl}
				onChange={(value) => setAttributes({ [getAttrKey('formBuckarooRedirectUrl', attributes, manifest)]: value })}
			/>
			<TextControl
				label={__('Redirect url (on error)', 'eightshift-forms')}
				value={redirectUrlError}
				onChange={(value) => setAttributes({ [getAttrKey('formBuckarooRedirectUrlError', attributes, manifest)]: value })}
			/>
			{formBuckarooService === 'ideal' &&
				<TextControl
					label={__('Redirect url (when payment rejected)', 'eightshift-forms')}
					value={redirectUrlReject}
					onChange={(value) => setAttributes({ [getAttrKey('formBuckarooRedirectUrlReject', attributes, manifest)]: value })}
					/>
			}

		</>
	);
};
