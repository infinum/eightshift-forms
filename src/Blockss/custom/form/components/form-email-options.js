import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { TextControl, BaseControl, ToggleControl } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';
import { NewSection } from '../../../components/new-section/new-section';

export const FormEmailOptions = (props) => {
  const {
    richTextClass,
    to,
    subject,
    message = '',
    additionalHeaders,
    sendConfirmationToSender,
    confirmationMessage,
    confirmationSubject,
    onChangeTo,
    onChangeSubject,
    onChangeMessage,
    onChangeAdditionalHeaders,
    onChangeSendConfirmationToSender,
    onChangeConfirmationMessage,
    onChangeConfirmationSubject,
  } = props;

  return (
    <Fragment>
      <NewSection
        label={__('Admin email settings', 'eightshift-forms')}
      />
      {onChangeTo &&
        <TextControl
          label={__('To', 'eightshift-forms')}
          help={__('Email address to which the form sends emails to.', 'eightshift-forms')}
          value={to}
          onChange={onChangeTo}
        />
      }
      {onChangeSubject &&
        <TextControl
          label={__('Subject', 'eightshift-forms')}
          help={__('Subject of the email.', 'eightshift-forms')}
          value={subject}
          onChange={onChangeSubject}
        />
      }
      {onChangeMessage &&
        <BaseControl
          label={__('Message', 'eightshift-forms')}
          help={__('Message which is sent on form submit. Remember you can use placeholders.', 'eightshift-forms')}
        >
          <RichText
            className={richTextClass}
            placeholder={__('Add your message', 'eightshift-forms')}
            onChange={onChangeMessage}
            value={message}
          />
        </BaseControl>
      }
      {onChangeAdditionalHeaders &&
        <BaseControl
          label={__('Additional headers (optional)', 'eightshift-forms')}
          help={__('Add additional headers for this email.', 'eightshift-forms')}
        >
          <RichText
            className={richTextClass}
            placeholder={__('Add additional headers', 'eightshift-forms')}
            onChange={additionalHeaders}
            value={onChangeAdditionalHeaders}
          />
        </BaseControl>
      }
      <NewSection
        label={__('Confirmation email settings', 'eightshift-forms')}
      />
      {onChangeSendConfirmationToSender &&
        <ToggleControl
          label={__('Send email confirmation to sender?', 'eightshift-forms')}
          help={__('If enabled, an email will be sent to the user after filling out this form.', 'eightshift-forms')}
          checked={sendConfirmationToSender}
          onChange={onChangeSendConfirmationToSender}
        />
      }
      {onChangeConfirmationSubject && sendConfirmationToSender &&
        <TextControl
          label={__('Subject', 'eightshift-forms')}
          help={__('Subject of the confirmation email.', 'eightshift-forms')}
          value={confirmationSubject}
          onChange={onChangeConfirmationSubject}
        />
      }
      {onChangeConfirmationMessage && sendConfirmationToSender &&
        <BaseControl
          label={__('Message', 'eightshift-forms')}
          help={__('Message which is sent to user on form submit. Remember you can use placeholders.', 'eightshift-forms')}
        >
          <RichText
            className={richTextClass}
            placeholder={__('Add your message', 'eightshift-forms')}
            onChange={onChangeConfirmationMessage}
            value={confirmationMessage}
          />
        </BaseControl>
      }
    </Fragment>
  );
};
