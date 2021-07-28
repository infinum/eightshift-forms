import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { InnerBlocks } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import { LabelEditor } from '../../../components/label/components/label-editor';

const hasSelectedInnerBlock = (props) => {
	const select = wp.data.select('core/block-editor');
	const selected = select.getBlockSelectionStart();
	const inner = select.getBlock(props.clientId).innerBlocks ? select.getBlock(props.clientId).innerBlocks : [];
	for (let i = 0; i < inner.length; i++) {
		if (inner[i].clientId === selected || (inner[i].innerBlocks.length && hasSelectedInnerBlock(inner[i]))) {
			return true;
		}
	}
	return false;
};

export const SelectEditor = (props) => {
	const {
		attributes,
		attributes: {
			blockFullName,
			blockClass,
			label,
			allowedBlocks,
			theme = '',
			prefillData,
			prefillDataSource,
		},
		actions: {
			onChangeLabel,
		},
		isSelected,
		clientId,
	} = props;

	const isBlockOrChildrenSelected = isSelected || hasSelectedInnerBlock(props);

	const isPrefillUsed = prefillData && prefillDataSource;

	return (
		<>

			{isPrefillUsed &&
				<ServerSideRender
					block={blockFullName}
					attributes={{ ...attributes, hideLoading: false }}
					urlQueryArgs={{ cacheBusting: JSON.stringify(attributes) }}
				/>
			}

			{!isPrefillUsed &&
				<div className={`${blockClass} ${blockClass}__theme--${theme}`}>
					<LabelEditor
						blockClass={blockClass}
						label={label}
						onChangeLabel={onChangeLabel}
					/>
					<div className={`${blockClass}__content-wrap`}>
						{!isBlockOrChildrenSelected &&
							<select>
								{wp.data.select('core/block-editor').getBlock(clientId).innerBlocks.map((block, key) => {
									return (
										<option
											key={key}
											className={`${blockClass}__option`}
											{...block.attributes}
										>
											{label}
										</option>
									);
								})}
							</select>
						}
						{isBlockOrChildrenSelected &&
							<div className={`${blockClass}__editor`}>
								<h2>{__('Modify select options', 'eightshift-forms')}</h2>
								<p>{__('Unselect this block to render it', 'eightshift-forms')}</p>
								<InnerBlocks
									allowedBlocks={(typeof allowedBlocks === 'undefined') || allowedBlocks}
									templateLock={false}
								/>
							</div>
						}
					</div>
				</div>
			}
		</>
	);
};
