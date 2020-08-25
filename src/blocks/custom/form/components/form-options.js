import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { PanelBody, TextControl, SelectControl, BaseControl, TabPanel, Dashicon } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';

/**
 * Custom action which changes the "theme" attribute for this block and all it's innerBlocks.
 *
 * @param {string} newTheme New value for theme attribute
 * @param {function} onChangeTheme Prebuilt action for form block.
 */
const updateThemeForAllInnerBlocks = (newTheme, onChangeTheme) => {
  const thisBlock = wp.data.select('core/block-editor').getSelectedBlock();
  if (thisBlock.innerBlocks) {
    thisBlock.innerBlocks.forEach((innerBlock) => {
      innerBlock.attributes.theme = newTheme;
      wp.data.dispatch('core/block-editor').updateBlock(innerBlock.clientId, innerBlock);
    });
  }
  onChangeTheme(newTheme);
};

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
    dynamicsCrm = [],
  } = window.eightshiftForms;

  const themeAsOptions = hasThemes ? themes.map((tempTheme) => ({ label: tempTheme, value: tempTheme })) : [];

  // All Dynamics CRM config stuff
  let crmEntitiesAsOptions = [];
  if (isDynamicsCrmUsed) {
    crmEntitiesAsOptions = dynamicsCrm.availableEntities.map((entity) => ({ label: entity, value: entity }));
    formTypes.push({ label: __('Microsoft Dynamics CRM 365', 'eightshift-forms'), value: 'dynamics-crm' });
  }

  return (
    <PanelBody title={__('Form Settings', 'eightshift-forms')}>
      <TabPanel
        className="custom-button-tabs"
        activeClass="components-button is-button is-primary"
        tabs={[
          {
            name: 'general',
            title: <Dashicon icon="admin-generic" />,
            className: 'tab-large components-button is-button is-default custom-button-with-icon',
          },
          {
            name: 'email',
            title: <Dashicon icon="email" />,
            className: 'tab-desktop components-button is-button is-default custom-button-with-icon',
          },
          isDynamicsCrmUsed && type === 'dynamics-crm' && {
            name: 'dynamics-crm',
            title: <Dashicon icon="cloud-upload" />,
            className: 'tab-tablet components-button is-button is-default custom-button-with-icon',
          },
        ]
        }
      >
        {(tab) => (
          <Fragment>
            {tab.name === 'general' && (
              <Fragment>
                <br />
                <strong className="notice-title">{__('General Options', 'eightshift-forms')}</strong>
                <p>{__('These are general form options.', 'eightshift-forms')}</p>
                <br />
                {onChangeType &&
                  <SelectControl
                    label={__('Type', 'eightshift-forms')}
                    value={type}
                    help={__('Choose what will this form do on submit', 'eightshift-forms')}
                    options={formTypes}
                    onChange={onChangeType}
                  />
                }
                {onChangeTheme && hasThemes &&
                  <SelectControl
                    label={__('Theme', 'eightshift-forms')}
                    help={__('Choose your form theme.', 'eightshift-forms')}
                    value={theme}
                    options={themeAsOptions}
                    onChange={(newTheme) => {
                      updateThemeForAllInnerBlocks(newTheme, onChangeTheme);
                    }}
                  />
                }

                {onChangeSuccessMessage &&
                  <BaseControl
                    label={__('Success message', 'eightshift-forms')}
                    help={__('Message that the user will see if forms successfully submits.', 'eightshift-forms')}
                  >
                    <RichText
                      className={richTextClass}
                      placeholder={__('Add your success message', 'eightshift-forms')}
                      onChange={onChangeSuccessMessage}
                      value={successMessage}
                    />
                  </BaseControl>
                }

                {onChangeErrorMessage &&
                  <BaseControl
                    label={__('Error message', 'eightshift-forms')}
                    help={__('Message that the user will see if forms fails to submit for whatever reason.', 'eightshift-forms')}
                  >
                    <RichText
                      className={richTextClass}
                      placeholder={__('Add your error message', 'eightshift-forms')}
                      onChange={onChangeErrorMessage}
                      value={errorMessage}
                    />
                  </BaseControl>
                }
              </Fragment>
            )}
            {tab.name === 'email' && (
              <Fragment>
                <br />
                <strong className="notice-title">{__('Email Options', 'eightshift-forms')}</strong>
                <p>{__('These are options for when your form is sending emails.', 'eightshift-forms')}</p>
                <br />
              </Fragment>
            )}
            {tab.name === 'dynamics-crm' && (
              <Fragment>
                <br />
                <strong className="notice-title">{__('Dynamics CRM Options', 'eightshift-forms')}</strong>
                <p>{__('These are options for when your form is sending data to Dynamics CRM.', 'eightshift-forms')}</p>
                <br />
                {onChangeDynamicsEntity && isDynamicsCrmUsed && type === 'dynamics-crm' &&
                  <SelectControl
                    label={__('CRM Entity', 'eightshift-forms')}
                    help={__('Please enter the name of the entity record to which you wish to add records.', 'eightshift-forms')}
                    value={dynamicsEntity}
                    options={crmEntitiesAsOptions}
                    onChange={onChangeDynamicsEntity}
                  />
                }
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
