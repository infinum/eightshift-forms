import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';

export const CantHaveMultipleRedirects = (props) => {
	const {
		dismissError,
		forSelects = false,
	} = props;

	return (
		<Notice status="error" onRemove={dismissError}>
			{forSelects &&
				__('Unable to select type that redirects since redirection on success is already enabled.', 'eightshift-forms')
			}
			{!forSelects &&
				__('Unable to redirect user since one of the form types already redirects.', 'eightshift-forms')
			}
		</Notice>
	);
};
