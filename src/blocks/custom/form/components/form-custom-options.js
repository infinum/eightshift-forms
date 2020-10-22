import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { TextControl } from '@wordpress/components';


/**
 * Options component
 *
 * @param {object} props Component props.
 */
export const FormCustomOptions = (props) => {
  const {
    action,
    method,
    target,
    onChangeAction,
    onChangeMethod,
    onChangeTarget,
  } = props;

  return (
    <Fragment>
      {onChangeAction &&
        <TextControl
          label={__('Action', 'eightshift-forms')}
          value={action}
          onChange={onChangeAction}
        />
      }

      {onChangeMethod &&
        <TextControl
          label={__('Method', 'eightshift-forms')}
          value={method}
          onChange={onChangeMethod}
        />
      }

      {onChangeTarget &&
        <TextControl
          label={__('Target', 'eightshift-forms')}
          value={target}
          onChange={onChangeTarget}
        />
      }
    </Fragment>
  );
};
