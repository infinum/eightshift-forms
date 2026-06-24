import { select } from '@wordpress/data';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { IntegrationsInternalOptions } from './../../../components/integrations/components/integrations-internal-options';
import manifest from '../manifest.json';

export const PardotOptions = ({ attributes, setAttributes, clientId }) => {
	const { title } = manifest;

	return (
		<IntegrationsInternalOptions
			title={title}
			clientId={clientId}
			attributes={attributes}
			setAttributes={setAttributes}
		/>
	);
};
