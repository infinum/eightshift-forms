import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { TextControl, BaseControl } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';

export const FormEmailOptions = (props) => {
  const {
    richTextClass,
    to,
    subject,
    message,
    additionalHeaders,
    onChangeTo,
    onChangeSubject,
    onChangeMessage,
    onChangeAdditionalHeaders,
  } = props;

  const defaultMessage = message ?? 'Message from user [[name]]:<br><br>[[message]]';

  return (
    <Fragment>
      {onChangeTo &&
        <TextControl
          label={__('To', 'eightshift-forms')}
          help={__('Email address to which you wish to receive emails.', 'eightshift-forms')}
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
          help={__('Message you will receive on form submit. Remember you can use placeholders.', 'eightshift-forms')}
        >
          <RichText
            className={richTextClass}
            placeholder={__('Add your message', 'eightshift-forms')}
            onChange={onChangeMessage}
            value={defaultMessage}
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
    </Fragment>
  );
};
