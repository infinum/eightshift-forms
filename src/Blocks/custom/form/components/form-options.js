import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { PanelBody, TextControl, TabPanel, Dashicon } from '@wordpress/components';
import { FormGeneralOptions } from './form-general-options';
import { FormDynamicsCrmOptions } from './form-dynamics-crm-options';
import { FormBuckarooOptions } from './form-buckaroo-options';
import { FormEmailOptions } from './form-email-options';
import { FormMailchimpOptions } from './form-mailchimp-options';
import { FormMailerliteOptions } from './form-mailerlite-options';
import { FormCustomEventOptions } from './form-custom-event-options';
import { FormCustomOptions } from './form-custom-options';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormOptions = ({ setAttributes, attributes }) => {
	const {
		blockClass,
		theme,
	} = attributes;

	const formAction = checkAttr('formAction', attributes, manifest);
	const formMethod = checkAttr('formMethod', attributes, manifest);
	const formTarget = checkAttr('formTarget', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);
	const formClasses = checkAttr('formClasses', attributes, manifest);
	const formType = checkAttr('formType', attributes, manifest);
	const formTypesComplex = checkAttr('formTypesComplex', attributes, manifest);
	const formTypesComplexRedirect = checkAttr('formTypesComplexRedirect', attributes, manifest);
	const formIsComplexType = checkAttr('formIsComplexType', attributes, manifest);
	const formDynamicsEntity = checkAttr('formDynamicsEntity', attributes, manifest);
	const formSuccessMessage = checkAttr('formSuccessMessage', attributes, manifest);
	const formErrorMessage = checkAttr('formErrorMessage', attributes, manifest);
	const formShouldRedirectOnSuccess = checkAttr('formShouldRedirectOnSuccess', attributes, manifest);
	const formRedirectSuccess = checkAttr('formRedirectSuccess', attributes, manifest);
	const formEmailTo = checkAttr('formEmailTo', attributes, manifest);
	const formEmailSubject = checkAttr('formEmailSubject', attributes, manifest);
	const formEmailMessage = checkAttr('formEmailMessage', attributes, manifest);
	const formEmailAdditionalHeaders = checkAttr('formEmailAdditionalHeaders', attributes, manifest);
	const formEmailSendConfirmationToSender = checkAttr('formEmailSendConfirmationToSender', attributes, manifest);
	const formEmailConfirmationSubject = checkAttr('formEmailConfirmationSubject', attributes, manifest);
	const formEmailConfirmationMessage = checkAttr('formEmailConfirmationMessage', attributes, manifest);
	const formBuckarooService = checkAttr('formBuckarooService', attributes, manifest);
	const formBuckarooPaymentDescription = checkAttr('formBuckarooPaymentDescription', attributes, manifest);
	const formBuckarooEmandateDescription = checkAttr('formBuckarooEmandateDescription', attributes, manifest);
	const formBuckarooSequenceType = checkAttr('formBuckarooSequenceType', attributes, manifest);
	const formBuckarooIsSequenceTypeOnFrontend = checkAttr('formBuckarooIsSequenceTypeOnFrontend', attributes, manifest);
	const formBuckarooRedirectUrl = checkAttr('formBuckarooRedirectUrl', attributes, manifest);
	const formBuckarooRedirectUrlCancel = checkAttr('formBuckarooRedirectUrlCancel', attributes, manifest);
	const formBuckarooRedirectUrlError = checkAttr('formBuckarooRedirectUrlError', attributes, manifest);
	const formBuckarooRedirectUrlReject = checkAttr('formBuckarooRedirectUrlReject', attributes, manifest);
	const formMailchimpListId = checkAttr('formMailchimpListId', attributes, manifest);
	const formMailchimpAddTag = checkAttr('formMailchimpAddTag', attributes, manifest);
	const formMailchimpTags = checkAttr('formMailchimpTags', attributes, manifest);
	const formMailchimpAddExistingMembers = checkAttr('formMailchimpAddExistingMembers', attributes, manifest);
	const formMailerliteGroupId = checkAttr('formMailerliteGroupId', attributes, manifest);
	const formEventNames = checkAttr('formEventNames', attributes, manifest);

	const formTypeOptions = [
		{ label: __('Email', 'eightshift-forms'), value: 'email' },
		{ label: __('Custom (PHP)', 'eightshift-forms'), value: 'custom', redirects: true },
		{ label: __('Custom (Event)', 'eightshift-forms'), value: 'custom-event' },
	];

	const richTextClass = `${blockClass}__rich-text`;

	const {
		hasThemes,
		themes = [],
		isDynamicsCrmUsed,
		isBuckarooUsed,
		isMailchimpUsed,
		isMailerliteUsed,
		dynamicsCrm = [],
	} = window.eightshiftForms;

	const mailchimpAdmin = window.eightshiftFormsAdmin.mailchimp || {};
	const mailerliteAdmin = window.eightshiftFormsAdmin.mailerlite || {};
	const formMailchimpAudiences = (mailchimpAdmin && mailchimpAdmin.audiences) ? mailchimpAdmin.audiences : [];
	const formMailerliteGroups = (mailerliteAdmin && mailerliteAdmin.groups) ? mailerliteAdmin.groups : [];

	const formThemeAsOptions = hasThemes ? themes.map((tempTheme) => ({ label: tempTheme, value: tempTheme })) : [];

	let crmEntitiesAsOptions = [];
	if (isDynamicsCrmUsed) {
		crmEntitiesAsOptions = [
			{ label: __('Select CRM entity', 'eightshift-forms'), value: 'select-please' },
			...dynamicsCrm.availableEntities.map((entity) => ({ label: entity, value: entity })),
		];
		formTypeOptions.push({ label: __('Microsoft Dynamics CRM 365', 'eightshift-forms'), value: 'dynamics-crm' });
	}

	if (isBuckarooUsed) {
		formTypeOptions.push({ label: __('Buckaroo', 'eightshift-forms'), value: 'buckaroo', redirects: true });
	}

	if (isMailchimpUsed) {
		formTypeOptions.push({ label: __('Mailchimp', 'eightshift-forms'), value: 'mailchimp' });
	}

	if (isMailerliteUsed) {
		formTypeOptions.push({ label: __('Mailerlite', 'eightshift-forms'), value: 'mailerlite' });
	}

	const tabs = [
		{
			name: 'general',
			title: <Dashicon icon="admin-generic" />,
			className: 'tab-general components-button is-button is-default custom-button-with-icon',
		},
	];

	if ((!formIsComplexType && formType === 'email') || (formIsComplexType && formTypesComplex.includes('email'))) {
		tabs.push({
			name: 'email',
			title: <Dashicon icon="email" />,
			className: 'tab-email components-button is-button is-default custom-button-with-icon',
		});
	}

	if (isDynamicsCrmUsed && (
		(!formIsComplexType && formType === 'dynamics-crm') || (formIsComplexType && formTypesComplex.includes('dynamics-crm'))
	)) {
		tabs.push({
			name: 'dynamics-crm',
			title: <Dashicon icon="cloud-upload" />,
			className: 'tab-dynamics-crm components-button is-button is-default custom-button-with-icon',
		});
	}

	if (isBuckarooUsed && (
		(!formIsComplexType && formType === 'buckaroo') || (formIsComplexType && formTypesComplexRedirect.includes('buckaroo'))
	)) {
		tabs.push({
			name: 'buckaroo',
			title: <Dashicon icon="money" />,
			className: 'tab-buckaroo components-button is-button is-default custom-button-with-icon',
		});
	}

	if (isMailchimpUsed && (
		(!formIsComplexType && formType === 'mailchimp') || (formIsComplexType && formTypesComplex.includes('mailchimp'))
	)) {
		tabs.push({
			name: 'mailchimp',
			title: <Dashicon icon="email-alt2" />,
			className: 'tab-mailchimp components-button is-button is-default custom-button-with-icon',
		});
	}

	if (isMailerliteUsed && (
		(!formIsComplexType && formType === 'mailerlite') || (formIsComplexType && formTypesComplex.includes('mailerlite'))
	)) {
		tabs.push({
			name: 'mailerlite',
			title: <Dashicon icon="email-alt2" />,
			className: 'tab-mailerlite components-button is-button is-default custom-button-with-icon',
		});
	}

	if ((!formIsComplexType && formType === 'custom') || (formIsComplexType && formTypesComplexRedirect.includes('custom'))) {
		tabs.push({
			name: 'custom',
			title: <Dashicon icon="arrow-right-alt" />,
			className: 'tab-custom components-button is-button is-default custom-button-with-icon',
		});
	}

	if ((!formIsComplexType && formType === 'custom-event') || (formIsComplexType && formTypesComplex.includes('custom-event'))) {
		tabs.push({
			name: 'custom-event',
			title: <Dashicon icon="megaphone" />,
			className: 'tab-custom-event components-button is-button is-default custom-button-with-icon',
		});
	}

	return (
		<PanelBody title={__('Form Settings', 'eightshift-forms')}>
			<TabPanel
				className="custom-button-tabs"
				activeClass="components-button is-button is-primary"
				tabs={tabs}
			>
				{(tab) => (
					<>
						{tab.name === 'general' && (
							<>
								<br />
								<strong className="notice-title">{__('General Options', 'eightshift-forms')}</strong>
								<p>{__('These are general form options.', 'eightshift-forms')}</p>
								<br />
								<FormGeneralOptions
									attributes={attributes}
									setAttributes={setAttributes}
									blockClass={blockClass}
									formType={formType}
									formIsComplexType={formIsComplexType}
									formTypesComplex={formTypesComplex}
									formTypesComplexRedirect={formTypesComplexRedirect}
									formTypeOptions={formTypeOptions}
									formThemeAsOptions={formThemeAsOptions}
									formSuccessMessage={formSuccessMessage}
									formErrorMessage={formErrorMessage}
									formShouldRedirectOnSuccess={formShouldRedirectOnSuccess}
									formRedirectSuccess={formRedirectSuccess}
									hasThemes={hasThemes}
									theme={theme}
									richTextClass={richTextClass}
								/>

								<TextControl
									label={__('Classes', 'eightshift-forms')}
									value={formClasses}
									onChange={(value) => setAttributes({ [getAttrKey('formClasses', attributes, manifest)]: value })}
								/>

								<TextControl
									label={__('ID', 'eightshift-forms')}
									value={formId}
									onChange={(value) => setAttributes({ [getAttrKey('formId', attributes, manifest)]: value })}
								/>
							</>
						)}
						{tab.name === 'email' && (
							<>
								<br />
								<strong className="notice-title">{__('Email Options', 'eightshift-forms')}</strong>
								<p>{__('These are options for when your form is sending emails. You can use form fields by name as placeholders in Subject and Message fields in the following format [[field_name]]. These will be replace with actual field values before sending.', 'eightshift-forms')}</p>
								<br />
								<FormEmailOptions
									attributes={attributes}
									setAttributes={setAttributes}
									richTextClass={richTextClass}
									formEmailTo={formEmailTo}
									formEmailSubject={formEmailSubject}
									formEmailMessage={formEmailMessage}
									formEmailAdditionalHeaders={formEmailAdditionalHeaders}
									formEmailSendConfirmationToSender={formEmailSendConfirmationToSender}
									formEmailConfirmationMessage={formEmailConfirmationMessage}
									formEmailConfirmationSubject={formEmailConfirmationSubject}
								/>
							</>
						)}
						{tab.name === 'dynamics-crm' && (
							<>
								<br />
								<strong className="notice-title">{__('Dynamics CRM Options', 'eightshift-forms')}</strong>
								<p>{__('These are options for when your form is sending data to Dynamics CRM.', 'eightshift-forms')}</p>
								<br />
								<FormDynamicsCrmOptions
									attributes={attributes}
									setAttributes={setAttributes}
									formType={formType}
									formDynamicsEntity={formDynamicsEntity}
									crmEntitiesAsOptions={crmEntitiesAsOptions}
									isDynamicsCrmUsed={isDynamicsCrmUsed}
								/>
							</>
						)}
						{tab.name === 'buckaroo' && (
							<>
								<br />
								<strong className="notice-title">{__('Buckaroo Options', 'eightshift-forms')}</strong>
								<p>{__('These are options for when your form is sending data to Buckaroo.', 'eightshift-forms')}</p>
								<br />
								<FormBuckarooOptions
									attributes={attributes}
									setAttributes={setAttributes}
									blockClass={blockClass}
									formBuckarooService={formBuckarooService}
									formBuckarooPaymentDescription={formBuckarooPaymentDescription}
									formBuckarooEmandateDescription={formBuckarooEmandateDescription}
									formBuckarooSequenceType={formBuckarooSequenceType}
									formBuckarooIsSequenceTypeOnFrontend={formBuckarooIsSequenceTypeOnFrontend}
									redirectUrl={formBuckarooRedirectUrl}
									redirectUrlCancel={formBuckarooRedirectUrlCancel}
									redirectUrlError={formBuckarooRedirectUrlError}
									redirectUrlReject={formBuckarooRedirectUrlReject}
								/>

							</>
						)}
						{tab.name === 'mailchimp' && (
							<>
								<br />
								<strong className="notice-title">{__('Mailchimp Options', 'eightshift-forms')}</strong>
								<p>{__('These are options for when your form is sending data to Mailchimp.', 'eightshift-forms')}</p>
								<br />
								<FormMailchimpOptions
									attributes={attributes}
									setAttributes={setAttributes}
									formMailchimpListLid={formMailchimpListId}
									formMailchimpAudiences={formMailchimpAudiences}
									formMailchimpAddTag={formMailchimpAddTag}
									formMailchimpTags={formMailchimpTags}
									formMailchimpAddExistingMembers={formMailchimpAddExistingMembers}
								/>

							</>
						)}
						{tab.name === 'mailerlite' && (
							<>
								<br />
								<strong className="notice-title">{__('MailerLite Options', 'eightshift-forms')}</strong>
								<p>{__('These are the options for when your form is sending data to MailerLite.', 'eightshift-forms')}</p>
								<br />
								<FormMailerliteOptions
									attributes={attributes}
									setAttributes={setAttributes}
									formMailerliteGroupId={formMailerliteGroupId}
									formMailerliteGroups={formMailerliteGroups}
								/>

							</>
						)}
						{tab.name === 'custom' && (
							<>
								<br />
								<strong className="notice-title">{__('Custom PHP action', 'eightshift-forms')}</strong>
								<p>{__('These are options for when your form is triggering a custom PHP action.', 'eightshift-forms')}</p>
								<br />
								<FormCustomOptions
									attributes={attributes}
									setAttributes={setAttributes}
									formAction={formAction}
									formMethod={formMethod}
									formTarget={formTarget}
								/>

							</>
						)}
						{tab.name === 'custom-event' && (
							<>
								<br />
								<strong className="notice-title">{__('Custom event options', 'eightshift-forms')}</strong>
								<p>{__('These are options for when your form is triggering a custom event.', 'eightshift-forms')}</p>
								<br />
								<FormCustomEventOptions
									attributes={attributes}
									setAttributes={setAttributes}
									formEventNames={formEventNames}
								/>

							</>
						)}
					</>
				)}
			</TabPanel>
		</PanelBody>
	);
};
