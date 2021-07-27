import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { TextControl, BaseControl, ToggleControl } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';
import { NewSection } from '../../../components/new-section/new-section';
import { getAttrKey } from '@eightshift/frontend-libs/scripts/helpers';
import manifest from '../manifest.json';

export const FormEmailOptions = (props) => {
	const {
		attributes,
		setAttributes,
		richTextClass,
		formEmailTo,
		formEmailSubject,
		formEmailMessage = '',
		formEmailAdditionalHeaders,
		formEmailSendConfirmationToSender,
		formEmailConfirmationMessage,
		formEmailConfirmationSubject,
	} = props;

	return (
		<Fragment>
			<NewSection
				label={__('Admin email settings', 'eightshift-forms')}
			/>
			<TextControl
				label={__('To', 'eightshift-forms')}
				help={__('Email address to which the form sends emails to.', 'eightshift-forms')}
				value={formEmailTo}
				onChange={(value) => setAttributes({ [getAttrKey('formEmailTo', attributes, manifest)]: value })}
			/>
			<TextControl
				label={__('Subject', 'eightshift-forms')}
				help={__('Subject of the email.', 'eightshift-forms')}
				value={formEmailSubject}
				onChange={(value) => setAttributes({ [getAttrKey('formEmailSubject', attributes, manifest)]: value })}
			/>
			<BaseControl
				label={__('Message', 'eightshift-forms')}
				help={__('Message which is sent on form submit. Remember you can use placeholders.', 'eightshift-forms')}
			>
				<RichText
					className={richTextClass}
					placeholder={__('Add your message', 'eightshift-forms')}
					value={formEmailMessage}
					onChange={(value) => setAttributes({ [getAttrKey('formEmailMessage', attributes, manifest)]: value })}
				/>
			</BaseControl>
			<BaseControl
				label={__('Additional headers (optional)', 'eightshift-forms')}
				help={__('Add additional headers for this email.', 'eightshift-forms')}
			>
				<RichText
					className={richTextClass}
					placeholder={__('Add additional headers', 'eightshift-forms')}
					value={formEmailAdditionalHeaders}
					onChange={(value) => setAttributes({ [getAttrKey('formEmailAdditionalHeaders', attributes, manifest)]: value })}
				/>
			</BaseControl>
			<NewSection
				label={__('Confirmation email settings', 'eightshift-forms')}
			/>
			<ToggleControl
				label={__('Send email confirmation to sender?', 'eightshift-forms')}
				help={__('If enabled, an email will be sent to the user after filling out this form.', 'eightshift-forms')}
				checked={formEmailSendConfirmationToSender}
				onChange={(value) => setAttributes({ [getAttrKey('formEmailSendConfirmationToSender', attributes, manifest)]: value })}
				/>
			{formEmailSendConfirmationToSender &&
				<TextControl
					label={__('Subject', 'eightshift-forms')}
					help={__('Subject of the confirmation email.', 'eightshift-forms')}
					value={formEmailConfirmationSubject}
					onChange={(value) => setAttributes({ [getAttrKey('formEmailConfirmationSubject', attributes, manifest)]: value })}
					/>
			}
			{formEmailSendConfirmationToSender &&
				<BaseControl
					label={__('Message', 'eightshift-forms')}
					help={__('Message which is sent to user on form submit. Remember you can use placeholders.', 'eightshift-forms')}
				>
					<RichText
						className={richTextClass}
						placeholder={__('Add your message', 'eightshift-forms')}
						onChange={(value) => setAttributes({ [getAttrKey('formEmailConfirmationMessage', attributes, manifest)]: value })}
						value={formEmailConfirmationMessage}
					/>
				</BaseControl>
			}
		</Fragment>
	);
};
