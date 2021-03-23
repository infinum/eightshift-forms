import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { PanelBody, TextControl, TabPanel, Dashicon } from '@wordpress/components';
import { FormGeneralOptions } from './form-general-options';
import { FormDynamicsCrmOptions } from './form-dynamics-crm-options';
import { FormBuckarooOptions } from './form-buckaroo-options';
import { FormEmailOptions } from './form-email-options';
import { FormMailchimpOptions } from './form-mailchimp-options';
import { FormCustomEventOptions } from './form-custom-event-options';
import { FormCustomOptions } from './form-custom-options';

export const FormOptions = (props) => {
  const {
    attributes: {
      blockClass,
      action,
      method,
      target,
      id,
      classes,
      type,
      typesComplex,
      typesComplexRedirect,
      isComplexType,
      dynamicsEntity,
      theme,
      successMessage,
      errorMessage,
      shouldRedirectOnSuccess,
      redirectSuccess,
      emailTo,
      emailSubject,
      emailMessage,
      emailAdditionalHeaders,
      emailSendConfirmationToSender,
      emailConfirmationSubject,
      emailConfirmationMessage,
      buckarooService,
      buckarooPaymentDescription,
      buckarooEmandateDescription,
      buckarooSequenceType,
      buckarooIsSequenceTypeOnFrontend,
      buckarooRedirectUrl,
      buckarooRedirectUrlCancel,
      buckarooRedirectUrlError,
      buckarooRedirectUrlReject,
      mailchimpListId,
      mailchimpAddTag,
      mailchimpTags,
      mailchimpAddExistingMembers,
      eventNames,
    },
    actions: {
      onChangeAction,
      onChangeMethod,
      onChangeTarget,
      onChangeId,
      onChangeClasses,
      onChangeType,
      onChangeTypesComplex,
      onChangeTypesComplexRedirect,
      onChangeIsComplexType,
      onChangeDynamicsEntity,
      onChangeTheme,
      onChangeSuccessMessage,
      onChangeErrorMessage,
      onChangeShouldRedirectOnSuccess,
      onChangeRedirectSuccess,
      onChangeEmailTo,
      onChangeEmailSubject,
      onChangeEmailMessage,
      onChangeEmailAdditionalHeaders,
      onChangeEmailSendConfirmationToSender,
      onChangeEmailConfirmationSubject,
      onChangeEmailConfirmationMessage,
      onChangeBuckarooService,
      onChangeBuckarooPaymentDescription,
      onChangeBuckarooEmandateDescription,
      onChangeBuckarooSequenceType,
      onChangeBuckarooIsSequenceTypeOnFrontend,
      onChangeBuckarooRedirectUrl,
      onChangeBuckarooRedirectUrlCancel,
      onChangeBuckarooRedirectUrlError,
      onChangeBuckarooRedirectUrlReject,
      onChangeMailchimpListId,
      onChangeMailchimpAddTag,
      onChangeMailchimpTags,
      onChangeMailchimpAddExistingMembers,
      onChangeEventNames,
    },
  } = props;

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
    dynamicsCrm = [],
  } = window.eightshiftForms;

  const mailchimpAdmin = window.eightshiftFormsAdmin.mailchimp || {};

  const audiences = (mailchimpAdmin && mailchimpAdmin.audiences) ? mailchimpAdmin.audiences : [];

  const themeAsOptions = hasThemes ? themes.map((tempTheme) => ({ label: tempTheme, value: tempTheme })) : [];

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

  const tabs = [
    {
      name: 'general',
      title: <Dashicon icon="admin-generic" />,
      className: 'tab-general components-button is-button is-default custom-button-with-icon',
    },
  ];

  if ((!isComplexType && type === 'email') || (isComplexType && typesComplex.includes('email'))) {
    tabs.push({
      name: 'email',
      title: <Dashicon icon="email" />,
      className: 'tab-email components-button is-button is-default custom-button-with-icon',
    });
  }

  if (isDynamicsCrmUsed && (
    (!isComplexType && type === 'dynamics-crm') || (isComplexType && typesComplex.includes('dynamics-crm'))
  )) {
    tabs.push({
      name: 'dynamics-crm',
      title: <Dashicon icon="cloud-upload" />,
      className: 'tab-dynamics-crm components-button is-button is-default custom-button-with-icon',
    });
  }

  if (isBuckarooUsed && (
    (!isComplexType && type === 'buckaroo') || (isComplexType && typesComplexRedirect.includes('buckaroo'))
  )) {
    tabs.push({
      name: 'buckaroo',
      title: <Dashicon icon="money" />,
      className: 'tab-buckaroo components-button is-button is-default custom-button-with-icon',
    });
  }

  if (isMailchimpUsed && (
    (!isComplexType && type === 'mailchimp') || (isComplexType && typesComplex.includes('mailchimp'))
  )) {
    tabs.push({
      name: 'mailchimp',
      title: <Dashicon icon="email-alt2" />,
      className: 'tab-mailchimp components-button is-button is-default custom-button-with-icon',
    });
  }

  if ((!isComplexType && type === 'custom') || (isComplexType && typesComplexRedirect.includes('custom'))) {
    tabs.push({
      name: 'custom',
      title: <Dashicon icon="arrow-right-alt" />,
      className: 'tab-custom components-button is-button is-default custom-button-with-icon',
    });
  }

  if ((!isComplexType && type === 'custom-event') || (isComplexType && typesComplex.includes('custom-event'))) {
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
                  blockClass={blockClass}
                  type={type}
                  isComplexType={isComplexType}
                  typesComplex={typesComplex}
                  typesComplexRedirect={typesComplexRedirect}
                  formTypes={formTypes}
                  theme={theme}
                  themeAsOptions={themeAsOptions}
                  hasThemes={hasThemes}
                  richTextClass={richTextClass}
                  successMessage={successMessage}
                  errorMessage={errorMessage}
                  shouldRedirectOnSuccess={shouldRedirectOnSuccess}
                  redirectSuccess={redirectSuccess}
                  onChangeType={onChangeType}
                  onChangeTypesComplex={onChangeTypesComplex}
                  onChangeTypesComplexRedirect={onChangeTypesComplexRedirect}
                  onChangeIsComplexType={onChangeIsComplexType}
                  onChangeTheme={onChangeTheme}
                  onChangeSuccessMessage={onChangeSuccessMessage}
                  onChangeErrorMessage={onChangeErrorMessage}
                  onChangeShouldRedirectOnSuccess={onChangeShouldRedirectOnSuccess}
                  onChangeRedirectSuccess={onChangeRedirectSuccess}
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
                  richTextClass={richTextClass}
                  to={emailTo}
                  subject={emailSubject}
                  message={emailMessage}
                  additionalHeaders={emailAdditionalHeaders}
                  sendConfirmationToSender={emailSendConfirmationToSender}
                  confirmationMessage={emailConfirmationMessage}
                  confirmationSubject={emailConfirmationSubject}
                  onChangeTo={onChangeEmailTo}
                  onChangeSubject={onChangeEmailSubject}
                  onChangeMessage={onChangeEmailMessage}
                  onChangeAdditionalHeaders={onChangeEmailAdditionalHeaders}
                  onChangeSendConfirmationToSender={onChangeEmailSendConfirmationToSender}
                  onChangeConfirmationSubject={onChangeEmailConfirmationSubject}
                  onChangeConfirmationMessage={onChangeEmailConfirmationMessage}
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
                  type={type}
                  crmEntitiesAsOptions={crmEntitiesAsOptions}
                  dynamicsEntity={dynamicsEntity}
                  isDynamicsCrmUsed={isDynamicsCrmUsed}
                  onChangeDynamicsEntity={onChangeDynamicsEntity}
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
                  blockClass={blockClass}
                  type={type}
                  service={buckarooService}
                  paymentDescription={buckarooPaymentDescription}
                  emandateDescription={buckarooEmandateDescription}
                  sequenceType={buckarooSequenceType}
                  isSequenceTypeOnFrontend={buckarooIsSequenceTypeOnFrontend}
                  redirectUrl={buckarooRedirectUrl}
                  redirectUrlCancel={buckarooRedirectUrlCancel}
                  redirectUrlError={buckarooRedirectUrlError}
                  redirectUrlReject={buckarooRedirectUrlReject}
                  onChangeService={onChangeBuckarooService}
                  onChangeEmandateDescription={onChangeBuckarooEmandateDescription}
                  onChangePaymentDescription={onChangeBuckarooPaymentDescription}
                  onChangeSequenceType={onChangeBuckarooSequenceType}
                  onChangeRedirectUrl={onChangeBuckarooRedirectUrl}
                  onChangeRedirectUrlCancel={onChangeBuckarooRedirectUrlCancel}
                  onChangeRedirectUrlError={onChangeBuckarooRedirectUrlError}
                  onChangeRedirectUrlReject={onChangeBuckarooRedirectUrlReject}
                  onChangeIsSequenceTypeOnFrontend={onChangeBuckarooIsSequenceTypeOnFrontend}
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
                  blockClass={blockClass}
                  type={type}
                  listId={mailchimpListId}
                  audiences={audiences}
                  addTag={mailchimpAddTag}
                  tags={mailchimpTags}
                  addExistingMembers={mailchimpAddExistingMembers}
                  onChangeListId={onChangeMailchimpListId}
                  onChangeAddTag={onChangeMailchimpAddTag}
                  onChangeTags={onChangeMailchimpTags}
                  onChangeAddExistingMembers={onChangeMailchimpAddExistingMembers}
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
                  action={action}
                  method={method}
                  target={target}
                  onChangeAction={onChangeAction}
                  onChangeMethod={onChangeMethod}
                  onChangeTarget={onChangeTarget}
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
                  blockClass={blockClass}
                  type={type}
                  eventNames={eventNames}
                  onChangeEventNames={onChangeEventNames}
                />

              </Fragment>
            )}
          </Fragment>
        )}
      </TabPanel>

      {onChangeClasses &&
        <TextControl
          label={__('Classes', 'eightshift-forms')}
          value={classes}
          onChange={onChangeClasses}
        />
      }

      {onChangeId &&
        <TextControl
          label={__('ID', 'eightshift-forms')}
          value={id}
          onChange={onChangeId}
        />
      }
    </PanelBody>
  );
};
