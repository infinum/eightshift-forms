import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { PanelBody, TextControl, TabPanel, Dashicon } from '@wordpress/components';
import { FormGeneralOptions } from './form-general-options';
import { FormDynamicsCrmOptions } from './form-dynamics-crm-options';
import { FormBuckarooOptions } from './form-buckaroo-options';
import { FormEmailOptions } from './form-email-options';
import { FormMailchimpOptions } from './form-mailchimp-options';

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
      dynamicsEntity,
      theme,
      successMessage,
      errorMessage,
      emailTo,
      emailSubject,
      emailMessage,
      emailAdditionalHeaders,
      buckarooService,
      buckarooEmandateDescription,
      buckarooSequenceType,
      buckarooRedirectUrl,
      buckarooRedirectUrlCancel,
      buckarooRedirectUrlError,
      buckarooRedirectUrlReject,
      mailchimpListId,
      mailchimpAddTag,
      mailchimpTags,
    },
    actions: {
      onChangeAction,
      onChangeMethod,
      onChangeTarget,
      onChangeId,
      onChangeClasses,
      onChangeType,
      onChangeDynamicsEntity,
      onChangeTheme,
      onChangeSuccessMessage,
      onChangeErrorMessage,
      onChangeEmailTo,
      onChangeEmailSubject,
      onChangeEmailMessage,
      onChangeEmailAdditionalHeaders,
      onChangeBuckarooService,
      onChangeBuckarooEmandateDescription,
      onChangeBuckarooSequenceType,
      onChangeBuckarooRedirectUrl,
      onChangeBuckarooRedirectUrlCancel,
      onChangeBuckarooRedirectUrlError,
      onChangeBuckarooRedirectUrlReject,
      onChangeMailchimpListId,
      onChangeMailchimpAddTag,
      onChangeMailchimpTags,
    },
  } = props;

  const richTextClass = `${blockClass}__rich-text`;

  const formTypes = [
    { label: __('Email', 'eightshift-forms'), value: 'email' },
    { label: __('Custom', 'eightshift-forms'), value: 'custom' },
  ];

  const {
    hasThemes,
    themes = [],
    isDynamicsCrmUsed,
    isBuckarooUsed,
    isMailchimpUsed,
    dynamicsCrm = [],
    mailchimp: {
      audiences,
    },
  } = window.eightshiftForms;

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
    formTypes.push({ label: __('Buckaroo', 'eightshift-forms'), value: 'buckaroo' });
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
    {
      name: 'email',
      title: <Dashicon icon="email" />,
      className: 'tab-email components-button is-button is-default custom-button-with-icon',
    },
  ];

  if (isDynamicsCrmUsed && type === 'dynamics-crm') {
    tabs.push({
      name: type,
      title: <Dashicon icon="cloud-upload" />,
      className: 'tab-dynamics-crm components-button is-button is-default custom-button-with-icon',
    });
  }

  if (isBuckarooUsed && type === 'buckaroo') {
    tabs.push({
      name: type,
      title: <Dashicon icon="money" />,
      className: 'tab-buckaroo components-button is-button is-default custom-button-with-icon',
    });
  }

  if (isMailchimpUsed && type === 'mailchimp') {
    tabs.push({
      name: type,
      title: <Dashicon icon="email-alt2" />,
      className: 'tab-mailchimp components-button is-button is-default custom-button-with-icon',
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
                  type={type}
                  formTypes={formTypes}
                  theme={theme}
                  themeAsOptions={themeAsOptions}
                  hasThemes={hasThemes}
                  richTextClass={richTextClass}
                  successMessage={successMessage}
                  errorMessage={errorMessage}
                  onChangeType={onChangeType}
                  onChangeTheme={onChangeTheme}
                  onChangeSuccessMessage={onChangeSuccessMessage}
                  onChangeErrorMessage={onChangeErrorMessage}
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
                  onChangeTo={onChangeEmailTo}
                  onChangeSubject={onChangeEmailSubject}
                  onChangeMessage={onChangeEmailMessage}
                  onChangeAdditionalHeaders={onChangeEmailAdditionalHeaders}
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
                  emandateDescription={buckarooEmandateDescription}
                  sequenceType={buckarooSequenceType}
                  redirectUrl={buckarooRedirectUrl}
                  redirectUrlCancel={buckarooRedirectUrlCancel}
                  redirectUrlError={buckarooRedirectUrlError}
                  redirectUrlReject={buckarooRedirectUrlReject}
                  onChangeService={onChangeBuckarooService}
                  onChangeEmandateDescription={onChangeBuckarooEmandateDescription}
                  onChangeSequenceType={onChangeBuckarooSequenceType}
                  onChangeRedirectUrl={onChangeBuckarooRedirectUrl}
                  onChangeRedirectUrlCancel={onChangeBuckarooRedirectUrlCancel}
                  onChangeRedirectUrlError={onChangeBuckarooRedirectUrlError}
                  onChangeRedirectUrlReject={onChangeBuckarooRedirectUrlReject}
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
                  onChangeListId={onChangeMailchimpListId}
                  onChangeAddTag={onChangeMailchimpAddTag}
                  onChangeTags={onChangeMailchimpTags}
                />

              </Fragment>
            )}
          </Fragment>
        )}
      </TabPanel>

      {onChangeAction && type === 'custom' &&
        <TextControl
          label={__('Action', 'eightshift-forms')}
          value={action}
          onChange={onChangeAction}
        />
      }

      {onChangeMethod && type === 'custom' &&
        <TextControl
          label={__('Method', 'eightshift-forms')}
          value={method}
          onChange={onChangeMethod}
        />
      }

      {onChangeTarget && type === 'custom' &&
        <TextControl
          label={__('Target', 'eightshift-forms')}
          value={target}
          onChange={onChangeTarget}
        />
      }

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
