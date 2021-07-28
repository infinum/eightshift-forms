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
import { getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormOptions = ({ setAttributes, attributes }) => {
	const {
		blockClass,
		formAction,
		formMethod,
		formTarget,
		formId,
		formClasses,
		formType,
		formTypesComplex,
		formTypesComplexRedirect,
		formIsComplexType,
		formDynamicsEntity,
		theme,
		formSuccessMessage,
		formErrorMessage,
		formShouldRedirectOnSuccess,
		formRedirectSuccess,
		formEmailTo,
		formEmailSubject,
		formEmailMessage,
		formEmailAdditionalHeaders,
		formEmailSendConfirmationToSender,
		formEmailConfirmationSubject,
		formEmailConfirmationMessage,
		formBuckarooService,
		formBuckarooPaymentDescription,
		formBuckarooEmandateDescription,
		formBuckarooSequenceType,
		formBuckarooIsSequenceTypeOnFrontend,
		formBuckarooRedirectUrl,
		formBuckarooRedirectUrlCancel,
		formBuckarooRedirectUrlError,
		formBuckarooRedirectUrlReject,
		formMailchimpListId,
		formMailchimpAddTag,
		formMailchimpTags,
		formMailchimpAddExistingMembers,
		formMailerliteGroupId,
		formEventNames,
	} = attributes;

	const richTextClass = `${blockClass}__rich-text`;

	const formTypes = [
		{ label: __('Email', 'eightshift-forms'), value: 'email' },
		{ label: __('Custom (PHP)', 'eightshift-forms'), value: 'custom', redirects: true },
		{ label: __('Custom (Event)', 'eightshift-forms'), value: 'custom-event' },
	];

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
		formTypes.push({ label: __('Microsoft Dynamics CRM 365', 'eightshift-forms'), value: 'dynamics-crm' });
	}

	if (isBuckarooUsed) {
		formTypes.push({ label: __('Buckaroo', 'eightshift-forms'), value: 'buckaroo', redirects: true });
	}

	if (isMailchimpUsed) {
		formTypes.push({ label: __('Mailchimp', 'eightshift-forms'), value: 'mailchimp' });
	}

	if (isMailerliteUsed) {
		formTypes.push({ label: __('Mailerlite', 'eightshift-forms'), value: 'mailerlite' });
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
					<Fragment>
						{tab.name === 'general' && (
							<Fragment>
								<br />
								<strong className="notice-title">{__('General Options', 'eightshift-forms')}</strong>
								<p>{__('These are general form options.', 'eightshift-forms')}</p>
								<br />
								<FormGeneralOptions
									attributes={attributes}
									blockClass={blockClass}
									formType={formType}
									formIsComplexType={formIsComplexType}
									formTypesComplex={formTypesComplex}
									formTypesComplexRedirect={formTypesComplexRedirect}
									formTypes={formTypes}
									theme={theme}
									formThemeAsOptions={formThemeAsOptions}
									hasThemes={hasThemes}
									richTextClass={richTextClass}
									formSuccessMessage={formSuccessMessage}
									formErrorMessage={formErrorMessage}
									formShouldRedirectOnSuccess={formShouldRedirectOnSuccess}
									formRedirectSuccess={formRedirectSuccess}
									setAttributes={setAttributes}
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
							</Fragment>
						)}
						{tab.name === 'email' && (
							<Fragment>
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
							</Fragment>
						)}
						{tab.name === 'dynamics-crm' && (
							<Fragment>
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
							</Fragment>
						)}
						{tab.name === 'buckaroo' && (
							<Fragment>
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

							</Fragment>
						)}
						{tab.name === 'mailchimp' && (
							<Fragment>
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

							</Fragment>
						)}
						{tab.name === 'mailerlite' && (
							<Fragment>
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

							</Fragment>
						)}
						{tab.name === 'custom' && (
							<Fragment>
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

							</Fragment>
						)}
						{tab.name === 'custom-event' && (
							<Fragment>
								<br />
								<strong className="notice-title">{__('Custom event options', 'eightshift-forms')}</strong>
								<p>{__('These are options for when your form is triggering a custom event.', 'eightshift-forms')}</p>
								<br />
								<FormCustomEventOptions
									attributes={attributes}
									setAttributes={setAttributes}
									blockClass={blockClass}
									formEventNames={formEventNames}
								/>

							</Fragment>
						)}
					</Fragment>
				)}
			</TabPanel>
		</PanelBody>
	);
};
