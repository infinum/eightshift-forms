import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { form, globeAnchor } from '@eightshift/ui-components/icons';
import { __ } from '@wordpress/i18n';
import { FormEditButton } from '../../../components/utils';
import { Container, ContainerGroup, Notice, RichLabel, BaseControl } from '@eightshift/ui-components';
import { upperFirst } from '@eightshift/ui-components/utilities';
import { useBlockProps } from '@wordpress/block-editor';
import manifest from '../manifest.json';

export const FormsEditor = ({ attributes, preview }) => {
	const { isGeoPreview } = preview;

	const blockProps = useBlockProps({
		className: 'esf:p-8 es:font-sans',
	});

	const formsFormGeolocationAlternatives = checkAttr('formsFormGeolocationAlternatives', attributes, manifest);
	const formsFormPostIdRaw = checkAttr('formsFormPostIdRaw', attributes, manifest);
	const formsFormPostId = checkAttr('formsFormPostId', attributes, manifest);

	if (formsFormPostId?.length < 1) {
		return (
			<div className='esf:p-8 es:font-sans'>
				<Notice
					type='placeholder'
					icon={form}
					label={__('Eightshift Forms', 'eightshift-forms')}
					subtitle={__('Select a form in the block options', 'eightshift-forms')}
					className='esf:w-fit'
				/>
			</div>
		);
	}

	const formId = formsFormPostIdRaw?.id ?? formsFormPostIdRaw?.value;

	return (
		<div {...blockProps}>
			<ContainerGroup className='esf:max-w-sm'>
				<Container
					elevated
					centered
					compact
					accent
				>
					<span className='esf:text-xs esf:font-stretch-110% esf:font-normal!'>{__('Eightshift Forms', 'eightshift-forms')}</span>
				</Container>

				<Container centered>
					<BaseControl
						icon={form}
						label={formsFormPostIdRaw?.label}
						subtitle={upperFirst(formsFormPostIdRaw?.metadata)}
						className='esf:w-full'
						inline
					>
						<FormEditButton formId={formId} />
					</BaseControl>
				</Container>

				<Container
					hidden={!isGeoPreview}
					centered
				>
					<RichLabel
						icon={globeAnchor}
						label={__('Original form', 'eightshift-forms')}
						subtitle={__('Geolocation', 'eightshift-forms')}
					/>
				</Container>
			</ContainerGroup>

			<ContainerGroup
				hidden={!isGeoPreview || formsFormGeolocationAlternatives?.length < 1}
				label={__('Geolocation alternatives', 'eightshift-forms')}
				className='esf:max-w-sm'
			>
				{formsFormGeolocationAlternatives.map((item, index) => {
					return (
						<Container
							key={index}
							icon={form}
							label={<span>{__('Eightshift Forms', 'eightshift-forms')}</span>}
						>
							<BaseControl
								icon={form}
								label={`${item?.form?.label} (${item?.form?.metadata})`}
								subtitle={item.geoLocation.join(', ')}
								subtitleClassName='es:font-mono'
								inline
							>
								<FormEditButton formId={item?.form?.id ?? item?.form?.value} />
							</BaseControl>
						</Container>
					);
				})}
			</ContainerGroup>
		</div>
	);
};
